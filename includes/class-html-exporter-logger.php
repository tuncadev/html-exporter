<?php
class HTML_Exporter_Logger {
    public static function log_export($date, $status, $post_types, $exported_count, $failed_count, $transient_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'html_exporter_logs';

        $wpdb->insert(
            $table_name,
            array(
                'export_date' => $date,
                'export_status' => $status,
                'post_types' => implode(', ', $post_types),
                'exported_count' => $exported_count,
                'failed_count' => $failed_count,
                'transient_id' => $transient_id
            )
        );
    }

    public static function log_error($export_id, $error_message) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'html_exporter_error_logs';

        $wpdb->insert(
            $table_name,
            array(
                'export_id' => $export_id,
                'error_message' => $error_message,
                'error_time' => current_time('mysql')
            )
        );
    }
}
