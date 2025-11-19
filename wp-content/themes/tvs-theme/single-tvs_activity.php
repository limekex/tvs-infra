<?php
/**
 * Single template for TVS Activity (tvs_activity)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Render header for block theme via template part (header.html). If unavailable, fall back to classic get_header().
// Ensure full WP head pipeline runs.
get_header();

while ( have_posts() ) : the_post();
  $id   = get_the_ID();
  $meta = get_post_meta( $id );
  $title = get_the_title();
  $date  = get_the_date( 'F j, Y' );
  $distance_m = isset($meta['distance_m'][0]) ? (float)$meta['distance_m'][0] : ( isset($meta['_tvs_distance_m'][0]) ? (float)$meta['_tvs_distance_m'][0] : 0 );
  $duration_s = isset($meta['duration_s'][0]) ? (int)$meta['duration_s'][0] : ( isset($meta['_tvs_duration_s'][0]) ? (int)$meta['_tvs_duration_s'][0] : 0 );
  $route_name = isset($meta['route_name'][0]) ? $meta['route_name'][0] : ( isset($meta['_tvs_route_name'][0]) ? $meta['_tvs_route_name'][0] : '' );
  $synced     = isset($meta['synced_strava'][0]) ? $meta['synced_strava'][0] : ( isset($meta['_tvs_synced_strava'][0]) ? $meta['_tvs_synced_strava'][0] : '' );
  $strava_id  = isset($meta['strava_activity_id'][0]) ? $meta['strava_activity_id'][0] : ( isset($meta['_tvs_strava_remote_id'][0]) ? $meta['_tvs_strava_remote_id'][0] : '' );

  $km = $distance_m > 0 ? number_format( $distance_m / 1000, 2 ) : '—';
  $min = $duration_s > 0 ? floor( $duration_s / 60 ) : '—';
?>
  <main class="tvs-app tvs-activity">
    <header class="tvs-panel" style="margin-bottom: var(--tvs-space-4, 1rem);">
      <h1 style="margin:0 0 .25rem 0;"><?php echo esc_html( $title ?: ( $route_name ?: 'Activity' ) ); ?></h1>
      <div class="tvs-text-muted"><?php echo esc_html( $date ); ?></div>
    </header>

    <section class="tvs-panel" style="margin-bottom: var(--tvs-space-4, 1rem);">
      <div class="tvs-row" style="gap: var(--tvs-space-4, 1rem); flex-wrap: wrap;">
        <div>
          <div class="tvs-text-muted">Distance</div>
          <div style="font-size:1.25rem; font-weight:600;"><?php echo esc_html( $km ); ?> km</div>
        </div>
        <div>
          <div class="tvs-text-muted">Duration</div>
          <div style="font-size:1.25rem; font-weight:600;"><?php echo esc_html( $min ); ?> min</div>
        </div>
        <?php if ( $route_name ) : ?>
        <div>
          <div class="tvs-text-muted">Route</div>
          <div style="font-size:1.1rem; font-weight:600;"><?php echo esc_html( $route_name ); ?></div>
        </div>
        <?php endif; ?>
        <div>
          <div class="tvs-text-muted">Strava</div>
          <div>
            <?php if ( $synced === '1' || $synced === 1 ) : ?>
              <span class="tvs-badge tvs-badge-success">Synced</span>
              <?php if ( $strava_id ) : ?>
                <a class="tvs-link tvs-text-sm" target="_blank" rel="noopener" href="https://www.strava.com/activities/<?php echo esc_attr( $strava_id ); ?>" style="margin-left:.5rem;">View on Strava →</a>
              <?php endif; ?>
            <?php else : ?>
              <span class="tvs-badge tvs-badge-warning">Not synced</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="tvs-panel">
      <h2 style="margin-top:0;">Stats & Graphs</h2>
      <p class="tvs-text-muted">Coming soon: splits, pace, heart rate, elevation.</p>
    </section>
  </main>
<?php endwhile; 
// Render footer via block template part (footer.html) or fallback to classic footer.
get_footer();
