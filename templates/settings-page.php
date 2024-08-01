<div class="wrap">
    <h1>HTML Exporter Settings</h1>
    <form method="post" action="options.php" id="html-exporter-settings-form">
        <?php settings_fields('html_exporter_settings'); ?>
        <?php do_settings_sections('html_exporter_settings'); ?>
        <h2>Select Content Types to Export</h2>
        <?php foreach ($post_types as $post_type) : ?>
            <label>
                <input type="checkbox" name="html_exporter_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php echo in_array($post_type->name, $schedule['post_types']) ? 'checked' : ''; ?>>
                <?php echo esc_html($post_type->labels->name); ?>
            </label><br>
        <?php endforeach; ?>
        <h3>Schedule Export</h3>
        <label>
            <input type="checkbox" name="html_exporter_interval_enabled" id="html_exporter_interval_enabled" value="yes" <?php checked($schedule['interval_enabled'], 'yes'); ?>>
            Enable scheduled exports
        </label>
        <div id="interval-options" style="display: <?php echo $schedule['interval_enabled'] === 'yes' ? 'block' : 'none'; ?>;">
            <h3>Schedule Interval</h3>
            <select name="html_exporter_export_interval" id="html_exporter_export_interval">
                <option value="" <?php selected($schedule['export_interval'], ''); ?>>Please Select Interval</option>
                <option value="minutes" <?php selected($schedule['export_interval'], 'minutes'); ?>>Minutes (for testing)</option>
                <option value="weekly" <?php selected($schedule['export_interval'], 'weekly'); ?>>Weekly</option>
                <option value="monthly" <?php selected($schedule['export_interval'], 'monthly'); ?>>Monthly</option>
            </select>
            <div id="minutes-interval" style="display: <?php echo $schedule['export_interval'] === 'minutes' ? 'block' : 'none'; ?>;">
                <h3>Interval Minutes</h3>
                <input type="number" name="html_exporter_interval_minutes" value="<?php echo esc_attr($schedule['interval_minutes']); ?>" min="1">
            </div>
        </div>
        <h3>Email Settings</h3>
        <label for="html_exporter_email">Send export completion email to:</label>
        <input type="email" name="html_exporter_email" id="html_exporter_email" value="<?php echo esc_attr($schedule['email']); ?>">
        <div style="display: flex; gap: 10px; align-items: center;">
            <?php submit_button('Save Settings'); ?>
            <p class="submit">
            <button type="button" class="button button-secondary" id="reset-settings-button" style="background-color: red; color: white; border-color: red;">Reset Settings</button>
            </p>
        </div>
    </form>
</div>
<script>
    jQuery(document).ready(function($) {
        $('#html_exporter_interval_enabled').change(function() {
            if ($(this).is(':checked')) {
                $('#interval-options').show();
            } else {
                $('#interval-options').hide();
            }
        });

        $('#html_exporter_export_interval').change(function() {
            if ($(this).val() === 'minutes') {
                $('#minutes-interval').show();
            } else {
                $('#minutes-interval').hide();
            }
        });

        $('#reset-settings-button').click(function() {
            $('<form method="post" style="display:none;">' +
              '<input type="hidden" name="reset_html_exporter_settings" value="1">' +
              '</form>').appendTo('body').submit();
        });
    });
</script>
