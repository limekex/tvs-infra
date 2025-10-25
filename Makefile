# -----------------------------
# TVS-INFRA MAKEFILE
# Forenkler arbeid med WordPress dev-miljøet
# -----------------------------

# Miljøvariabler (kan settes i .env)
WP_SITE_URL ?= http://localhost:8080
WP_TITLE ?= Norway Virtual Sports
WP_ADMIN_USER ?= admin
WP_ADMIN_PASS ?= admin
WP_ADMIN_EMAIL ?= admin@example.com

# -----------------------------
# Basiskommandoer
# -----------------------------

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose up -d --force-recreate

logs:
	docker compose logs -f wordpress

# -----------------------------
# WordPress setup
# -----------------------------

install:
	docker compose run --rm cli wp core install \
		--url=$(WP_SITE_URL) \
		--title="$(WP_TITLE)" \
		--admin_user=$(WP_ADMIN_USER) \
		--admin_password=$(WP_ADMIN_PASS) \
		--admin_email=$(WP_ADMIN_EMAIL) || true

activate:
	docker compose run --rm cli wp plugin activate tvs-virtual-sports || true
	docker compose run --rm cli wp theme activate tvs-theme || true

seed:
	docker compose run --rm cli wp eval-file /scripts/seed.php || true

# -----------------------------
# WP utility commands
# -----------------------------

wp:
	docker compose run --rm cli wp $(cmd)

shell:
	docker compose exec wordpress bash

cli-shell:
	docker compose run --rm cli bash

# -----------------------------
# Extras
# -----------------------------

tunnel:
	cloudflared tunnel --url http://localhost:8080

runnerrun:
	cloudflared tunnel run 

status:
	docker compose ps
