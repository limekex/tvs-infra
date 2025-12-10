<?php
/**
 * Server-render for Route Card block
 */

$rid = isset( $attributes['routeId'] ) ? intval( $attributes['routeId'] ) : 0;
$showMeta = array_key_exists('showMeta',$attributes) ? (bool)$attributes['showMeta'] : true;
$showCTA  = array_key_exists('showCTA',$attributes) ? (bool)$attributes['showCTA'] : true;
$ctaLabel = isset($attributes['ctaLabel']) && $attributes['ctaLabel'] !== '' ? (string)$attributes['ctaLabel'] : __('View route','tvs-virtual-sports');
if ( $rid <= 0 ) {
    echo '<div class="tvs-route-card tvs-route-card--empty">' . esc_html__( 'Select a route ID in block settings.', 'tvs-virtual-sports' ) . '</div>';
    return;
}

$post = get_post( $rid );
if ( ! $post || $post->post_type !== 'tvs_route' ) {
    echo '<div class="tvs-route-card tvs-route-card--missing">' . esc_html__( 'Route not found.', 'tvs-virtual-sports' ) . '</div>';
    return;
}

$title = get_the_title( $rid );
$link  = get_permalink( $rid );
$img   = get_the_post_thumbnail_url( $rid, 'large' );
$dist  = get_post_meta( $rid, 'distance_m', true );
$elev  = get_post_meta( $rid, 'elevation_m', true );
$dur   = get_post_meta( $rid, 'duration_s', true );
$season= get_post_meta( $rid, 'season', true );
$diff  = get_post_meta( $rid, 'difficulty', true );

$terms = get_the_terms( $rid, 'tvs_region' );
$regions = array();
if ( $terms && ! is_wp_error( $terms ) ) {
    foreach ( $terms as $t ) { $regions[] = $t->name; }
}

// Helper functions with guards to avoid redeclaration fatals
if ( ! function_exists( 'tvs_rc_human_km' ) ) {
    function tvs_rc_human_km( $m ){
        $m = floatval( $m );
        if ( $m <= 0 ) return '';
        $km = $m / 1000;
        return ( $km >= 10 ) ? floor( $km ) . ' km' : number_format( $km, 1 ) . ' km';
    }
}
if ( ! function_exists( 'tvs_rc_human_dur' ) ) {
    function tvs_rc_human_dur( $s ){
        $s = intval( $s );
        if ( $s <= 0 ) return '';
        $m = floor( $s / 60 );
        if ( $m < 60 ) return $m . ' min';
        $h = floor( $m / 60 );
        $r = $m % 60;
        return sprintf( '%d:%02d h', $h, $r );
    }
}
if ( ! function_exists( 'tvs_rc_diff' ) ) {
    function tvs_rc_diff( $v ){
        $map = [ 'easy'=>1, 'moderate'=>2, 'medium'=>2, 'hard'=>3, 'difficult'=>3 ];
        $k = strtolower( trim( (string) $v ) );
        return $map[ $k ] ?? 0;
    }
}

$diff_count = tvs_rc_diff( $diff );

// Optional stats box placement: bottom (default), left, right
$metaPlacement = isset( $attributes['metaPlacement'] ) && in_array( $attributes['metaPlacement'], [ 'left','right','bottom' ], true )
    ? $attributes['metaPlacement']
    : 'bottom';

echo '<article class="tvs-route-card tvs-route-card--single">';
    // When CTA is visible, avoid wrapping the whole card in an anchor to prevent nested links
    $wrapAsLink = ! $showCTA; 
    if ( $wrapAsLink ) {
        echo '<a class="tvs-route-card__link" href="' . esc_url( $link ) . '">';
    } else {
        echo '<div class="tvs-route-card__link">';
    }
    echo '<div class="tvs-route-card__bg"' . ( $img ? ' style="background-image:url(' . esc_url( $img ) . ');"' : '' ) . '></div>';
    echo '<div class="tvs-route-card__overlay">';
        echo '<div class="tvs-route-card__top">';
            echo '<h3 class="tvs-route-card__title">' . esc_html( $title ) . '</h3>';
            $badges = array();
            if ( $season ) $badges[] = '<span class="tvs-badge tvs-badge--season">' . esc_html( ucfirst( $season ) ) . '</span>';
            if ( $regions ) $badges[] = '<span class="tvs-badge tvs-badge--region">' . esc_html( implode(', ', $regions) ) . '</span>';
            if ( $badges ) echo '<div class="tvs-route-card__badges">' . implode( '', $badges ) . '</div>';
            if ( $diff_count > 0 ) {
                echo '<div class="tvs-route-card__difficulty" aria-label="' . esc_attr__( 'Difficulty', 'tvs-virtual-sports' ) . ': ' . esc_attr( $diff ) . '">';
                for ( $i=0; $i<$diff_count; $i++ ) echo '<span class="tvs-diff-dot" aria-hidden="true"></span>';
                echo '</div>';
            }
        echo '</div>';
        if ( $showMeta ) {
            $meta = array();
            if ( $dist ) $meta[] = '<span class="meta-item meta-item--distance"><span class="meta-icon" aria-hidden="true">⟷</span>' . esc_html( tvs_rc_human_km($dist) ) . '</span>';
            if ( $elev ) $meta[] = '<span class="meta-item meta-item--elevation"><span class="meta-icon" aria-hidden="true">⛰</span>' . esc_html( intval($elev) ) . ' m</span>';
            if ( $dur )  $meta[] = '<span class="meta-item meta-item--duration"><span class="meta-icon" aria-hidden="true">⏱</span>' . esc_html( tvs_rc_human_dur($dur) ) . '</span>';
            if ( $meta ) echo '<div class="tvs-route-card__stats tvs-route-card__stats--' . esc_attr( $metaPlacement ) . '">' . implode('', $meta) . '</div>';
        }
        // Visible CTA button/link (uses token styles) when enabled
        if ( $showCTA ) {
            echo '<div class="tvs-route-card__cta-wrap">'
                . '<a class="tvs-route-card__cta" href="' . esc_url( $link ) . '">' . esc_html( $ctaLabel ) . '</a>'
                . '</div>';
        }
    echo '</div>';
    if ( $wrapAsLink ) { echo '</a>'; } else { echo '</div>'; }
echo '</article>';
