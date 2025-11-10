# Feature: Route Map Animation

**Branch:** `feature/route-map-animation`  
**Status:** 🚧 In Development  
**Inspirasjonskilder:** [CodePen - Scroll Map](https://codepen.io/creativeocean/pen/zYrPrgd)

## Konsept

Erstatte Vimeo-videoen med en scroll-basert animert kartvisning for ruter uten video. Bruker kan scrolle gjennom ruten med same fullscreen-opplevelse som cinematic mode.

### Når brukes det?

- ✅ Rute **HAR** `gpx_url` og **MANGLER** `vimeo_id` → Vis map animation
- ❌ Rute **HAR** `vimeo_id` → Vis Vimeo-player (existing behavior)
- ❌ Rute mangler både GPX og video → Show fallback

## Teknisk Arkitektur

### Komponenter

```
Route Player Block
├── Conditional Rendering
│   ├── Has vimeo_id? → Vimeo Player (current)
│   └── Has gpx_url, no video? → Map Animation (NEW)
│
├── Map Animation Container
│   ├── Mapbox/Leaflet map instance
│   ├── SVG overlay with route path
│   ├── Animated marker following scroll
│   └── Background map syncing to marker position
│
└── Shared Controls
    ├── Fullscreen toggle
    ├── Play/pause (auto-scroll)
    ├── Progress bar
    └── Stats overlay (distance, elevation, etc.)
```

### Libraries/Dependencies

#### GSAP (GreenSock Animation Platform)
```javascript
// Core GSAP
https://unpkg.com/gsap@3/dist/gsap.min.js

// ScrollTrigger (FREE)
https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js

// MotionPathPlugin (FREE)
https://unpkg.com/gsap@3/dist/MotionPathPlugin.min.js

// DrawSVG (PREMIUM - 99$/year)
// Alternative: CSS stroke-dashoffset animation eller anime.js
```

**⚠️ Licensing Note:** DrawSVGPlugin er premium. Vurder alternativer:
1. CSS `stroke-dasharray` + `stroke-dashoffset` animasjon
2. [anime.js](https://animejs.com/) (gratis, open source)
3. Custom implementation

#### Mapping

Bruk eksisterende Mapbox token:
```javascript
// Allerede i bruk i Weather Widget
mapboxgl.accessToken = 'pk.eyJ1IjoibGltZWtleCIsImEiOiJjbTN4emN4NDUwY2o2MmtzOXRrb2w5YmNxIn0.rJ0YZqV7mDmx5I3rpglXvg'
```

### Data Flow

```
1. User loads route page
   ↓
2. Check post meta: vimeo_id? gpx_url?
   ↓
3a. Has video → Render Vimeo player
   |
3b. No video, has GPX → Fetch GPX file
   ↓
4. Parse GPX → Extract lat/lng/elevation points
   ↓
5. Convert coordinates to:
   - SVG path for animation
   - GeoJSON for map rendering
   ↓
6. Initialize GSAP ScrollTrigger
   ↓
7. Bind scroll position to:
   - Marker position on path
   - Map center/zoom
   - Progress stats
```

## Implementation Plan

### Phase 1: Core Infrastructure ✅
- [x] Create feature branch
- [ ] Add GSAP dependencies to WordPress
- [ ] Create utility functions for GPX → SVG conversion

### Phase 2: Block Development
- [ ] Decide: Modify existing `route-player` block or create new block?
- [ ] Add conditional rendering logic (video vs. map)
- [ ] Create map container component
- [ ] Implement scroll-based animation

### Phase 3: Controls & UX
- [ ] Reuse fullscreen mode from cinematic mode
- [ ] Add play/pause button (auto-scroll)
- [ ] Create progress bar synced to scroll
- [ ] Add stats overlay (current km, elevation, etc.)

### Phase 4: Performance & Polish
- [ ] GPX point simplification (reduce overhead)
- [ ] Lazy load map tiles
- [ ] Mobile optimization
- [ ] Testing across browsers

## Technical Challenges

### 1. GPX to SVG Path Conversion

**Problem:** GPX has lat/lng coordinates, SVG needs pixel coordinates.

**Solution:**
```javascript
// Pseudo-code
function gpxToSvgPath(gpxPoints, width, height) {
  // 1. Find bounding box
  const bounds = getBounds(gpxPoints);
  
  // 2. Project lat/lng to pixels
  const projected = gpxPoints.map(point => ({
    x: project(point.lng, bounds.west, bounds.east, 0, width),
    y: project(point.lat, bounds.north, bounds.south, 0, height)
  }));
  
  // 3. Build SVG path string
  return `M${projected[0].x},${projected[0].y}` + 
         projected.slice(1).map(p => `L${p.x},${p.y}`).join('');
}
```

**Libraries to consider:**
- [turf.js](https://turfjs.org/) - Geospatial analysis
- [simplify-js](https://mourner.github.io/simplify-js/) - Point reduction

### 2. Sync Map with Marker

**CodePen Approach:**
```javascript
// Move container opposite to marker position
const xTo = gsap.quickTo('#container', 'x', {duration: 0.7});
const yTo = gsap.quickTo('#container', 'y', {duration: 0.7});

gsap.ticker.add(() => {
  xTo(-gsap.getProperty('#marker', 'x'));
  yTo(-gsap.getProperty('#marker', 'y'));
});
```

**Map Adaptation:**
```javascript
// Update map center to marker's lat/lng
gsap.ticker.add(() => {
  const markerCoords = getMarkerLatLng(); // Based on path progress
  map.setCenter(markerCoords);
  // Optionally adjust zoom based on terrain
});
```

### 3. DrawSVG Alternative

Since DrawSVG is premium ($99/year), implement CSS-based alternative:

```css
.route-path {
  stroke-dasharray: 1000; /* Total path length */
  stroke-dashoffset: 1000; /* Start hidden */
  animation: draw 2s ease-out forwards;
}

@keyframes draw {
  to {
    stroke-dashoffset: 0; /* Reveal path */
  }
}
```

Or use GSAP's free features:
```javascript
// Animate using clip-path or mask
gsap.to('.route-path', {
  clipPath: 'inset(0 0 0 100%)',
  scrollTrigger: { /* ... */ }
});
```

## File Structure (Proposed)

```
wp-content/plugins/tvs-virtual-sports/
├── src/blocks/
│   ├── route-player/            # Existing block
│   │   ├── edit.js
│   │   ├── view.js              # Add map animation logic here
│   │   └── style.scss
│   │
│   └── route-map-animation/     # OR: New dedicated block
│       ├── edit.js
│       ├── view.js
│       ├── map-animator.js      # Core animation logic
│       ├── gpx-parser.js        # GPX → SVG/GeoJSON conversion
│       └── style.scss
│
├── includes/
│   └── class-tvs-assets.php     # Enqueue GSAP scripts
│
└── assets/
    ├── js/
    │   ├── gsap.min.js          # GSAP core
    │   ├── ScrollTrigger.min.js # Scroll binding
    │   └── MotionPathPlugin.min.js # Path animation
    │
    └── css/
        └── route-animation.css   # Map container, controls
```

## Open Questions / Decisions Needed

1. **Block Strategy:**
   - ✅ Modify existing `route-player` block (simpler, reuses controls)
   - ❌ Create separate `route-map-animation` block (cleaner separation)

2. **DrawSVG Licensing:**
   - Pay $99/year for official plugin?
   - Use CSS-based alternative?
   - Use anime.js instead?

3. **Map Provider:**
   - ✅ Mapbox (already have token, used in weather widget)
   - ❌ Leaflet + OpenStreetMap (free, but different API)
   - ❌ Google Maps (expensive)

4. **Scroll Behavior:**
   - Use CodePen's approach (scroll distance = animation progress)?
   - Add "auto-play" button that smoothly scrolls?
   - Both?

5. **Mobile Experience:**
   - Scroll can be tricky on mobile
   - Alternative: Swipe gestures?
   - Or: Auto-play mode only on mobile?

## Resources & References

- [CodePen Original](https://codepen.io/creativeocean/pen/zYrPrgd)
- [GSAP ScrollTrigger Docs](https://greensock.com/docs/v3/Plugins/ScrollTrigger)
- [GSAP MotionPath Docs](https://greensock.com/docs/v3/Plugins/MotionPathPlugin)
- [Mapbox GL JS API](https://docs.mapbox.com/mapbox-gl-js/api/)
- [Turf.js - Geospatial Tools](https://turfjs.org/)
- [Simplify.js - Path Simplification](https://mourner.github.io/simplify-js/)

## Next Steps

1. ✅ Create branch
2. ⏳ Download and study gist files
3. ⏳ Prototype GPX → SVG conversion
4. ⏳ Test GSAP integration in WordPress context
5. ⏳ Build minimal proof-of-concept

---

**Sist oppdatert:** 10. november 2025
