<?php
// Minimal theme bootstrap for Norway Virtual Sport

require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/rest.php';
require get_template_directory() . '/inc/blocks.php';
require get_template_directory() . '/inc/media.php';

// Add meta box for page protection settings
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'tvs_page_protection',
        'TVS Page Protection',
        function( $post ) {
            wp_nonce_field( 'tvs_page_protection_nonce', 'tvs_page_protection_nonce' );
            $requires_auth = get_post_meta( $post->ID, 'tvs_requires_auth', true ) === '1';
            $hide_from_nav = get_post_meta( $post->ID, 'tvs_hide_from_nav', true ) === '1';
            ?>
            <p>
                <label>
                    <input type="checkbox" name="tvs_requires_auth" value="1" <?php checked( $requires_auth ); ?>>
                    <strong>Require login to view this page</strong>
                </label>
                <br><small>Non-logged-in users will see a login prompt instead of the page content.</small>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="tvs_hide_from_nav" value="1" <?php checked( $hide_from_nav ); ?>>
                    <strong>Hide from navigation when user is not logged in</strong>
                </label>
                <br><small>This page will not appear in menus/navigation for non-logged-in users.</small>
            </p>
            <?php
        },
        'page',
        'side',
        'high'
    );
} );

// Save meta box data
add_action( 'save_post_page', function( $post_id ) {
    if ( ! isset( $_POST['tvs_page_protection_nonce'] ) || ! wp_verify_nonce( $_POST['tvs_page_protection_nonce'], 'tvs_page_protection_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_page', $post_id ) ) {
        return;
    }
    
    // Save requires_auth
    $requires_auth = isset( $_POST['tvs_requires_auth'] ) && $_POST['tvs_requires_auth'] === '1' ? '1' : '0';
    update_post_meta( $post_id, 'tvs_requires_auth', $requires_auth );
    
    // Save hide_from_nav
    $hide_from_nav = isset( $_POST['tvs_hide_from_nav'] ) && $_POST['tvs_hide_from_nav'] === '1' ? '1' : '0';
    update_post_meta( $post_id, 'tvs_hide_from_nav', $hide_from_nav );
} );

// Filter navigation menu items to hide protected pages (classic menus)
add_filter( 'wp_nav_menu_objects', function( $items ) {
    if ( is_user_logged_in() ) {
        return $items; // Show all items to logged-in users
    }
    
    foreach ( $items as $key => $item ) {
        if ( $item->object === 'page' && isset( $item->object_id ) ) {
            $hide_from_nav = get_post_meta( $item->object_id, 'tvs_hide_from_nav', true ) === '1';
            if ( $hide_from_nav ) {
                unset( $items[ $key ] );
            }
        }
    }
    
    return $items;
}, 10, 1 );

// Filter Navigation block items to hide protected pages (FSE/Gutenberg)
add_filter( 'render_block', function( $block_content, $block ) {
    // Only process Navigation blocks
    if ( $block['blockName'] !== 'core/navigation' ) {
        return $block_content;
    }
    
    // Parse the HTML to find page links
    if ( empty( $block_content ) || ! class_exists( 'DOMDocument' ) ) {
        return $block_content;
    }
    
    $dom = new DOMDocument();
    @$dom->loadHTML( '<?xml encoding="UTF-8">' . $block_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    $xpath = new DOMXPath( $dom );
    
    // Find all links
    $links = $xpath->query( '//a[@href]' );
    $nodes_to_remove = [];
    
    // Hide protected pages from non-logged-in users
    if ( ! is_user_logged_in() ) {
        foreach ( $links as $link ) {
            $href = $link->getAttribute( 'href' );
            
            // Extract page ID from URL
            $page_id = url_to_postid( $href );
            
            if ( $page_id ) {
                $hide_from_nav = get_post_meta( $page_id, 'tvs_hide_from_nav', true ) === '1';
                
                if ( $hide_from_nav ) {
                    // Find the parent <li> element
                    $li = $link->parentNode;
                    while ( $li && $li->nodeName !== 'li' ) {
                        $li = $li->parentNode;
                    }
                    if ( $li ) {
                        $nodes_to_remove[] = $li;
                    }
                }
            }
        }
    }
    
    // Remove nodes after iteration to avoid "Node no longer exists" error
    foreach ( $nodes_to_remove as $node ) {
        if ( $node->parentNode ) {
            $node->parentNode->removeChild( $node );
        }
    }
    
    // Hide/show Login, Register and Logout links based on auth state
    if ( is_user_logged_in() ) {
        // For logged-in users: hide Login and Register links
        $links = $xpath->query( '//a[@href]' );
        $nodes_to_remove = [];
        foreach ( $links as $link ) {
            $href = $link->getAttribute( 'href' );
            // Check if this is a login or register link - hide it
            if ( strpos( $href, '/login/' ) !== false || strpos( $href, '/login' ) !== false ||
                 strpos( $href, '/register/' ) !== false || strpos( $href, '/register' ) !== false ) {
                // Find parent <li> and mark for removal
                $parent = $link->parentNode;
                while ( $parent && $parent->nodeName !== 'li' ) {
                    $parent = $parent->parentNode;
                }
                if ( $parent && $parent->nodeName === 'li' ) {
                    $nodes_to_remove[] = $parent;
                }
            }
        }
        // Remove collected nodes
        foreach ( $nodes_to_remove as $node ) {
            if ( $node->parentNode ) {
                $node->parentNode->removeChild( $node );
            }
        }
    } else {
        // For non-logged-in users: hide Logout link
        $links = $xpath->query( '//a[@href]' );
        $nodes_to_remove = [];
        foreach ( $links as $link ) {
            $href = $link->getAttribute( 'href' );
            // Check if this is a logout link - hide it
            if ( strpos( $href, '/logout/' ) !== false || strpos( $href, '/logout' ) !== false ) {
                // Find parent <li> and mark for removal
                $parent = $link->parentNode;
                while ( $parent && $parent->nodeName !== 'li' ) {
                    $parent = $parent->parentNode;
                }
                if ( $parent && $parent->nodeName === 'li' ) {
                    $nodes_to_remove[] = $parent;
                }
            }
        }
        // Remove collected nodes
        foreach ( $nodes_to_remove as $node ) {
            if ( $node->parentNode ) {
                $node->parentNode->removeChild( $node );
            }
        }
    }
    
    return $dom->saveHTML();
}, 10, 2 );

// Protect pages that require authentication
add_action( 'template_redirect', function() {
    if ( is_user_logged_in() ) {
        return;
    }
    
    if ( ! is_page() ) {
        return;
    }
    
    $page_id = get_the_ID();
    if ( ! $page_id ) {
        return;
    }
    
    $requires_auth = get_post_meta( $page_id, 'tvs_requires_auth', true );
    
    if ( $requires_auth === '1' ) {
        // Redirect to login page with return URL
        $current_url = esc_url_raw( $_SERVER['REQUEST_URI'] ?? '/' );
        $login_url = home_url( '/login/?redirect=' . urlencode( $current_url ) );
        wp_safe_redirect( $login_url );
        exit;
    }
}, 5 );

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
