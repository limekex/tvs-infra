<?php
/** Development REST endpoints for theme/plugin integration. For production, move to plugin. */

add_action( 'rest_api_init', function () {
    register_rest_route( 'tvs/v1', '/activities/me', array(
        'methods' => 'GET',
        'callback' => function( $request ) {
            // Mocked response for development
            return rest_ensure_response( array(
                'activities' => array(),
                'paging' => array( 'page' => 1 )
            ) );
        },
        'permission_callback' => '__return_true'
    ) );

    register_rest_route( 'tvs/v1', '/strava-status', array(
        'methods' => 'GET',
        'callback' => function() {
            return rest_ensure_response( array(
                'connected' => false,
                'athlete' => null
            ) );
        },
        'permission_callback' => '__return_true'
    ) );
} );
