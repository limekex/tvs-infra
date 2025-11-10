# Feature: Virtual Training Experience (Route Map Animation)

**Branch:** `feature/route-map-animation`  
**Status:** 🚧 In Development  
**Inspirasjonskilder:** [CodePen - Scroll Map](https://codepen.io/creativeocean/pen/zYrPrgd)

## Konsept

En **virtuell treningsopplevelse** for ruter uten video. Brukeren oppgir sin hastighet (km/t eller min/km), og kartet animeres i sanntid som om de løper/sykler ruten i fugleperspektiv. Perfekt for tredemølle eller ergometersykkel! 🏃‍♂️🚴‍♂️

### Use Case

**Scenario:** Du skal trene på tredemølle/ergometersykkel og vil "oppleve" en ekte rute samtidig.

1. Åpne en rute uten video (f.eks. "Afternoon Run" - 8.2 km)
2. Gå inn i fullscreen-modus
3. Oppgi din hastighet (f.eks. 10 km/t for løping, 25 km/t for sykling)
4. Trykk **Start** → Kartet animeres i fugleperspektiv i sanntid
5. Tren mens du ser terrenget, høydeprofilen og fremdriften din
6. Pause/fortsett etter behov

### Når brukes det?

- ✅ Rute **HAR** `gpx_url` og **MANGLER** `vimeo_id` → Vis virtual training mode
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

// MotionPathPlugin (FREE) - Animate marker along path
https://unpkg.com/gsap@3/dist/MotionPathPlugin.min.js

// NOTE: ScrollTrigger NOT needed - we use time-based animation instead!
```

**⚠️ Licensing Note:** DrawSVGPlugin er premium ($99/year). Vurder alternativer:
1. CSS `stroke-dasharray` + `stroke-dashoffset` animasjon (synkronisert med marker)
2. [anime.js](https://animejs.com/) (gratis, open source)
3. Skip path drawing entirely - just show full route and animate marker
4. **Recommendation:** Skip DrawSVG for MVP - just show the full route path

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
3b. No video, has GPX → Show Virtual Training Mode
   ↓
4. Fetch and parse GPX file
   ↓
5. Calculate route metrics:
   - Total distance (km)
   - Elevation profile
   - Start/end coordinates
   ↓
6. Convert GPX to:
   - SVG path for marker animation
   - GeoJSON for map route rendering
   ↓
7. User inputs speed (km/h or min/km)
   ↓
8. Calculate animation duration:
   duration = distance / speed
   ↓
9. User clicks "Start"
   ↓
10. Initialize GSAP timeline animation:
    - Animate marker along SVG path
    - Sync map camera to marker position
    - Update real-time stats (distance, elevation, time remaining)
    ↓
11. User controls:
    - Pause/Resume
    - Speed up/slow down
    - Restart
    - Fullscreen toggle
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
- [ ] Speed input field (km/h or min/km toggle)
- [ ] Start/Pause/Resume buttons
- [ ] Restart button
- [ ] Speed adjustment controls (+/- buttons or slider)
- [ ] Reuse fullscreen mode from cinematic mode
- [ ] Create progress bar synced to time
- [ ] Real-time stats overlay:
  - Current speed
  - Distance traveled vs. total
  - Current elevation
  - Time elapsed / Time remaining
  - ETA (estimated time of arrival)

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

### 2. Sync Map with Marker (Real-time Camera Following)

**Time-based Animation Approach:**
```javascript
// Calculate duration based on user speed
const routeDistanceKm = 8.2; // From GPX
const userSpeedKmh = 12; // User input
const durationSeconds = (routeDistanceKm / userSpeedKmh) * 3600;

// Create GSAP timeline
const timeline = gsap.timeline({
  paused: true, // Wait for user to click "Start"
  onUpdate: function() {
    const progress = timeline.progress(); // 0 to 1
    
    // Get current marker position
    const markerCoords = getMarkerLatLng(progress);
    
    // Update map camera (smooth following)
    map.easeTo({
      center: markerCoords,
      zoom: 14,
      pitch: 60, // Bird's eye view angle
      duration: 100 // Smooth transition
    });
    
    // Update stats
    updateStats(progress, durationSeconds);
  }
});

// Animate marker along path
timeline.to('#marker', {
  motionPath: {
    path: '#routePath',
    align: '#routePath',
    alignOrigin: [0.5, 0.5]
  },
  duration: durationSeconds,
  ease: 'none' // Constant speed
});

// User clicks "Start"
document.getElementById('startBtn').addEventListener('click', () => {
  timeline.play();
});
```

**Key Differences from CodePen:**
- ❌ No scroll binding (ScrollTrigger)
- ✅ Time-based animation (duration calculated from speed)
- ✅ User controls (play/pause/speed adjustment)
- ✅ Real map camera following (not just SVG container movement)

### 3. Path Rendering Strategy

**Decision: Show full route immediately, animate marker only**

Since DrawSVG is premium ($99/year) and we want the user to see the full route from start:

```javascript
// Simple approach: Display full route path from start
// Only animate the marker position

// Route path (always visible)
<path id="routePath" 
      class="route-path" 
      stroke="#3b82f6" 
      stroke-width="4" 
      fill="none" 
      d="M..." />

// Animated marker
<circle id="marker" 
        r="8" 
        fill="#ef4444" 
        filter="drop-shadow(0 2px 8px rgba(239,68,68,0.6))" />

// Optional: "Completed" path overlay
<path id="completedPath" 
      stroke="#22c55e" 
      stroke-width="6" 
      fill="none" 
      d="M..." 
      stroke-dasharray="1000" 
      stroke-dashoffset="1000" />
```

**If we want "completed path" effect:**
```javascript
// Sync dashoffset with marker progress
timeline.to('#completedPath', {
  strokeDashoffset: 0,
  duration: durationSeconds,
  ease: 'none'
}, 0); // Start at same time as marker
```

**Recommendation for MVP:** Skip the drawing effect - just show full route and animate marker. Add "completed path" overlay as enhancement later.

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
   - ✅ **DECISION:** Skip DrawSVG for MVP - show full route, animate marker only
   - 💡 Enhancement: Add "completed path" overlay later using CSS stroke-dashoffset

3. **Map Provider:**
   - ✅ Mapbox (already have token, used in weather widget)
   - ❌ Leaflet + OpenStreetMap (free, but different API)
   - ❌ Google Maps (expensive)

4. **Speed Input:**
   - Toggle between km/h and min/km?
   - Presets for different activities (walking: 5 km/h, running: 10-12 km/h, cycling: 20-30 km/h)?
   - Allow speed adjustment during animation?

5. **Map Camera Behavior:**
   - Fixed zoom level or dynamic based on terrain?
   - Fixed pitch (60°) or adjustable?
   - Smooth easing or instant updates?

6. **Mobile Experience:**
   - Touch controls for speed adjustment
   - Simplified UI for smaller screens
   - Landscape mode required?

7. **Pause Behavior:**
   - Remember position when paused
   - Allow "rewind" or only forward?
   - Show speed = 0 km/h when paused?

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
