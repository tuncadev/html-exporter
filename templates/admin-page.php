<div class="wrap">
    <h1>HTML Exporter</h1>
    <form method="post" action="admin.php?page=html-exporter-progress" target="_blank" id="html-exporter-form">
        <input type="hidden" name="action" value="html_exporter_export">
        <h2>Select Content Types to Export</h2>
        <?php foreach ($post_types as $post_type) : ?>
            <label>
                <input type="checkbox" name="post_types[]" value="<?php echo esc_attr($post_type->name); ?>">
                <?php echo esc_html($post_type->labels->name); ?>
            </label><br>
        <?php endforeach; ?>
        <h3>
            <label>
                <input type="checkbox" id="regular-export-checkbox" name="regular_export">
                Schedule Regular Export
            </label>
        </h3>
        <div id="schedule-options" style="display: none;">
            <h3>Schedule Interval</h3>
            <select name="interval">
                <option value="weekly" <?php selected($schedule['interval'], 'weekly'); ?>>Weekly</option>
                <option value="monthly" <?php selected($schedule['interval'], 'monthly'); ?>>Monthly</option>
            </select>
            <h3>Schedule Time (HH:MM)</h3>
            <input type="time" name="time" value="<?php echo esc_attr($schedule['time']); ?>">
        </div>
        <h3>
            <label>
                <input type="checkbox" id="send-email-checkbox" name="send_email">
                Send Export Link via Email
            </label>
        </h3>
        <div id="email-options" style="display: none;">
            <h3>Email Address</h3>
            <input type="email" name="email_address" placeholder="Enter email address">
        </div>
        <?php submit_button('Export Content to HTML'); ?>
    </form>
    <div id="export-result"></div>
    <h2>Previous Exports</h2>
    <table class="wp-list-table widefat fixed striped" id="export-log-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Export Date</th>
                <th>Status</th>
                <th>Post Types</th>
                <th>Exported</th>
                <th>Failed</th>
                <th>Download</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log) : ?>
                <tr>
                    <td><?php echo esc_html($log->ID); ?></td>
                    <td><?php echo esc_html($log->export_date); ?></td>
                    <td><?php echo esc_html($log->export_status); ?></td>
                    <td><?php echo esc_html($log->post_types); ?></td>
                    <td><?php echo esc_html($log->exported_count); ?></td>
                    <td><?php echo esc_html($log->failed_count); ?></td>
                    <td>
                        <?php if (!empty($log->zip_filename)) : ?>
                            <a href="<?php echo esc_url(ABSPATH . '/html_exports/' . $log->zip_filename); ?>" download>Download</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="html_exporter_action" value="delete">
                            <input type="hidden" name="export_id" value="<?php echo esc_attr($log->ID); ?>">
                            <?php submit_button('Delete', 'delete', '', false); ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    jQuery(document).ready(function($) {
        $('#regular-export-checkbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#schedule-options').show();
            } else {
                $('#schedule-options').hide();
            }
        });

        $('#send-email-checkbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#email-options').show();
            } else {
                $('#email-options').hide();
            }
        });

        $('#html-exporter-form').on('submit', function(e) {
            window.open('admin.php?page=html-exporter-progress', '_blank');
        });
    });
</script>
