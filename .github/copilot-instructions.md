# TVS Virtual Sports — AI Agent Instructions

## Project Overview

This is a WordPress-based virtual sports platform combining a custom plugin (`tvs-virtual-sports`) and FSE theme (`tvs-theme`). The plugin provides virtual route training with Vimeo video playback, activity tracking, Strava integration, and server-rendered Gutenberg blocks. The theme is a block-first (FSE) scaffold consuming the plugin's REST API.

**Key architecture**: Docker-based WordPress environment; React SPA embedded via PHP; custom post types for routes and activities; REST API under `tvs/v1` namespace.

## Critical Developer Workflows

### Environment Setup & Running
```bash
# Start WordPress + MariaDB containers
make up

# First-time setup (installs WordPress core)
make install

# Activate plugin & theme
make activate

# View logs
make logs

# Access WordPress CLI
make cli-shell

# Run WP-CLI commands
make wp cmd="plugin list"
```

**Environment URL**: `http://localhost:8080`  
**Admin**: `admin` / `admin` (configurable in Makefile)

### Plugin Development

**React/JS builds** (required after editing `src/`):
```bash
cd wp-content/plugins/tvs-virtual-sports

# Production build (minified)
npm run build

# Watch mode (rebuilds on save, use during development)
npm run dev
```

**Build output**: `public/js/tvs-app.js` and `public/js/tvs-block-*.js` (IIFE bundles)  
**Node version**: Pinned to 14.21.x for esbuild 0.17.19 compatibility.

**PHP tests** (PHPUnit 9.6):
```bash
cd wp-content/plugins/tvs-virtual-sports
vendor/bin/phpunit
```

Tests live in `tests/phpunit/`. Bootstrap expects WordPress test library at `/tmp/wordpress-tests-lib`.

### Debugging

