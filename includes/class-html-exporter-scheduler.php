<?php

if (!class_exists('HTML_Exporter_Scheduler')) {
    class HTML_Exporter_Scheduler {

        const SCHEDULE_OPTION = 'html_exporter_schedule';

        public function __construct() {
            add_action('html_exporter_cron', array($this, 'run_scheduled_export'));
        }

        public static function activate() {
            if (!wp_next_scheduled('html_exporter_cron')) {
                wp_schedule_event(time(), 'hourly', 'html_exporter_cron');
            }
        }

        public static function deactivate() {
            self::remove_all_schedules();
        }

        public function run_scheduled_export($task_id = null) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'html_exporter_scheduled_tasks';

            if ($task_id) {
                $tasks = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE task_id = %d", $task_id));
            } else {
                $tasks = $wpdb->get_results("SELECT * FROM $table_name WHERE next_run <= NOW()");
            }

            foreach ($tasks as $task) {
                $task_settings = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}html_exporter_task_settings WHERE ID = %d", $task->task_id));
                if (!$task_settings) {
                    continue;
                }

                $export_result = HTML_Exporter::get_instance()->export_content_to_html($task_settings);

                $next_run = $this->calculate_next_run($task_settings);
                $status = $export_result['status'] === 'success' ? 'completed' : 'failed';

                $wpdb->update(
                    $table_name,
                    array(
                        'last_run' => current_time('mysql'),
                        'next_run' => $next_run,
                        'status' => $status,
                        'execution_success' => $export_result['message']
                    ),
                    array('ID' => $task->ID)
                );
            }
        }

        public static function schedule_task($task_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'html_exporter_scheduled_tasks';
            $task_settings = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}html_exporter_task_settings WHERE ID = %d", $task_id));

            if (!$task_settings) {
                return;
            }

            $next_run = self::calculate_next_run($task_settings);

            $wpdb->insert(
                $table_name,
                array(
                    'task_id' => $task_id,
                    'next_run' => $next_run,
                    'status' => 'scheduled',
                    'last_run' => null
                )
            );
        }

        public static function calculate_next_run($task_settings) {
            $interval = $task_settings->export_interval;
            $next_run = strtotime('10:00:00');

            if ($interval === 'minutes') {
                $next_run = strtotime("+{$task_settings->interval_minutes} minutes");
            } elseif ($interval === 'weekly') {
                $next_run = strtotime('+1 week', $next_run);
            } elseif ($interval === 'monthly') {
                $next_run = strtotime('+1 month', $next_run);
            }

            return date('Y-m-d H:i:s', $next_run);
        }

        public static function remove_schedule($task_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'html_exporter_scheduled_tasks';

            $wpdb->delete($table_name, array('task_id' => $task_id));
        }

        public static function remove_all_schedules() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'html_exporter_scheduled_tasks';

            $wpdb->query("DELETE FROM $table_name");
        }
    }
}
