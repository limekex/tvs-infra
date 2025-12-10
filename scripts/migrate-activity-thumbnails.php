<?php
/**
 * Migration Script: Set Featured Images on Activities
 * 
 * Sets featured images (_thumbnail_id) on activities that have
 * _tvs_map_image_attachment_id but no featured image set yet.
 * 
 * Usage:
 *   docker compose run --rm cli eval-file /scripts/migrate-activity-thumbnails.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/var/www/html/' );
    require_once ABSPATH . 'wp-load.php';
}

echo "=== TVS Activity Thumbnail Migration ===\n\n";

// Get all activities
$args = array(
    'post_type'      => 'tvs_activity',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
);

$activity_ids = get_posts( $args );
$total = count( $activity_ids );

echo "Found {$total} activities to check.\n\n";

$migrated = 0;
$skipped = 0;
$errors = 0;

foreach ( $activity_ids as $activity_id ) {
    // Check if already has featured image
    $existing_thumbnail = get_post_thumbnail_id( $activity_id );
    if ( $existing_thumbnail ) {
        $skipped++;
        continue;
    }
    
    // Check if has map image attachment
    $attachment_id = get_post_meta( $activity_id, '_tvs_map_image_attachment_id', true );
    if ( ! $attachment_id ) {
        $skipped++;
        continue;
    }
    
    // Verify attachment exists
    if ( ! get_post( $attachment_id ) ) {
        echo "⚠️  Activity {$activity_id}: Attachment {$attachment_id} not found\n";
        $errors++;
        continue;
    }
    
    // Set as featured image
    $result = set_post_thumbnail( $activity_id, $attachment_id );
    
    if ( $result ) {
        $activity_title = get_the_title( $activity_id );
        echo "✅ Activity {$activity_id} ({$activity_title}): Set thumbnail {$attachment_id}\n";
        $migrated++;
    } else {
        echo "❌ Activity {$activity_id}: Failed to set thumbnail\n";
        $errors++;
    }
}

echo "\n=== Migration Complete ===\n";
echo "Total activities: {$total}\n";
echo "Migrated: {$migrated}\n";
echo "Skipped (already had thumbnail or no attachment): {$skipped}\n";
echo "Errors: {$errors}\n";
