<?php
/** Enqueue theme assets */
add_action( 'wp_enqueue_scripts', function() {
    // 0) Design tokens (loaded first, globally)
    $tokens_path = get_template_directory() . '/assets/css/tvs-tokens.css';
    if ( file_exists( $tokens_path ) ) {
        wp_enqueue_style( 
            'tvs-tokens', 
            get_theme_file_uri( 'assets/css/tvs-tokens.css' ), 
            [], 
            filemtime( $tokens_path ) 
        );
    }

    // 1) Hoved-stylesheet (style.css) – versjoneres med filemtime
    $style_path = get_template_directory() . '/style.css';
    $style_ver  = file_exists( $style_path ) ? filemtime( $style_path ) : wp_get_theme()->get( 'Version' );
    wp_enqueue_style( 'nvs-style', get_stylesheet_uri(), ['tvs-tokens'], $style_ver );

    // 2) Registrer app-ressurser (JS/CSS) – lastes kun der vi trenger dem
    $theme      = wp_get_theme();
    $app_js_rel = 'assets/tvs-app.js';
    $app_css_rel= 'assets/tvs-app.css';
    $app_js_abs = get_template_directory() . '/' . $app_js_rel;
    $app_css_abs= get_template_directory() . '/' . $app_css_rel;

    if ( file_exists( $app_js_abs ) ) {
        // Registrer med korrekt avhengighet slik at React fra WP lastes (wp-element)
        wp_register_script(
            'tvs-app',
            get_theme_file_uri( $app_js_rel ),
            [ 'wp-element' ],
            filemtime( $app_js_abs ),
            true
        );

        // Dev/konfig inn i JS (gjør dette etter register, før enqueue)
        wp_localize_script( 'tvs-app', 'TVS_SETTINGS', [
            'env'      => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'development' : 'production',
            'restRoot' => get_rest_url(),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'version'  => $theme->get( 'Version' ),
            'user'     => is_user_logged_in() ? wp_get_current_user()->user_login : null,
        ] );
    }

    if ( file_exists( $app_css_abs ) ) {
        wp_register_style(
            'tvs-app-style',
            get_theme_file_uri( $app_css_rel ),
            [],
            filemtime( $app_css_abs )
        );
    }

    // Enqueue app scripts/styles on pages that rely on TVS_SETTINGS (e.g., min-profil, my-activities)
    if ( function_exists('is_page') && ( is_page( 'min-profil' ) || is_page( 'my-activities' ) ) ) {
        if ( wp_script_is( 'tvs-app', 'registered' ) ) {
            wp_enqueue_script( 'tvs-app' );
        }
        if ( wp_style_is( 'tvs-app-style', 'registered' ) ) {
            wp_enqueue_style( 'tvs-app-style' );
        }
    }

    // 3) Enqueue strava-connect.js only on Strava Connected page (slug: connect-strava)
    if ( is_page( 'connect-strava' ) ) {
        $strava_js_rel = 'assets/strava-connect.js';
        $strava_js_abs = get_template_directory() . '/' . $strava_js_rel;
        $strava_btn_js_rel = 'assets/strava-button.js';
        $strava_btn_js_abs = get_template_directory() . '/' . $strava_btn_js_rel;
        
        if ( file_exists( $strava_js_abs ) ) {
            wp_register_script(
                'strava-connect',
                get_theme_file_uri( $strava_js_rel ),
                [],
                filemtime( $strava_js_abs ),
                true
            );
            // Localize nonce, restRoot, and Strava client ID for JS
            $strava_client_id = get_option( 'tvs_strava_client_id', '' );
            wp_localize_script( 'strava-connect', 'TVS_SETTINGS', [
                'restRoot' => get_rest_url(),
                'nonce'    => wp_create_nonce( 'wp_rest' ),
                'stravaClientId' => $strava_client_id,
                'siteUrl' => home_url(),
            ] );
            wp_enqueue_script( 'strava-connect' );
        }
        
        // Enqueue button builder script
        if ( file_exists( $strava_btn_js_abs ) ) {
            wp_enqueue_script(
                'strava-button',
                get_theme_file_uri( $strava_btn_js_rel ),
                ['strava-connect'],
                filemtime( $strava_btn_js_abs ),
                true
            );
        }
    }
} );
