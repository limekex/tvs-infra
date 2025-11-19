<?php
/**
 * Theme footer: renders the footer template part and prints wp_footer(), closing body/html.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<?php
// Render footer template part from parts/footer.html (block theme style) if available.
if ( function_exists( 'do_blocks' ) ) {
    echo do_blocks( '<!-- wp:template-part {"slug":"footer"} /-->' );
}
?>
<?php wp_footer(); ?>
</body>
</html>
