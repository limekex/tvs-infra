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
// Collect candidate attachments for thumbnails: original images only (not cropped variants like -150x150)
function tvs_seed_pick_random_attachment_id() {
  $atts = get_posts([
    'post_type'      => 'attachment',
    'post_status'    => 'inherit',
    'post_mime_type' => 'image',
    'posts_per_page' => 200,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'fields'         => 'ids',
  ]);
  if (empty($atts)) return 0;
  // Filter out cropped file names by inspecting _wp_attached_file meta
  $orig = [];
  foreach ($atts as $aid) {
    $file = get_post_meta($aid, '_wp_attached_file', true);
    if (!$file) continue;
    // Exclude e.g., name-300x200.jpg
    if (preg_match('/-\d+x\d+\.(jpe?g|png|gif|webp|avif)$/i', basename($file))) {
      continue;
    }
    $orig[] = $aid;
  }
  if (empty($orig)) return 0;
  return $orig[array_rand($orig)];
}

$routes = [
  [
    'title' => 'Eik Forest Trail',
    'content' => 'Morning fog, mixed forest terrain.',
    'distance_m' => 6400, 'elevation_m' => 120, 'duration_s' => 2200,
    'vimeo_id' => '228740420', 'gpx_url' => 'https://example.com/gpx/eik-forest.gpx',
    'surface' => 'trail', 'difficulty' => 'moderate', 'location' => 'Tønsberg', 'season' => 'autumn',
    'region' => 'tonsberg', 'types' => ['run']
  ],
  [
    'title' => 'Fjordside Ride',
    'content' => 'Coastal views and gentle climbs.',
    'distance_m' => 18000, 'elevation_m' => 260, 'duration_s' => 3600,
    'vimeo_id' => '76979871', 'gpx_url' => 'https://example.com/gpx/fjordside-ride.gpx',
    'surface' => 'asphalt', 'difficulty' => 'easy', 'location' => 'Oslo', 'season' => 'summer',
    'region' => 'oslo', 'types' => ['ride']
  ],
  [
    'title' => 'City Park Laps',
    'content' => 'Flat loops perfect for intervals.',
    'distance_m' => 5000, 'elevation_m' => 40, 'duration_s' => 1500,
    'vimeo_id' => '143418951', 'gpx_url' => 'https://example.com/gpx/city-park-laps.gpx',
    'surface' => 'asphalt', 'difficulty' => 'easy', 'location' => 'Oslo', 'season' => 'spring',
    'region' => 'oslo', 'types' => ['run','walk']
  ],
  [
    'title' => 'Mountain Edge Hike',
    'content' => 'Rocky sections with rewarding vistas.',
    'distance_m' => 12000, 'elevation_m' => 620, 'duration_s' => 7200,
    'vimeo_id' => '22439234', 'gpx_url' => 'https://example.com/gpx/mountain-edge.gpx',
    'surface' => 'rock', 'difficulty' => 'hard', 'location' => 'Bergen', 'season' => 'summer',
    'region' => 'bergen', 'types' => ['walk']
  ],
  [
    'title' => 'Lake Loop',
    'content' => 'Smooth loop around the lake.',
    'distance_m' => 8500, 'elevation_m' => 90, 'duration_s' => 2600,
    'vimeo_id' => '1084537', 'gpx_url' => 'https://example.com/gpx/lake-loop.gpx',
    'surface' => 'mixed', 'difficulty' => 'moderate', 'location' => 'Tønsberg', 'season' => 'summer',
    'region' => 'tonsberg', 'types' => ['ride','run']
  ],
  [
    'title' => 'Harbor Walk',
    'content' => 'Urban waterfront with cafes.',
    'distance_m' => 3200, 'elevation_m' => 10, 'duration_s' => 900,
    'vimeo_id' => '769798710', 'gpx_url' => 'https://example.com/gpx/harbor-walk.gpx',
    'surface' => 'boardwalk', 'difficulty' => 'easy', 'location' => 'Bergen', 'season' => 'autumn',
    'region' => 'bergen', 'types' => ['walk']
  ],
  // Additional routes to expand dataset
  [
    'title' => 'Riverbank Run',
    'content' => 'Shaded path along the river.',
    'distance_m' => 7200, 'elevation_m' => 55, 'duration_s' => 2400,
    'vimeo_id' => '76979999', 'gpx_url' => 'https://example.com/gpx/riverbank-run.gpx',
    'surface' => 'gravel', 'difficulty' => 'easy', 'location' => 'Oslo', 'season' => 'spring',
    'region' => 'oslo', 'types' => ['run']
  ],
  [
    'title' => 'Forest Ridge MTB',
    'content' => 'Technical singletrack with roots and rocks.',
    'distance_m' => 15000, 'elevation_m' => 410, 'duration_s' => 5400,
    'vimeo_id' => '228740421', 'gpx_url' => 'https://example.com/gpx/forest-ridge-mtb.gpx',
    'surface' => 'trail', 'difficulty' => 'hard', 'location' => 'Bergen', 'season' => 'summer',
    'region' => 'bergen', 'types' => ['ride']
  ],
  [
    'title' => 'Seaside Stroll',
    'content' => 'Windy day with waves crashing nearby.',
    'distance_m' => 4200, 'elevation_m' => 20, 'duration_s' => 1300,
    'vimeo_id' => '143418952', 'gpx_url' => 'https://example.com/gpx/seaside-stroll.gpx',
    'surface' => 'boardwalk', 'difficulty' => 'easy', 'location' => 'Tønsberg', 'season' => 'summer',
    'region' => 'tonsberg', 'types' => ['walk']
  ],
  [
    'title' => 'Urban Sprint',
    'content' => 'Short and fast downtown circuit.',
    'distance_m' => 3000, 'elevation_m' => 25, 'duration_s' => 900,
    'vimeo_id' => '22439235', 'gpx_url' => 'https://example.com/gpx/urban-sprint.gpx',
    'surface' => 'asphalt', 'difficulty' => 'moderate', 'location' => 'Oslo', 'season' => 'winter',
    'region' => 'oslo', 'types' => ['run']
  ],
  [
    'title' => 'Hill Repeats',
    'content' => 'Steady climb repeats with scenic overlooks.',
    'distance_m' => 6000, 'elevation_m' => 350, 'duration_s' => 3000,
    'vimeo_id' => '1084538', 'gpx_url' => 'https://example.com/gpx/hill-repeats.gpx',
    'surface' => 'asphalt', 'difficulty' => 'hard', 'location' => 'Oslo', 'season' => 'autumn',
    'region' => 'oslo', 'types' => ['run','ride']
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
  if (!empty($r['vimeo_id'])) update_post_meta($rid, 'vimeo_id', $r['vimeo_id']);
  if (!empty($r['gpx_url'])) update_post_meta($rid, 'gpx_url', $r['gpx_url']);
  if (!empty($r['surface'])) update_post_meta($rid, 'surface', $r['surface']);
  if (!empty($r['difficulty'])) update_post_meta($rid, 'difficulty', $r['difficulty']);
  if (!empty($r['location'])) update_post_meta($rid, 'location', $r['location']);
  if (!empty($r['season'])) update_post_meta($rid, 'season', $r['season']);
  update_post_meta($rid, 'seed_batch', $SEED_BATCH);

  // Taxonomier
  if (!empty($r['region'])) {
    wp_set_post_terms($rid, [$r['region']], 'tvs_region', false);
  }
  if (!empty($r['types'])) {
    wp_set_post_terms($rid, $r['types'], 'tvs_activity_type', false);
  }

  $route_ids[] = $rid;

  // Set random featured image from local uploads (originals only)
  $thumb = tvs_seed_pick_random_attachment_id();
  if ($thumb) {
    set_post_thumbnail($rid, $thumb);
  }
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
