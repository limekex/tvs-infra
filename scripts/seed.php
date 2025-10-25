<?php
// Kjør med: make seed
// Lager et par tvs_route + en side med shortcode
// Forutsetter at plugin er aktivert

// Tax terms
$types = ['run','ride','walk'];
foreach ($types as $t) {
  if (!term_exists($t, 'tvs_activity_type')) {
    wp_insert_term($t, 'tvs_activity_type', ['slug'=>$t]);
  }
}

// Opprett rute
$rute_id = wp_insert_post([
  'post_type' => 'tvs_route',
  'post_status' => 'publish',
  'post_title' => 'Eik Forest Trail',
  'post_content' => 'Morning fog, mixed forest terrain.',
]);
if ($rute_id && !is_wp_error($rute_id)) {
  update_post_meta($rute_id,'_tvs_distance_m', 6400);
  update_post_meta($rute_id,'_tvs_elevation_m', 120);
  update_post_meta($rute_id,'_tvs_duration_s', 2200);
  update_post_meta($rute_id,'_tvs_gpx_url', '');
  update_post_meta($rute_id,'_tvs_vimeo_id', ''); // fyll inn ved behov
  update_post_meta($rute_id,'_tvs_surface','trail');
  update_post_meta($rute_id,'_tvs_difficulty','moderate');
  update_post_meta($rute_id,'_tvs_location','Eik, Tønsberg');
  update_post_meta($rute_id,'_tvs_season','autumn');
  wp_set_post_terms($rute_id, ['run'], 'tvs_activity_type', false);
}

// Dev side med shortcode
$page_id = wp_insert_post([
  'post_type' => 'page','post_status' => 'publish',
  'post_title' => 'TVS Dev Route',
  'post_content' => $rute_id ? '[tvs_route id="'.$rute_id.'"]' : 'No route created',
]);
echo "Route ID: $rute_id\nPage ID: $page_id\n";
