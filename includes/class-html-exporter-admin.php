<?php

if (!class_exists('HTML_Exporter_Admin')) {
    class HTML_Exporter_Admin {

        public function __construct() {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_ajax_html_exporter_export_status', array($this, 'export_status'));
            add_action('wp_ajax_html_exporter_cancel_export', array($this, 'cancel_export'));
            add_action('wp_ajax_html_exporter_remove_schedule', array($this, 'remove_schedule'));
        }

        public function export_status() {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');

            while (true) {
                $status = get_transient('html_exporter_status');
                if ($status) {
                    echo "data: {$status}\n\n";
                    flush();
                }
                sleep(1);
            }
            wp_die();
        }

        public function cancel_export() {
            set_transient('html_exporter_cancel', true, 60);
            wp_send_json_success();
        }

        public function remove_schedule() {
            if (isset($_POST['schedule_id'])) {
                $schedule_id = intval($_POST['schedule_id']);
                HTML_Exporter_Scheduler::remove_schedule($schedule_id);
                wp_send_json_success();
            } else {
                wp_send_json_error('Schedule ID not provided.');
            }
        }

        public function add_admin_menu() {
            add_menu_page(
                'HTML Exporter',
                'HTML Exporter',
                'manage_options',
                'html-exporter-settings',
                array($this, 'settings_page'),
                'dashicons-admin-generic'
            );
            add_submenu_page(
                'html-exporter-settings',
                'Settings',
                'Settings',
                'manage_options',
                'html-exporter-settings',
                array($this, 'settings_page')
            );
            add_submenu_page(
                'html-exporter-settings',
                'Export HTML',
                'Export HTML',
                'manage_options',
                'html-exporter-export',
                array($this, 'export_page')
            );
            add_submenu_page(
                'html-exporter-settings',
                'Previous Exports',
                'Previous Exports',
                'manage_options',
                'html-exporter-previous-exports',
                array($this, 'previous_exports_page')
            );
            add_submenu_page(
                'html-exporter-settings',
                'Scheduled Exports',
                'Scheduled Exports',
                'manage_options',
                'html-exporter-scheduled',
                array($this, 'scheduled_exports_page')
            );
        }

        public function enqueue_scripts($hook) {
            if (strpos($hook, 'html-exporter') === false) {
                return;
            }
            wp_enqueue_script('html-exporter-admin', HTML_EXPORTER_URL . 'assets/js/admin.js', array('jquery'), HTML_EXPORTER_VERSION, true);
            wp_localize_script('html-exporter-admin', 'htmlExporter', array('ajaxUrl' => admin_url('admin-ajax.php')));
        }

        public function settings_page() {
            $post_types = html_exporter_get_post_types();
            $schedule = array(
                'post_types' => get_option('html_exporter_post_types', array()),
                'interval_enabled' => get_option('html_exporter_interval_enabled', 'no'),
                'export_interval' => get_option('html_exporter_export_interval', ''),
                'interval_minutes' => get_option('html_exporter_interval_minutes', ''),
                'email' => get_option('html_exporter_email', ''),
            );

            include HTML_EXPORTER_DIR . 'templates/settings-page.php';
        }

        public function previous_exports_page() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'html_exporter_logs';

            if (isset($_POST['html_exporter_action']) && $_POST['html_exporter_action'] === 'delete' && isset($_POST['export_id'])) {
                $export_id = intval($_POST['export_id']);
                $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ID = %d", $export_id));

                if ($log) {
                    $upload_dir = wp_upload_dir();
                    $zip_file = ABSPATH . '/html_exports/' . $log->zip_filename;
                    if (file_exists($zip_file)) {
                        unlink($zip_file);
                    }
                    $wpdb->delete($table_name, array('ID' => $export_id));
                }
            }

            $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY export_date DESC");

            include HTML_EXPORTER_DIR . 'templates/previous-exports-page.php';
        }

        public function export_page() {
            $post_types = html_exporter_get_post_types();
            $schedule = array(
                'post_types' => get_option('html_exporter_post_types', array()),
                'interval_enabled' => get_option('html_exporter_interval_enabled', 'no'),
                'export_interval' => get_option('html_exporter_export_interval', ''),
                'interval_minutes' => get_option('html_exporter_interval_minutes', ''),
                'email' => get_option('html_exporter_email', ''),
            );

            include HTML_EXPORTER_DIR . 'templates/export-page.php';
        }

        public function scheduled_exports_page() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'html_exporter_task_settings';

            $tasks = $wpdb->get_results("SELECT * FROM $table_name ORDER BY first_execution DESC");

            include HTML_EXPORTER_DIR . 'templates/scheduled-exports-page.php';
        }

        public function check_transients() {
            $running = get_transient('html_exporter_running');
            $cancel = get_transient('html_exporter_cancel');

            echo 'Running: ' . ($running ? 'Yes' : 'No') . '<br>';
            echo 'Cancel: ' . ($cancel ? 'Yes' : 'No') . '<br>';
            echo 'Running set at: ' . get_option('html_exporter_running_time') . '<br>';

            wp_die();
        }
    }
}

add_action('wp_ajax_html_exporter_check_transients', array('HTML_Exporter_Admin', 'check_transients'));
