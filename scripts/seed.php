<?php
// Seed-data for lokal utvikling/QA
// Kjør med: make seed (wp eval-file scripts/seed.php)
// Forutsetter at plugin og tema er aktivert

// Sikkerhet: bare i CLI/admin-kontekst
if ( defined('WP_CLI') && ! WP_CLI ) {
  echo "This script should be executed via WP-CLI.\n";
}

// Idempotens: merk alt vi lager med et batch-navn
$SEED_BATCH = 'tvs_seed_v1';

// Hjelper: lag term hvis den ikke finnes
function tvs_seed_ensure_term($slug, $taxonomy, $name = null) {
  $term = term_exists($slug, $taxonomy);
  if (!$term) {
    $args = ['slug' => $slug];
    if ($name) { $args['name'] = $name; }
    wp_insert_term($name ?: $slug, $taxonomy, $args);
  }
}

// Hjelper: slett tidligere seedet innhold
function tvs_seed_cleanup($batch) {
  $types = ['tvs_route','tvs_activity'];
  foreach ($types as $pt) {
    $posts = get_posts([
      'post_type'      => $pt,
      'posts_per_page' => 100,
      'post_status'    => 'any',
      'meta_key'       => 'seed_batch',
      'meta_value'     => $batch,
      'fields'         => 'ids',
    ]);
    foreach ($posts as $pid) {
      wp_delete_post($pid, true);
    }
  }
}

// Rydd opp forrige runde (gjør scriptet idempotent)
tvs_seed_cleanup($SEED_BATCH);

// Sørg for noen standard-termer
$activity_types = ['run','ride','walk'];
foreach ($activity_types as $t) {
  tvs_seed_ensure_term($t, 'tvs_activity_type');
}
$regions = ['tonsberg' => 'Tønsberg', 'oslo' => 'Oslo', 'bergen' => 'Bergen'];
foreach ($regions as $slug => $name) {
  tvs_seed_ensure_term($slug, 'tvs_region', $name);
}

// Sett sammen 6 ruter med variasjon i meta
$routes = [
  [
    'title' => 'Eik Forest Trail',
    'content' => 'Morning fog, mixed forest terrain.',
    'distance_m' => 6400, 'elevation_m' => 120, 'duration_s' => 2200,
    'video_provider' => 'vimeo', 'video_id' => '228740420', 'difficulty' => 'moderate',
    'region' => 'tonsberg', 'types' => ['run']
  ],
  [
    'title' => 'Fjordside Ride',
    'content' => 'Coastal views and gentle climbs.',
    'distance_m' => 18000, 'elevation_m' => 260, 'duration_s' => 3600,
    'video_provider' => 'vimeo', 'video_id' => '76979871', 'difficulty' => 'easy',
    'region' => 'oslo', 'types' => ['ride']
  ],
  [
    'title' => 'City Park Laps',
    'content' => 'Flat loops perfect for intervals.',
    'distance_m' => 5000, 'elevation_m' => 40, 'duration_s' => 1500,
    'video_provider' => 'vimeo', 'video_id' => '143418951', 'difficulty' => 'easy',
    'region' => 'oslo', 'types' => ['run','walk']
  ],
  [
    'title' => 'Mountain Edge Hike',
    'content' => 'Rocky sections with rewarding vistas.',
    'distance_m' => 12000, 'elevation_m' => 620, 'duration_s' => 7200,
    'video_provider' => 'vimeo', 'video_id' => '22439234', 'difficulty' => 'hard',
    'region' => 'bergen', 'types' => ['walk']
  ],
  [
    'title' => 'Lake Loop',
    'content' => 'Smooth loop around the lake.',
    'distance_m' => 8500, 'elevation_m' => 90, 'duration_s' => 2600,
    'video_provider' => 'vimeo', 'video_id' => '1084537', 'difficulty' => 'moderate',
    'region' => 'tonsberg', 'types' => ['ride','run']
  ],
  [
    'title' => 'Harbor Walk',
    'content' => 'Urban waterfront with cafes.',
    'distance_m' => 3200, 'elevation_m' => 10, 'duration_s' => 900,
    'video_provider' => 'vimeo', 'video_id' => '769798710', 'difficulty' => 'easy',
    'region' => 'bergen', 'types' => ['walk']
  ],
];

