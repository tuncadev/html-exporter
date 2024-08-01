<div class="wrap">
    <h1>Previous Exports</h1>
    <table class="wp-list-table widefat fixed striped">
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
                            <a href="<?php echo esc_html(HTML_EXPORTER_URL . 'exports/zip/'.$log->zip_filename); ?>" download>Download</a>
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
