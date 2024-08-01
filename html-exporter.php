<?php
/*
Plugin Name: HTML Exporter
Description: Export pages, posts, or other custom post types to HTML and save to local uploads folder with additional functionalities.
Version: 1.1
Author: Murat Tunca (HYS Enterprise)
*/

if (!defined('ABSPATH')) {
    exit;
}

define('HTML_EXPORTER_VERSION', '1.4');
define('HTML_EXPORTER_DIR', plugin_dir_path(__FILE__));
define('HTML_EXPORTER_URL', plugin_dir_url(__FILE__));

require_once HTML_EXPORTER_DIR . 'includes/functions.php';
require_once HTML_EXPORTER_DIR . 'includes/class-html-exporter.php';
require_once HTML_EXPORTER_DIR . 'includes/class-html-exporter-admin.php';
require_once HTML_EXPORTER_DIR . 'includes/class-html-exporter-logger.php';
require_once HTML_EXPORTER_DIR . 'includes/class-html-exporter-scheduler.php';

function html_exporter_init() {
    HTML_Exporter::get_instance();
    new HTML_Exporter_Admin();
    new HTML_Exporter_Scheduler();
}

add_action('plugins_loaded', 'html_exporter_init');

function html_exporter_register_settings() {
    register_setting('html_exporter_settings', 'html_exporter_post_types');
    register_setting('html_exporter_settings', 'html_exporter_interval_enabled');
    register_setting('html_exporter_settings', 'html_exporter_export_interval');
    register_setting('html_exporter_settings', 'html_exporter_interval_minutes');
    register_setting('html_exporter_settings', 'html_exporter_email');
    
    add_action('update_option_html_exporter_interval_enabled', 'html_exporter_schedule_task');
    add_action('update_option_html_exporter_export_interval', 'html_exporter_schedule_task');
    add_action('update_option_html_exporter_interval_minutes', 'html_exporter_schedule_task');
}

add_action('admin_init', 'html_exporter_register_settings');

function html_exporter_schedule_task() {
    HTML_Exporter_Scheduler::schedule_task();
}

function html_exporter_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table for Export Process Logs
    $table_name = $wpdb->prefix . 'html_exporter_logs';
    $sql = "CREATE TABLE $table_name (
        ID bigint(20) NOT NULL AUTO_INCREMENT,
        export_date datetime NOT NULL,
        export_status varchar(20) NOT NULL,
        post_types text NOT NULL,
        exported_count int(11) NOT NULL,
        failed_count int(11) NOT NULL,
        zip_filename varchar(255) DEFAULT '',
        transient_id varchar(255) DEFAULT '',
        error_log text DEFAULT '',
        task_id bigint(20) NOT NULL,
        PRIMARY KEY  (ID)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Table for Task Settings
    $table_name = $wpdb->prefix . 'html_exporter_task_settings';
    $sql = "CREATE TABLE $table_name (
        ID bigint(20) NOT NULL AUTO_INCREMENT,
        post_types text NOT NULL,
        interval_enabled varchar(3) DEFAULT 'no',
        export_interval varchar(10) DEFAULT '',
        interval_minutes int(11) DEFAULT NULL,
        email varchar(255) NOT NULL,
        user_id bigint(20) NOT NULL,
        first_execution datetime NOT NULL,
        PRIMARY KEY  (ID)
    ) $charset_collate;";
    dbDelta($sql);

    // Table for Scheduled Tasks
    $table_name = $wpdb->prefix . 'html_exporter_scheduled_tasks';
    $sql = "CREATE TABLE $table_name (
        ID bigint(20) NOT NULL AUTO_INCREMENT,
        task_id bigint(20) NOT NULL,
        next_run datetime NOT NULL,
        status varchar(20) NOT NULL,
        last_run datetime DEFAULT NULL,
        execution_success varchar(255) DEFAULT '',
        PRIMARY KEY  (ID)
    ) $charset_collate;";
    dbDelta($sql);

    // Table for Hashes
    $table_name = $wpdb->prefix . 'html_exporter_hashes';
    $sql = "CREATE TABLE $table_name (
        ID bigint(20) NOT NULL AUTO_INCREMENT,
        task_id bigint(20) NOT NULL,
        post_id bigint(20) NOT NULL,
        post_type varchar(20) NOT NULL,
        hash varchar(255) NOT NULL,
        general_hash varchar(255) NOT NULL,
        PRIMARY KEY  (ID)
    ) $charset_collate;";
    dbDelta($sql);
}

function html_exporter_migrate_tables() {
    // Implement migration logic if necessary
}

register_activation_hook(__FILE__, 'html_exporter_create_tables');
register_activation_hook(__FILE__, 'html_exporter_migrate_tables');
register_deactivation_hook(__FILE__, array('HTML_Exporter_Scheduler', 'deactivate'));

register_uninstall_hook(__FILE__, 'html_exporter_uninstall');
function html_exporter_uninstall() {
    HTML_Exporter_Scheduler::remove_all_schedules();
}
