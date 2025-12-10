<?php
/**
 * Debug script to check Mapbox settings
 * Run with: wp eval-file scripts/debug-mapbox.php
 */

echo "\n=== Mapbox Settings Debug ===\n\n";

$options = [
    'tvs_mapbox_access_token',
    'tvs_mapbox_map_style',
    'tvs_mapbox_initial_zoom',
    'tvs_mapbox_min_zoom',
    'tvs_mapbox_max_zoom',
    'tvs_mapbox_pitch',
    'tvs_mapbox_bearing',
    'tvs_mapbox_default_speed',
    'tvs_mapbox_camera_offset',
    'tvs_mapbox_smooth_factor',
    'tvs_mapbox_marker_color',
    'tvs_mapbox_route_color',
    'tvs_mapbox_route_width',
    'tvs_mapbox_terrain_enabled',
    'tvs_mapbox_terrain_exaggeration',
];

foreach ($options as $option) {
    $value = get_option($option, '__NOT_SET__');
    $display = $value === '__NOT_SET__' ? '[NOT SET]' : $value;
    
    // Mask token for security (show first/last 4 chars)
    if ($option === 'tvs_mapbox_access_token' && $value !== '__NOT_SET__' && strlen($value) > 8) {
        $display = substr($value, 0, 4) . '...' . substr($value, -4);
    }
    
    echo sprintf("%-40s : %s\n", $option, $display);
}

echo "\n=== End Debug ===\n";
