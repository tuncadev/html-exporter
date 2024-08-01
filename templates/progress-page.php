<div class="wrap">
    <h1>HTML Exporter Progress</h1>
    <div id="export-progress">
        <p>Export is in progress, please wait...</p>
        <button id="cancel-export" class="button">Cancel</button>
    </div>
    <div id="export-result"></div>
</div>
<script>
    jQuery(document).ready(function($) {
        function checkExportStatus() {
            $.ajax({
                url: htmlExporter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'check_export_status'
                },
                success: function(response) {
                    if (response.data.status === 'complete') {
                        window.location.href = 'tools.php?page=html-exporter';
                    }
                }
            });
        }

        setInterval(checkExportStatus, 2000);

        $('#cancel-export').on('click', function() {
            window.location.href = 'tools.php?page=html-exporter';
        });
    });
</script>
