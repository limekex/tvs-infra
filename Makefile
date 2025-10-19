up:
	docker compose up -d

install:
	docker compose exec wordpress wp core install \
	--url=$(WP_SITE_URL) \
	--title="$(WP_TITLE)" \
	--admin_user=$(WP_ADMIN_USER) \
	--admin_password=$(WP_ADMIN_PASS) \
	--admin_email=$(WP_ADMIN_EMAIL)

activate:
	docker compose exec wordpress wp plugin activate tvs-virtual-sports || true
	docker compose exec wordpress wp theme activate tvs-theme || true

seed:
	docker compose exec wordpress wp eval-file /scripts/seed.php

wp:
	docker compose exec wordpress wp $(cmd)

install:
	docker compose run --rm cli core install \
	--url=$(WP_SITE_URL) \
	--title="$(WP_TITLE)" \
	--admin_user=$(WP_ADMIN_USER) \
	--admin_password=$(WP_ADMIN_PASS) \
	--admin_email=$(WP_ADMIN_EMAIL)

activate:
	docker compose run --rm cli plugin activate tvs-virtual-sports || true
	docker compose run --rm cli theme activate tvs-theme || true

seed:
	docker compose run --rm cli eval-file /scripts/seed.php

tunnel:
	cloudflared tunnel --url http://localhost:8080
