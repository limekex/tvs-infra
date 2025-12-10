<?php
/**
 * Setup page protection meta for TVS pages
 * Run with: make wp cmd="eval-file /scripts/setup-page-protection.php"
 */

if ( ! defined( 'ABSPATH' ) ) {
    echo "Must be run from WordPress context\n";
    exit( 1 );
}

$protected_pages = array(
    'my-activities' => array(
        'title' => 'My Activities',
        'requires_auth' => true,
        'hide_from_nav' => true,
    ),
    'dashboard' => array(
        'title' => 'Dashboard',
        'requires_auth' => true,
        'hide_from_nav' => true,
    ),
    'user-profile' => array(
        'title' => 'User Profile',
        'requires_auth' => true,
        'hide_from_nav' => true,
    ),
    'min-profil' => array(
        'title' => 'Min Profil',
        'requires_auth' => true,
        'hide_from_nav' => true,
    ),
);

echo "Setting up page protection meta...\n\n";

foreach ( $protected_pages as $slug => $settings ) {
    $page = get_page_by_path( $slug );
    
    if ( ! $page ) {
        // Create the page if it doesn't exist
        $page_id = wp_insert_post( array(
            'post_type'   => 'page',
            'post_status' => 'publish',
            'post_title'  => $settings['title'],
            'post_name'   => $slug,
        ) );
        
        if ( is_wp_error( $page_id ) ) {
            echo "❌ Failed to create page: {$slug}\n";
            continue;
        }
        
        $page = get_post( $page_id );
        echo "📄 Created page: {$slug} (ID: {$page_id})\n";
    }
    
    $requires_auth = $settings['requires_auth'] ? '1' : '0';
    $hide_from_nav = $settings['hide_from_nav'] ? '1' : '0';
    
    update_post_meta( $page->ID, 'tvs_requires_auth', $requires_auth );
    update_post_meta( $page->ID, 'tvs_hide_from_nav', $hide_from_nav );
    
    echo "✅ {$slug} (ID: {$page->ID})\n";
    echo "   - Requires auth: " . ( $requires_auth === '1' ? 'YES' : 'NO' ) . "\n";
    echo "   - Hide from nav: " . ( $hide_from_nav === '1' ? 'YES' : 'NO' ) . "\n\n";
}

echo "Done! Page protection meta configured.\n";
