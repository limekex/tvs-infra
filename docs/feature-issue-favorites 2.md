# Feature: Favourites Blocks (My Favourites + People Favourites)

## Summary
Build two server-rendered Gutenberg blocks backed by a simple favourites system:
- My Favourites: shows only the logged-in user’s favourited routes
- People Favourites: shows routes most-favourited by users (all-time initially)

MVP stores favourites per-user; extend with a global count for efficient top lists. Follow TVS tokens and visual language (matching Routes Grid/Route Card).

## Motivation & Goals
- Give users a quick way to save routes and revisit them later
- Showcase community popularity (top favourites) for discovery
- Keep UI/UX consistent with existing TVS routes components

## Scope (MVP)
- Persist favourites per user (already implemented): `user_meta: tvs_favorites_routes = [post_id, ...]`
- Expose REST endpoints:
  - GET /tvs/v1/favorites → { ids: number[] }
  - POST /tvs/v1/favorites/{id} → toggle for current user; returns { favorited, ids, totalCount? }
  - (Optional, for People Favourites) GET /tvs/v1/favorites/top?per_page=12&period=all → { items: [{ id, title, image, meta, favCount }, ...] }
- Blocks (server-rendered):
  - tvs/my-favourites
  - tvs/people-favourites (all-time top)

## Out of scope (for now)
- Period-based rankings (7d/30d) unless we add counting infrastructure
- Public profile favourites or social graph

## Data Model
- Per-user favourites: `user_meta('tvs_favorites_routes')` = array of route IDs
- Global count (for top lists): store `post_meta('tvs_fav_count')` (integer, default 0), updated on toggle:
  - When a user favourites a route (add), increment
  - When a user unfavourites (remove), decrement (min 0)
- Future-proofing for period-based rankings:
  - Option A (simple, eventual consistency): schedule a daily cron to recompute 7/30 day counts into `post_meta('tvs_fav_count_7d')` and `post_meta('tvs_fav_count_30d')` from an event log
  - Option B (accurate, scalable): separate custom table `tvs_favorites` with (user_id, post_id, created_at, active), then aggregate with date filters

## API changes (plugin)
- Extend POST /tvs/v1/favorites/{id} response with `totalCount`
- Add GET /tvs/v1/favorites/top (all-time):
  - Query `tvs_route` ordered by `meta_key = 'tvs_fav_count'` (DESC), with `posts_per_page`
  - Return normalized payload similar to `get_routes()` with an added `favCount`

## Block: tvs/my-favourites
- Attributes:
  - layout: "grid" | "list" (default grid)
  - columns: number (default 3)
  - perPage: number (default 12)
  - showPagination: boolean (default true)
  - showMeta: boolean (default true)
  - showBadges: boolean (default true)
  - showDifficulty: boolean (default true)
  - emptyStateText: string (default "No favourites yet.")
- Behaviour:
  - If user not logged in → show call-to-action to log in
  - Render routes from `user_meta` IDs (paged via SSR if showPagination)
  - Reuse Routes Grid/Route Card visuals and tokens; respect list/grid settings
- Acceptance Criteria:
  - Shows only current user’s favourites
  - Pagination works when enabled; when disabled, limit by perPage (no links)
  - Empty state with configurable text

## Block: tvs/people-favourites (all-time)
- Attributes:
  - layout: "grid" | "list"
  - columns, perPage, showPagination (same as above)
  - showMeta/showBadges/showDifficulty (same as above)
  - showCounts: boolean (default true) → displays favCount badge
- Behaviour:
  - Queries `tvs_route` ordered by `tvs_fav_count` (DESC), paged
  - If showCounts = true, display a small count pill on card
- Acceptance Criteria:
  - Returns popular routes in descending order by global fav count
  - Stable ordering when counts tie (fallback to date DESC)

## UX & Visuals
- Bookmark button: already implemented with tokens, bottom-right placement (grid/list)
- Cards & list row containers reuse tokens: radius, elevation, borders, colours
- People Favourites can show a small "★ {count}" pill (tokenised) if enabled

## Security & Permissions
- Toggle/list favourites requires authenticated user (or dev nonce fallback as configured)
- People Favourites (top) is public (read-only)

## Performance
- Toggle endpoint updates small user_meta array and increments a single post_meta integer (O(1))
- People Favourites query uses `meta_key` ordering (index on meta key may be limited); acceptable for MVP scale; consider precomputing lists if volume grows

## Implementation Plan
1) Backend
   - Extend POST /favorites/{id} to also update `tvs_fav_count` (±1, clamped >= 0) and return `totalCount`
   - Add GET /favorites/top returning normalized route items with `favCount`
2) My Favourites Block
   - SSR block querying IDs from `user_meta`; apply existing render utilities (list/grid)
   - Attributes & InspectorControls; translations; tokens
3) People Favourites Block (all-time)
   - SSR block querying by `tvs_fav_count` DESC; attributes as above
   - Optional count pill visual
4) QA & Docs
   - Manual tests for toggle, persistence, SSR, pagination
   - README/Docs update

## Open Questions
- Do we need period-based rankings in v1? If yes, pick Option A (cron + meta) or Option B (custom table)
- Should anonymous users be able to queue favourites (e.g., prompt to log in)?

## Tasks
- [ ] Extend favourites toggle to update `tvs_fav_count` and return `totalCount`
- [ ] Add endpoint: GET /tvs/v1/favorites/top
- [ ] Block: tvs/my-favourites (SSR)
- [ ] Block: tvs/people-favourites (SSR)
- [ ] Tokens/UI polish for count pill
- [ ] Docs: usage, attributes, examples
- [ ] QA checklist (logged-in/out, pagination, empty states)