**Enable debug mode**: Add `?tvsdebug=1` to URL, or press backtick (`` ` ``) in browser  
**Simulate slow REST calls**: Add `&tvsslow=500` (milliseconds)  
**Dev overlay**: Press backtick to toggle persistent debug UI (stored in localStorage)

**PHP errors**: Logged to `logs/php-errors.log` (mounted volume)

## Project-Specific Patterns

### Custom Post Types

**Routes** (`tvs_route`):
- Public, REST-enabled CPT for virtual routes
- Meta: `video_url`, `gpx_file_url`, `distance`, `elevation`, `surface`, `lat`, `lng`, `meta` (JSON)
- Registered via `includes/class-tvs-cpt-route.php`
- **Cache invalidation**: `tvs_routes_cache_buster` option updated on any route save

**Activities** (`tvs_activity`):
- Private CPT for user activity sessions
- Meta: `route_id`, `started_at`, `ended_at`, `duration_s`, `distance_m`, `visibility` (`public`/`private`)
- Privacy guard: Private activities return 404 to non-authors (`guard_activity_privacy()`)
- Title format: `"Fullført {date - time}"` (auto-generated)
- Slug: Numeric only (collision-resistant)

### REST API (`tvs/v1`)

**Routes**:
- `GET /tvs/v1/routes` — cached 5min, bypass with `?tvsforcefetch=1`
- `GET /tvs/v1/routes/{id}` — single route

**Activities**:
- `POST /tvs/v1/activities` — create activity (auth required)
- `GET /tvs/v1/activities/user/{user_id}` — user activities
- `PATCH /tvs/v1/activities/{id}` — update activity (author only)

**Favorites** (per-user, stored in `user_meta`):
- `GET /tvs/v1/favorites` — returns `{ ids: [1, 2, 3] }`
- `POST /tvs/v1/favorites/{id}` — toggle favorite
- `DELETE /tvs/v1/favorites/{id}` — remove favorite

**Strava**:
- `POST /tvs/v1/strava/connect` — OAuth callback
- `GET /tvs/v1/strava/status` — connection status
- `POST /tvs/v1/strava/upload` — upload activity

**PoIs** (Points of Interest):
- `GET /tvs/v1/routes/{id}/pois` — fetch PoIs for a route
- `POST /tvs/v1/routes/{id}/pois` — save PoIs (admin only)

**Authentication**: Use `X-WP-Nonce` header with `TVS_SETTINGS.nonce` (localized in PHP)

### React/Frontend Integration

**Entry points**:
- `src/index.js` → `src/boot.js` → `src/app.js` (main React app)
- Block views: `src/blocks/{block-name}/view.js`

**Global settings**: `window.TVS_SETTINGS` (localized via `wp_localize_script`):
```js
{
  env: 'development' | 'production',
  restRoot: '/wp-json/',
  nonce: 'abc123...',
  version: '1.2.585',
  user: 'username' | null,
  pluginUrl: 'http://localhost:8080/wp-content/plugins/tvs-virtual-sports/'
}
```

**Flash notifications**: Global singleton `window.tvs_flash.show(message, type)` (types: `success`, `error`, `info`)

**Dependencies**:
- React 18 (CDN: unpkg)
- Mapbox GL JS 3.0.1 (virtual training mode)
- GSAP 3.12.5 (animation)

### Gutenberg Blocks (Server-Rendered)

All blocks registered via PHP in `class-tvs-plugin.php`:
- `tvs-virtual-sports/my-activities` — recent activities list
- `tvs-virtual-sports/route-insights` — elevation, surface, ETA
- `tvs-virtual-sports/personal-records` — best time, avg pace
- `tvs-virtual-sports/activity-heatmap` — sparkline/heatmap
- `tvs-virtual-sports/route-weather` — MET Norway historical weather
- `tvs-virtual-sports/invite-friends` — invitation code generator (logged-in only)

**Block pattern**: PHP renders `<div id="unique-mount-id" data-*>` with attributes, JS mounts React to that element.

### Strava Integration

**OAuth flow**: User connects via WP Admin → TVS → Strava Settings. Exchange code via `POST /tvs/v1/strava/connect`.  
**Upload**: `POST /tvs/v1/strava/upload` with activity ID. Sends GPX + metadata to Strava API.  
**Credentials**: Stored in options (`tvs_strava_client_id`, `tvs_strava_client_secret`).

### Internationalization

**Text domain**: `tvs-virtual-sports` (plugin + theme blocks)  
**Translation files**: `languages/tvs-virtual-sports.pot` (generate with `wp i18n make-pot`)

### Testing

**Manual testing checklist**: `MANUAL_TESTING.md` (activity session, My Activities block, Strava sync)  
**PHPUnit**: REST endpoints, helpers, CPT registration  
**Future**: JS tests for utils (`withTimeout`) and smoke renders

## Key Files

- `wp-content/plugins/tvs-virtual-sports/tvs-virtual-sports.php` — plugin entry
- `wp-content/plugins/tvs-virtual-sports/includes/class-tvs-plugin.php` — main bootstrap
- `wp-content/plugins/tvs-virtual-sports/includes/class-tvs-rest.php` — REST controller (2676 lines)
- `wp-content/plugins/tvs-virtual-sports/src/app.js` — React video player + controls
- `Makefile` — Docker shortcuts (up, down, install, activate, seed, etc.)
- `docker-compose.yml` — WordPress 6.6 + MariaDB 10.6, mounts plugin/theme/uploads

## Common Pitfalls

- **esbuild fails**: Ensure Node 14.21.x (or update esbuild version carefully)
- **Watch mode from repo root**: Use `npm run dev --prefix ./wp-content/plugins/tvs-virtual-sports`
- **WordPress not loading JS**: Check `public/js/tvs-app.js` exists, hard-refresh browser
- **Activity privacy**: Default visibility is `private` (only author can view)
- **Routes cache**: Clear with `?tvsforcefetch=1` or update any route (triggers cache-buster)
- **Strava upload fails**: Verify credentials, check activity has `route_id` and GPX exists
- **Invite table missing**: Run `make wp cmd="eval-file /scripts/seed.php"` or visit WP Admin (triggers `ensure_invites_table()`)

## Design Tokens

Theme tokens live in `wp-content/themes/tvs-theme/assets/css/tvs-tokens.css` (global CSS variables). Plugin styles reference these via `.tvs-app` scope.
