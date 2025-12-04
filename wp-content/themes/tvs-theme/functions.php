<?php
// Minimal theme bootstrap for Norway Virtual Sport

require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/rest.php';
require get_template_directory() . '/inc/blocks.php';
require get_template_directory() . '/inc/media.php';

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

    // User profile page (English)
    $ensure_page( 'user-profile', 'User profile' );
    // Auth entry points
    $ensure_page( 'login', 'Login' );
    $ensure_page( 'register', 'Register' );
} );

// Proactively hide any existing legacy /connect-strava page from navigation
add_action( 'init', function() {
    // Hide legacy connect-strava page if present
    $page = get_page_by_path( 'connect-strava' );
    if ( $page && isset( $page->ID ) && $page->post_status === 'publish' ) {
        wp_update_post( [ 'ID' => $page->ID, 'post_status' => 'private' ] );
    }
    // Hide legacy Norwegian profile page if present
    $no_page = get_page_by_path( 'min-profil' );
    if ( $no_page && isset( $no_page->ID ) && $no_page->post_status === 'publish' ) {
        wp_update_post( [ 'ID' => $no_page->ID, 'post_status' => 'private' ] );
    }
}, 20 );

// Intercept /connect-strava/ and render a minimal callback shell (no header/footer/nav)
add_action( 'template_redirect', function() {
    // Handle virtual endpoint without requiring a WP Page
    $path = '';
    if ( isset( $GLOBALS['wp'] ) && isset( $GLOBALS['wp']->request ) ) {
        $path = trim( (string) $GLOBALS['wp']->request, '/' );
    } else {
        $req_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        $path    = trim( parse_url( $req_uri, PHP_URL_PATH ), '/' );
    }

    // Redirect logged-in users away from auth entry points
    if ( is_user_logged_in() && ( $path === 'login' || $path === 'register' ) ) {
        wp_safe_redirect( home_url( '/user-profile/' ) );
        exit;
    }
    if ( $path === 'connect-strava' ) {
        // Only allow when handling OAuth or popup handshake
        $has_code = isset($_GET['code']) && is_string($_GET['code']) && $_GET['code'] !== '';
        $is_popup = isset($_GET['mode']) && $_GET['mode'] === 'popup';
        if ( ! $has_code && ! $is_popup ) {
            wp_safe_redirect( home_url( '/' ) );
            exit;
        }

        // Minimal document output
        header( 'X-Robots-Tag: noindex, nofollow', true );
        show_admin_bar( false );
        nocache_headers();

        $rest  = esc_url_raw( get_rest_url() );
        $nonce = wp_create_nonce( 'wp_rest' );
        $script_src = esc_url( get_theme_file_uri( 'assets/strava-connect.js' ) );
        $lang_attrs = function_exists('language_attributes') ? get_language_attributes() : '';
        $charset    = get_bloginfo( 'charset' );
          echo '<!DOCTYPE html>'
           . '<html ' . $lang_attrs . '>'
           . '<head>'
              . '<meta charset="' . esc_attr($charset) . '" />'
              . '<meta name="viewport" content="width=device-width, initial-scale=1" />'
              . '<meta name="robots" content="noindex, nofollow" />'
              . '<title>' . esc_html__( 'Connecting to Strava…', 'tvs-virtual-sports' ) . '</title>'
           . '<style>html,body{height:100%;margin:0;background:#0a0a0b;color:#fff;font:16px/1.5 -apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,\"Helvetica Neue\",Arial,sans-serif}.tvs-connect-shell{min-height:100%;display:grid;place-items:center;padding:2rem}#strava-status{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:1.25rem 1.5rem;max-width:520px;width:100%;box-shadow:0 8px 24px rgba(0,0,0,.35)}#strava-status p{margin:0}</style>'
           . '</head>'
           . '<body class="tvs-connect-strava-min">'
              . '<main class="tvs-connect-shell"><div id="strava-status"><p>' . esc_html__( 'Connecting to Strava…', 'tvs-virtual-sports' ) . '</p></div></main>'
           . '<script>window.TVS_SETTINGS={restRoot:' . wp_json_encode( $rest ) . ',nonce:' . wp_json_encode( $nonce ) . '};</script>'
           . '<script src="' . $script_src . '"></script>'
           . '</body></html>';
        exit;
    }
} );
