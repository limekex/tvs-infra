<?php
/**
 * Media-related tweaks: allow SVG uploads (for lightweight graphics/logos).
 * Scoped to users with upload capability; optionally restrict to admins for extra safety.
 */

// 1) Allow SVG mime type in uploads
add_filter( 'upload_mimes', function( $mimes ) {
	// Optionally restrict to admins only:
	if ( function_exists( 'current_user_can' ) && ! current_user_can( 'manage_options' ) ) {
		return $mimes; // Non-admins unchanged
	}
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
} );

// 2) Correct filetype and ext detection for SVGs in some WP versions
add_filter( 'wp_check_filetype_and_ext', function( $data, $file, $filename, $mimes ) {
	if ( isset( $data['ext'] ) && $data['ext'] ) {
		return $data;
	}
	$ext = pathinfo( $filename, PATHINFO_EXTENSION );
	if ( in_array( strtolower( $ext ), array( 'svg', 'svgz' ), true ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}, 10, 4 );

// 3) Optional: make SVGs display in media library with proper dimensions (fallback)
add_filter( 'wp_generate_attachment_metadata', function( $metadata, $attachment_id ) {
	$mime = get_post_mime_type( $attachment_id );
	if ( $mime === 'image/svg+xml' && ( ! isset( $metadata['width'] ) || ! isset( $metadata['height'] ) ) ) {
		// Provide a sensible default to avoid zero-dimension issues in some UIs
		$metadata['width']  = isset( $metadata['width'] ) ? $metadata['width'] : 512;
		$metadata['height'] = isset( $metadata['height'] ) ? $metadata['height'] : 512;
	}
	return $metadata;
}, 10, 2 );
