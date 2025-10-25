<?php
/**
 * Register block metadata for blocks in the /blocks directory.
 * Uses register_block_type_from_metadata when available (WP 5.5+).
 */

add_action( 'init', function() {
    $blocks_dir = get_template_directory() . '/blocks';
    if ( ! is_dir( $blocks_dir ) ) {
        return;
    }

    $entries = scandir( $blocks_dir );
    foreach ( $entries as $entry ) {
        if ( in_array( $entry, array( '.', '..' ), true ) ) {
            continue;
        }

        $block_path = $blocks_dir . '/' . $entry . '/block.json';
        if ( ! file_exists( $block_path ) ) {
            continue;
        }

        $meta_dir = dirname( $block_path );

        if ( function_exists( 'register_block_type_from_metadata' ) ) {
            // Preferred registration which parses block.json and loads build assets.
            register_block_type_from_metadata( $meta_dir );
        } else {
            // Fallback: attempt to register by path (older WP may still accept this).
            register_block_type( $meta_dir );
        }
    }
} );


