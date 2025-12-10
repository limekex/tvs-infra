<?php
/**
 * Register block metadata for blocks in the /blocks directory.
 * Uses register_block_type_from_metadata when available (WP 5.5+).
 */

add_action( 'init', function() {
    // Register a shared editor script for all TVS blocks, built via @wordpress/scripts
    $build_js     = get_template_directory() . '/build/index.js';
    $build_asset  = get_template_directory() . '/build/index.asset.php';
    if ( file_exists( $build_js ) ) {
        $deps = array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor' );
        $ver  = filemtime( $build_js );
        if ( file_exists( $build_asset ) ) {
            $asset = include $build_asset;
            if ( is_array( $asset ) ) {
                $deps = isset( $asset['dependencies'] ) ? $asset['dependencies'] : $deps;
                $ver  = isset( $asset['version'] ) ? $asset['version'] : $ver;
            }
        }
        wp_register_script(
            'tvs-theme-blocks',
            get_theme_file_uri( 'build/index.js' ),
            $deps,
            $ver,
            true
        );
    }
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


