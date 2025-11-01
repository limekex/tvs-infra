<?php
// Minimal theme bootstrap for Norway Virtual Sport

require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/rest.php';
require get_template_directory() . '/inc/blocks.php';

add_action( 'after_setup_theme', function() {
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-font-sizes' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'block-templates' );
    add_theme_support( 'post-thumbnails' );
    // Other supports can be added as needed
} );

// Ensure key pages exist in development
add_action( 'init', function() {
    // Only run on frontend requests
    if ( is_admin() ) {
        return;
    }

    // Helper to create a page if it doesn't exist
    $ensure_page = function( $slug, $title ) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            wp_insert_post( array(
                'post_type'   => 'page',
                'post_status' => 'publish',
                'post_title'  => $title,
                'post_name'   => $slug,
            ) );
        }
    };

    // Min profil page (for Strava status)
    $ensure_page( 'min-profil', 'Min profil' );

    // Connect Strava page (OAuth landing)
    $ensure_page( 'connect-strava', 'Koble til Strava' );
} );
