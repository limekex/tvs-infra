<?php
/**
 * Template: Connect Strava Minimal Callback
 *
 * Used for the /connect-strava/ page. Renders a minimal shell without theme header/footer,
 * loads the Strava callback script, and immediately handles OAuth code exchange.
 *
 * This page is intended to open either as a full page (fallback) or in a popup (?mode=popup).
 */

// Prevent search engines from indexing this utility page
header( 'X-Robots-Tag: noindex, nofollow', true );
?><!DOCTYPE html>

// Hide the admin bar to keep the surface minimal
add_filter( 'show_admin_bar', '__return_false' );

// Only allow access in popup mode or when handling an OAuth code
// If neither code nor mode=popup is present, redirect away to home
if ( empty( $_GET['code'] ) && ( empty( $_GET['mode'] ) || $_GET['mode'] !== 'popup' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	exit;
}
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex, nofollow" />
	<title><?php esc_html_e( 'Connecting to Strava…', 'tvs-theme' ); ?></title>
	<?php wp_head(); ?>
	<style>
		/* Minimal style just to center a status message */
		html, body { height:100%; margin:0; background: #0a0a0b; color:#fff; font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
		.tvs-connect-shell { min-height: 100%; display: grid; place-items: center; padding: 2rem; }
		#strava-status { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); border-radius: 12px; padding: 1.25rem 1.5rem; max-width: 520px; width: 100%; box-shadow: 0 8px 24px rgba(0,0,0,.35); }
		#strava-status p { margin: 0; }
	</style>
</head>
<body <?php body_class('tvs-connect-strava-min'); ?>>
	<main class="tvs-connect-shell">
		<div id="strava-status">
			<p><?php esc_html_e('Connecting to Strava…', 'tvs-theme'); ?></p>
		</div>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
