<?php
/**
 * Fix GPX files that have literal \n instead of actual newlines
 * 
 * Run: docker-compose exec wordpress php /var/www/html/scripts/fix-gpx-newlines.php
 */

require_once __DIR__ . '/../wp-load.php';

// Get upload directory
$upload_dir = wp_upload_dir();
$upload_path = $upload_dir['basedir'];

// Find all GPX files
$gpx_files = glob($upload_path . '/**/*.gpx');

echo "Found " . count($gpx_files) . " GPX files\n\n";

$fixed_count = 0;

foreach ($gpx_files as $file) {
    $content = file_get_contents($file);
    
    // Check if file has literal \n or \t
    if (strpos($content, '\\n') !== false || strpos($content, '\\t') !== false) {
        echo "Fixing: " . basename($file) . "\n";
        
        // Replace literal \n with actual newlines
        $fixed = str_replace('\\n', "\n", $content);
        $fixed = str_replace('\\t', "\t", $fixed);
        
        // Write back to file
        file_put_contents($file, $fixed);
        
        $fixed_count++;
        echo "  ✓ Fixed\n";
    }
}

echo "\n✅ Fixed $fixed_count GPX files\n";
