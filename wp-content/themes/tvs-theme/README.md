Norway Virtual Sport — Theme README

This theme is a block-first (FSE) scaffold for the Norway Virtual Sport project. It contains placeholder templates and starter blocks for development.

Build

```bash
cd themes/norway-virtual-sport
npm install
npm run build
```

Where to start
- `block-templates/` contains the HTML template placeholders for front-page, archive and single templates.
- `blocks/` contains minimal block manifests and starter JS.
- `inc/` has enqueue and REST mock endpoints for local development.
- `docs/ui/` contains design system documentation and visual examples.
- `assets/css/tvs-tokens.css` contains the global design token system.

Notes
- Blocks are currently placeholders using `save() { return null }` for server rendering.
- Move sensitive logic (Strava OAuth) into the plugin or a secure server-side flow.
# tvs-theme
