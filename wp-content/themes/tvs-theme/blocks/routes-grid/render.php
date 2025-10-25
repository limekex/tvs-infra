<?php
/**
 * Server-side render for Routes Grid block
 */
$args = [
	'post_type'      => 'tvs_route',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
];
$q = new WP_Query($args);

if (!$q->have_posts()) {
	echo '<p>No routes available yet.</p>';
	return;
}

echo '<div class="tvs-routes-grid">';
while ($q->have_posts()) {
	$q->the_post();
	$id    = get_the_ID();
	$title = get_the_title();
	$link  = get_permalink();
	$dist  = get_post_meta($id, '_tvs_distance_m', true);
	$elev  = get_post_meta($id, '_tvs_elevation_m', true);
	$loc   = get_post_meta($id, '_tvs_location', true);

	echo '<article class="tvs-route-card">';
	echo '<h3><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h3>';
	if ($loc) echo '<p class="route-loc">' . esc_html($loc) . '</p>';
	if ($dist) echo '<p class="route-meta">' . intval($dist) . ' m distance';
	if ($elev) echo ' · ' . intval($elev) . ' m elevation</p>';
	echo '</article>';
}
echo '</div>';
wp_reset_postdata();
