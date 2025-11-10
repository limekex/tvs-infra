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

    // 2) App script is now handled by the plugin (tvs-virtual-sports)
    //    Theme only enqueues it on specific pages below
    // Removed theme-level registration to avoid conflicts with plugin version
    /*
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
    */

    // Note: tvs-app is now entirely handled by the plugin
    // Removed theme-level enqueue as these pages use tvs-block-my-activities instead

    // 3) Enqueue Strava assets on connect/login/register pages
    if ( is_page( 'connect-strava' ) || is_page( 'login' ) || is_page( 'register' ) || ( function_exists('is_page_template') && is_page_template('templates/page-register.html') ) ) {
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
            // Localize nonce, restRoot, Strava client ID, and official button image URL for JS
            $strava_client_id = get_option( 'tvs_strava_client_id', '' );
            // Build a robust public URL to the plugin asset (works with custom content dirs)
            if ( function_exists('content_url') ) {
                $strava_btn_img = content_url( 'plugins/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg' );
            } else {
                $strava_btn_img = home_url( '/wp-content/plugins/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg' );
            }
            // Add a cache-busting query string based on file mtime if possible
            $strava_img_abs = WP_PLUGIN_DIR . '/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg';
            if ( file_exists( $strava_img_abs ) && function_exists('add_query_arg') ) {
                $strava_btn_img = add_query_arg( 'ver', filemtime( $strava_img_abs ), $strava_btn_img );
            }
            wp_localize_script( 'strava-connect', 'TVS_SETTINGS', [
                'restRoot' => get_rest_url(),
                'nonce'    => wp_create_nonce( 'wp_rest' ),
                'stravaClientId' => $strava_client_id,
                'siteUrl' => home_url(),
                'stravaButtonImage' => $strava_btn_img,
                // Also include recaptchaSiteKey here to avoid overwriting later localizations
                'recaptchaSiteKey' => ( function(){
                    $opt = get_option( 'tvs_recaptcha_site_key', '' );
                    if ( ! empty( $opt ) ) return $opt;
                    if ( defined( 'TVS_RECAPTCHA_SITE_KEY' ) ) return TVS_RECAPTCHA_SITE_KEY;
                    return '';
                } )(),
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

    // 3b) Auth UI helpers (register page behaviors)
    if ( function_exists('is_page') && ( is_page('register') || is_page('login') ) ) {
        $auth_js_rel = 'assets/auth.js';
        $auth_js_abs = get_template_directory() . '/' . $auth_js_rel;
        $auth_css_rel = 'assets/auth.css';
        $auth_css_abs = get_template_directory() . '/' . $auth_css_rel;
        if ( file_exists( $auth_js_abs ) ) {
            wp_enqueue_script(
                'tvs-auth',
                get_theme_file_uri( $auth_js_rel ),
                [],
                filemtime( $auth_js_abs ),
                true
            );
            // Provide same Strava button image to avoid overriding the global TVS_SETTINGS without it
            if ( function_exists('content_url') ) {
                $strava_btn_img2 = content_url( 'plugins/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg' );
            } else {
                $strava_btn_img2 = home_url( '/wp-content/plugins/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg' );
            }
            $strava_img_abs2 = WP_PLUGIN_DIR . '/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg';
            if ( file_exists( $strava_img_abs2 ) && function_exists('add_query_arg') ) {
                $strava_btn_img2 = add_query_arg( 'ver', filemtime( $strava_img_abs2 ), $strava_btn_img2 );
            }
            wp_localize_script( 'tvs-auth', 'TVS_SETTINGS', [
                'restRoot' => get_rest_url(),
                'nonce'    => wp_create_nonce( 'wp_rest' ),
                'stravaButtonImage' => $strava_btn_img2,
                'inviteOnly' => (bool) get_option( 'tvs_invite_only', false ),
                // Expose reCAPTCHA v3 site key if configured (option or constant)
                'recaptchaSiteKey' => ( function(){
                    $opt = get_option( 'tvs_recaptcha_site_key', '' );
                    if ( ! empty( $opt ) ) return $opt;
                    if ( defined( 'TVS_RECAPTCHA_SITE_KEY' ) ) return TVS_RECAPTCHA_SITE_KEY;
                    return '';
                } )(),
            ] );
        }
        if ( file_exists( $auth_css_abs ) ) {
            wp_enqueue_style(
                'tvs-auth-style',
                get_theme_file_uri( $auth_css_rel ),
                [ 'tvs-tokens' ],
                filemtime( $auth_css_abs )
            );
        }
    }

    // 4) Global nav dropdown helper (sets header height var, ensures burger toggles)
    $nav_js_rel = 'assets/nav-dropdown.js';
    $nav_js_abs = get_template_directory() . '/' . $nav_js_rel;
    if ( file_exists( $nav_js_abs ) ) {
        wp_enqueue_script(
            'tvs-nav-dropdown',
            get_theme_file_uri( $nav_js_rel ),
            [],
            filemtime( $nav_js_abs ),
            true
        );
    }

    // 5) Favorites handler (bookmark toggle) – lightweight, enqueue globally
    $fav_js_rel = 'assets/favorites.js';
    $fav_js_abs = get_template_directory() . '/' . $fav_js_rel;
    if ( file_exists( $fav_js_abs ) ) {
        wp_enqueue_script(
            'tvs-favorites',
            get_theme_file_uri( $fav_js_rel ),
            [],
            filemtime( $fav_js_abs ),
            true
        );
        // Provide REST root + nonce + login flag
        if ( function_exists('content_url') ) {
            $strava_btn_img3 = content_url( 'plugins/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg' );
        } else {
            $strava_btn_img3 = home_url( '/wp-content/plugins/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg' );
        }
        $strava_img_abs3 = WP_PLUGIN_DIR . '/tvs-virtual-sports/assets/img/btn_strava_connect_with_orange.svg';
        if ( file_exists( $strava_img_abs3 ) && function_exists('add_query_arg') ) {
            $strava_btn_img3 = add_query_arg( 'ver', filemtime( $strava_img_abs3 ), $strava_btn_img3 );
        }
        wp_localize_script( 'tvs-favorites', 'TVS_SETTINGS', [
            'restRoot' => get_rest_url(),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'user'     => is_user_logged_in() ? wp_get_current_user()->user_login : null,
            'stravaButtonImage' => $strava_btn_img3,
            // Keep recaptchaSiteKey in all TVS_SETTINGS localizations to avoid clobbering
            'recaptchaSiteKey' => ( function(){
                $opt = get_option( 'tvs_recaptcha_site_key', '' );
                if ( ! empty( $opt ) ) return $opt;
                if ( defined( 'TVS_RECAPTCHA_SITE_KEY' ) ) return TVS_RECAPTCHA_SITE_KEY;
                return '';
            } )(),
        ] );
    }
} );
