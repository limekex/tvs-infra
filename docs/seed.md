# Seed data for local dev/QA

This repository includes a simple seeding script to populate a few demo routes and activities for testing.

What it does
- Creates 5–10 `tvs_route` posts with varied meta (distance_m, elevation_m, duration_s, video_provider, video_id, difficulty)
- Assigns basic taxonomies (`tvs_region`, `tvs_activity_type`)
- Creates 2–3 `tvs_activity` posts referencing some of the routes
- Idempotent: each run removes the previously seeded content (tagged with `seed_batch=tvs_seed_v1`) before re-creating
- Creates a helper page “TVS Dev Routes” rendering the first route via `[tvs_route id="X"]`

How to run

```sh
make seed
```

Under the hood, this calls:

```sh
docker compose run --rm cli wp eval-file /scripts/seed.php
```

Notes
- Requires the plugin `tvs-virtual-sports` to be active.
- You can safely run `make seed` multiple times; it cleans up seeded items before re-creating them.
- To verify, hit the API:

```sh
docker compose run --rm cli wp option get siteurl
# then open http://localhost:8080/wp-json/tvs/v1/routes?per_page=12
```
