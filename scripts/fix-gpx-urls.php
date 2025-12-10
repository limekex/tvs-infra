<?php
/**
 * Fix GPX URLs in route meta to use localhost instead of dev.virtualsport.online
 * Run with: docker exec tvs_wordpress_1 php /var/www/html/wp-content/plugins/tvs-virtual-sports/scripts/fix-gpx-urls.php
 */

// Load WordPress
require_once('/var/www/html/wp-load.php');

$old_domain = 'http://dev.virtualsport.online:8080';
$new_domain = 'http://localhost:8080';

echo "🔍 Searching for routes with old GPX URLs...\n\n";

// Get all routes
$routes = get_posts(array(
    'post_type' => 'tvs_route',
    'posts_per_page' => -1,
    'post_status' => 'any',
));

$updated = 0;
$skipped = 0;

foreach ($routes as $route) {
    $gpx_url = get_post_meta($route->ID, 'gpx_url', true);
    
    if (empty($gpx_url)) {
        echo "⏭️  Route #{$route->ID} '{$route->post_title}' - No GPX URL\n";
        $skipped++;
        continue;
    }
    
    // Check if URL contains old domain
    if (strpos($gpx_url, $old_domain) !== false) {
        $new_url = str_replace($old_domain, $new_domain, $gpx_url);
        update_post_meta($route->ID, 'gpx_url', $new_url);
        echo "✅ Route #{$route->ID} '{$route->post_title}'\n";
        echo "   OLD: {$gpx_url}\n";
        echo "   NEW: {$new_url}\n\n";
        $updated++;
    } else {
        echo "⏭️  Route #{$route->ID} '{$route->post_title}' - URL OK: {$gpx_url}\n";
        $skipped++;
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Updated: {$updated} routes\n";
echo "⏭️  Skipped: {$skipped} routes\n";
echo str_repeat('=', 60) . "\n";
