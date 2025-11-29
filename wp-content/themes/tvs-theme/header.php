<?php
/**
 * Theme header: outputs <head> with wp_head() and begins <body>.
 * Also renders the block template part parts/header.html when present.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
// Render header template part from parts/header.html (block theme style) if available.
if ( function_exists( 'do_blocks' ) ) {
    echo do_blocks( '<!-- wp:template-part {"slug":"header"} /-->' );
}
?>
