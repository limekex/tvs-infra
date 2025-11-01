<?php
/**
 * Plugin Name: TVS Blocks (dev)
 * Plugin URI:  https://example.invalid/
 * Description: Companion plugin for Norway Virtual Sport providing dev REST endpoints and block helpers.
 * Version: 0.1.0
 * Author: Your Name
 */

add_action( 'rest_api_init', function() {
    register_rest_route( 'tvs/v1', '/activities/me', array(
        'methods' => 'GET',
        'callback' => function( $request ) {
            // Sample data for development
            return rest_ensure_response( array(
                'activities' => array(
                    array('id' => 1, 'route_id' => 101, 'distance_m' => 12000, 'duration_s' => 3600, 'synced' => false ),
                ),
                'paging' => array('page' => 1, 'per_page' => 20)
            ) );
        },
        'permission_callback' => function() { return true; }
    ) );

    register_rest_route( 'tvs/v1', '/strava-status', array(
        'methods' => 'GET',
        'callback' => function() {
            return rest_ensure_response( array('connected' => false, 'athlete' => null) );
        },
        'permission_callback' => function() { return true; }
    ) );
} );