$route_ids = [];
foreach ($routes as $idx => $r) {
  $postarr = [
    'post_type'   => 'tvs_route',
    'post_status' => 'publish',
    'post_title'  => $r['title'],
    'post_name'   => sanitize_title('seed-route-'.($idx+1)),
    'post_content'=> $r['content'],
  ];
  $rid = wp_insert_post($postarr, true);
  if (is_wp_error($rid)) {
    echo 'Failed creating route: '.$r['title'].' - '.$rid->get_error_message()."\n";
    continue;
  }
  // REST-modell meta (uten prefiks)
  update_post_meta($rid, 'distance_m', $r['distance_m']);
  update_post_meta($rid, 'elevation_m', $r['elevation_m']);
  update_post_meta($rid, 'duration_s', $r['duration_s']);
  update_post_meta($rid, 'video_provider', $r['video_provider']);
  update_post_meta($rid, 'video_id', $r['video_id']);
  update_post_meta($rid, 'difficulty', $r['difficulty']);
  update_post_meta($rid, 'seed_batch', $SEED_BATCH);

  // Taxonomier
  if (!empty($r['region'])) {
    wp_set_post_terms($rid, [$r['region']], 'tvs_region', false);
  }
  if (!empty($r['types'])) {
    wp_set_post_terms($rid, $r['types'], 'tvs_activity_type', false);
  }

  $route_ids[] = $rid;
}

// Aktiviteter (2–3 stk), knyttet til noen av rutene
// Finn en eier: bruk første admin om mulig
$owner = 0;
$admins = get_users(['role' => 'administrator', 'number' => 1]);
if (!empty($admins)) {
  $owner = $admins[0]->ID;
}
if (! $owner) {
  $users = get_users(['number' => 1, 'orderby' => 'ID', 'order' => 'ASC']);
  if (!empty($users)) { $owner = $users[0]->ID; }
}

if (!empty($route_ids)) {
  $samples = array_slice($route_ids, 0, min(3, count($route_ids)));
  foreach ($samples as $rid) {
    $rname = get_the_title($rid);
    $post_id = wp_insert_post([
      'post_title'  => sprintf('Activity on %s', $rname),
      'post_type'   => 'tvs_activity',
      'post_status' => 'publish',
      'post_author' => $owner ?: 0,
    ], true);
    if (is_wp_error($post_id)) {
      echo 'Failed creating activity for route '.$rid.' - '.$post_id->get_error_message()."\n";
      continue;
    }
    update_post_meta($post_id, 'route_id', $rid);
    update_post_meta($post_id, 'route_name', $rname);
    update_post_meta($post_id, 'activity_date', date('Y-m-d'));
    update_post_meta($post_id, 'started_at', current_time('mysql'));
    update_post_meta($post_id, 'ended_at', current_time('mysql', true));
    update_post_meta($post_id, 'duration_s', rand(900, 5400));
    update_post_meta($post_id, 'distance_m', rand(2000, 20000));
    update_post_meta($post_id, 'perceived_exertion', rand(3, 8));
    update_post_meta($post_id, 'seed_batch', $SEED_BATCH);
  }
}

// Opprett en enkel side som viser første rute via shortcode
$page_id = 0;
if (!empty($route_ids)) {
  $page_id = wp_insert_post([
    'post_type'    => 'page',
    'post_status'  => 'publish',
    'post_title'   => 'TVS Dev Routes',
    'post_content' => '[tvs_route id="'.intval($route_ids[0]).'"]',
    'post_name'    => 'tvs-dev-routes',
  ], true);
}

// Oppsummering
echo "Seeded ".count($route_ids)." routes and ".count($samples ?? [])." activities.\n";
if ($page_id && !is_wp_error($page_id)) {
  echo "Created page ID: $page_id\n";
}
echo "Done.\n";
