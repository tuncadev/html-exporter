<div class="wrap">
    <h1>Export HTML</h1>
    <h2>Current Export Settings</h2>
    <?php 
    $post_types_str = is_array($schedule['post_types']) ? implode(',', $schedule['post_types']) : $schedule['post_types'];
    $selected_post_types = !empty($post_types_str) ? explode(',', $post_types_str) : array();
    ?>
    <ol>
        <?php foreach ($selected_post_types as $post_type) : ?>
            <li><?php echo esc_html(get_post_type_object($post_type)->labels->name); ?></li>
        <?php endforeach; ?>
    </ol>
    <?php if ($schedule['interval_enabled'] === 'yes') : ?>
        <p><strong>Interval:</strong> <?php echo esc_html($schedule['export_interval']); ?></p>
        <?php if ($schedule['export_interval'] === 'minutes') : ?>
            <p><strong>Minutes Interval:</strong> <?php echo esc_html($schedule['interval_minutes']); ?></p>
        <?php endif; ?>
    <?php else : ?>
        <p><strong>Interval:</strong> One-time export</p>
    <?php endif; ?>
    <p><strong>Email address: </strong><?php echo get_option('html_exporter_email'); ?></p>

    
    <form method="post" action="" id="html-exporter-form">
        <input type="hidden" name="action" value="html_exporter_export">
        <button type="submit" class="button button-primary">Export Content to HTML</button>
    </form>
    <div id="export-progress" style="display:none;">
        <p>Export is in progress, please wait...</p>
        <ul id="export-status"></ul>
        <button id="cancel-export" class="button button-secondary">Cancel Export</button>
    </div>
    <div id="export-result"></div>
</div>
<script>
    jQuery(document).ready(function($) {
        var interval;

        $('#html-exporter-form').on('submit', function(e) {
            e.preventDefault();
            $('#export-progress').show();
            $('#export-status').html('');
            $('#cancel-export').show();
            var formData = new FormData(this);

            fetch(htmlExporter.ajaxUrl, {
                method: 'POST',
                body: formData
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    window.location.href = 'admin.php?page=html-exporter-previous-exports';
                } else {
                    $('#export-result').html('<div class="error"><p>' + data.data.message + '</p></div>');
                }
            }).catch(error => {
                console.error('Error:', error);
                $('#export-result').html('<div class="error"><p>An error occurred during the export.</p></div>');
            });

            interval = setInterval(checkStatus, 2000);

            $('#cancel-export').on('click', function() {
                fetch(htmlExporter.ajaxUrl + '?action=html_exporter_cancel_export').then(response => response.json()).then(data => {
                    if (data.success) {
                        clearInterval(interval);
                        $('#export-progress').hide();
                        $('#export-result').html('<div class="updated"><p>Export was cancelled.</p></div>');
                    }
                }).catch(error => console.error('Error:', error));
            });

            setTimeout(function() {
                clearInterval(interval);
                $('#export-progress').hide();
                window.location.href = 'admin.php?page=html-exporter-previous-exports';
            }, 60000); // Set timeout as per the expected duration of export process
        });

        function checkStatus() {
            fetch(htmlExporter.ajaxUrl + '?action=html_exporter_check_status').then(response => response.json()).then(data => {
                if (data.success) {
                    $('#export-status').append('<li>' + data.data + '</li>');
                }
            }).catch(error => console.error('Error:', error));
        }
    });
</script>
