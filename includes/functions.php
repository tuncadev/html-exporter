<?php

// includes/functions.php

// Utility function to get post types
function html_exporter_get_post_types() {
    return get_post_types(array('public' => true), 'objects');
}

// Utility function to create directories
function html_exporter_create_directory($dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

function html_exporter_create_zip_from_directory($sourceDir, $zipFile)
{
    // Check if the directory exists
    if (!is_dir($sourceDir)) {
        return false;
    }

    // Include the PclZip class
    if (!class_exists('PclZip')) {
        require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    }

    // Initialize the PclZip class
    $archive = new PclZip($zipFile);

    // Create the archive
    $v_list = $archive->create($sourceDir, PCLZIP_OPT_REMOVE_PATH, $sourceDir);

    if ($v_list == 0) {
        // Error creating the archive
        return false;
    }

    return true;
}
