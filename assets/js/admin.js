jQuery(document).ready(function($) {
    var interval;

    $('#html-exporter-form').off('submit').on('submit', function(e) {
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
                console.error('Export failed:', data);
                $('#export-result').html('<div class="error"><p>' + (data.data && data.data.message ? data.data.message : 'An error occurred during the export.') + '</p></div>');
            }
        }).catch(error => {
            console.error('Error:', error);
            $('#export-result').html('<div class="error"><p>An error occurred during the export. ' + error.message + '</p></div>');
        });

        interval = setInterval(checkStatus, 2000);

        $('#cancel-export').off('click').on('click', function() {
            fetch(htmlExporter.ajaxUrl + '?action=html_exporter_cancel_export').then(response => response.json()).then(data => {
                if (data.success) {
                    clearInterval(interval);
                    $('#export-progress').hide();
                    $('#export-result').html('<div class="updated"><p>Export was cancelled.</p></div>');
                }
            }).catch(error => {
                console.error('Error:', error);
            });
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
        }).catch(error => {
            console.error('Error:', error);
        });
    }

    // For handling the settings page
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
