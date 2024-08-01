<?php 

if (!class_exists('HTML_Exporter')) {
    class HTML_Exporter {
        private static $instance = null;

        private function __construct() {
            add_action('wp_ajax_html_exporter_export', array($this, 'export_content_to_html'));
            add_action('wp_ajax_html_exporter_check_status', array($this, 'check_export_status'));
            add_action('wp_ajax_html_exporter_cancel_export', array($this, 'cancel_export'));
        }

        public static function get_instance() {
            if (self::$instance == null) {
                self::$instance = new HTML_Exporter();
            }
            return self::$instance;
        }

        public function export_content_to_html($task_settings = null) {
            $export_id = uniqid('html_export_', true);
            if (get_transient($export_id)) {
                error_log('Export is already running. Transient set at: ' . get_option('html_exporter_running_time'));
                wp_send_json_error(array(
                    'message' => 'Export is already running.'
                ));
                return;
            }

            set_transient($export_id, true, 60 * 60);
            update_option('html_exporter_running_time', current_time('mysql'));
            error_log('Export started. Transient set at: ' . current_time('mysql'));

            try {
                if (!isset($_POST['action']) || $_POST['action'] !== 'html_exporter_export') {
                    throw new Exception('Invalid action.');
                }

                $post_types = $task_settings ? explode(',', $task_settings->post_types) : get_option('html_exporter_post_types', array());
                $email = $task_settings ? $task_settings->email : get_option('html_exporter_email', '');

                if (empty($post_types)) {
                    throw new Exception('No post types selected for export.');
                }

                global $wpdb;
                $table_name = $wpdb->prefix . 'html_exporter_logs';
                $current_date = date('Y-m-d H:i:s');
                $random_string = wp_generate_password(8, false);
                $date_folder = date('m-d-Y') . '_' . $random_string;

                $base_dir = HTML_EXPORTER_DIR . 'exports/';
                $export_dir = $base_dir . $date_folder;
                
                // Define the source directory and output zip file path
                $sourceDir = HTML_EXPORTER_DIR . 'exports/' . $date_folder;
                $zipFile = HTML_EXPORTER_DIR . 'exports/zip/' . $date_folder . '.zip';
                $download_link = HTML_EXPORTER_URL . 'exports/zip/' . $date_folder . '.zip';
                
                if (!file_exists($base_dir)) {
                    mkdir($base_dir, 0755, true);
                }
                if (!file_exists($export_dir)) {
                    mkdir($export_dir, 0755, true);
                }

                $status = 'done';
                $exported_count = 0;
                $failed_count = 0;
                $hash_table = $wpdb->prefix . 'html_exporter_hashes';
                $task_id = $task_settings ? $task_settings->ID : null;

                foreach ($post_types as $post_type) {
                    $args = array(
                        'post_type' => $post_type,
                        'posts_per_page' => -1,
                    );
                    $posts = get_posts($args);

                    foreach ($posts as $post) {
                        if (get_transient('html_exporter_cancel')) {
                            throw new Exception('Export was cancelled.');
                        }

                        $current_hash = md5(get_permalink($post->ID) . $post->post_content);
                        $existing_hash = $task_id ? $wpdb->get_var($wpdb->prepare("SELECT hash FROM $hash_table WHERE post_id = %d AND post_type = %s AND task_id = %d", $post->ID, $post_type, $task_id)) : false;

                        if ($existing_hash && $existing_hash === $current_hash) {
                            continue;
                        }

                        set_transient('html_exporter_current_status', 'Page "' . esc_html($post->post_title) . '" is being exported...', 60);

                        $response = wp_remote_get(get_permalink($post->ID));
                        if (is_wp_error($response)) {
                            $status = 'failed';
                            $failed_count++;
                            set_transient('html_exporter_current_status', 'Page "' . esc_html($post->post_title) . '" is being exported... <span style="color: red;">FAILED</span>', 60);
                            continue;
                        }

                        $html_content = wp_remote_retrieve_body($response);
                        $filename = $export_dir . '/' . sanitize_title($post->post_name) . '.html';
                         
                        if (file_put_contents($filename, $html_content) !== false) {
                            $exported_count++;
                            set_transient('html_exporter_current_status', 'Page "' . esc_html($post->post_title) . '" is being exported... <span style="color: green;">✓ DONE</span>', 60);
                            if ($task_id) {
                                $wpdb->replace($hash_table, array(
                                    'task_id' => $task_id,
                                    'post_id' => $post->ID,
                                    'post_type' => $post_type,
                                    'hash' => $current_hash,
                                    'general_hash' => md5($html_content)
                                ));
                            }
                        } else {
                            $status = 'failed';
                            $failed_count++;
                            set_transient('html_exporter_current_status', 'Page "' . esc_html($post->post_title) . '" is being exported... <span style="color: red;">FAILED</span>', 60);
                        }
                    }
                }
                error_log('Base Directory: ' . HTML_EXPORTER_DIR);
                error_log('Export Directory: ' . $export_dir);
                error_log('sourceDir: ' .$sourceDir);
                error_log('zipFile Directory: ' .  $zipFile);
                error_log('download link: ' . $download_link);
                
                // Create the zip archive
                if (html_exporter_create_zip_from_directory($sourceDir, $zipFile)) {
                    set_transient('html_exporter_current_status', 'Zip archive created successfully.', 60);
                    $status = 'success';
                } else {
                    set_transient('html_exporter_current_status', 'Failed to create zip archive.', 60);
                    $status = 'failed';
                }
                $zip_filename =  $zipFile;
                // Log export
                $wpdb->insert(
                    $table_name,
                    array(
                        'export_date' => $current_date,
                        'export_status' => $status,
                        'post_types' => implode(', ', $post_types),
                        'exported_count' => $exported_count,
                        'failed_count' => $failed_count,
                        'zip_filename' => $date_folder . '.zip',
                        'transient_id' => $export_id,
                        'task_id' => $task_id ? $task_id : 0
                    )
                );

                // Send email
                if (!empty($email)) {
                    $subject = 'HTML Export Completed';
                    $message = 'The export has been completed.' . PHP_EOL;
                    $message .= 'Export Settings:' . PHP_EOL;
                    $message .= 'Post Types: ' . implode(', ', $post_types) . PHP_EOL;
                    $message .= 'You can download the zip here: ' .  $download_link;
                
                    wp_mail($email, $subject, $message);
                }

                delete_transient($export_id);
                delete_transient('html_exporter_current_status');
                delete_transient('html_exporter_cancel');

                wp_send_json_success(array(
                    'message' => 'Export complete. Exported: ' . $exported_count . ', Failed: ' . $failed_count . '.'
                ));
            } catch (Exception $e) {
                error_log('Error during export: ' . $e->getMessage());
                delete_transient($export_id);
                delete_transient('html_exporter_current_status');
                delete_transient('html_exporter_cancel');
                wp_send_json_error(array(
                    'message' => $e->getMessage()
                ));
            }
        }

        public function check_export_status() {
            $status = get_transient('html_exporter_current_status');
            if ($status) {
                wp_send_json_success($status);
            } else {
                wp_send_json_error();
            }
        }

        public function cancel_export() {
            set_transient('html_exporter_cancel', true, 60);
            wp_send_json_success();
        }
    }
}
