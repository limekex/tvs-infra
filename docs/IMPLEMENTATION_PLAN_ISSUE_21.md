# Implementation Plan: Issue #21 - Manual Activity Tracker

**Branch**: `feature/issue-21-manual-activity-tracker`  
**Estimate**: 12-16 hours  
**Priority**: P2

---

## Phase 1: Backend Foundation (3-4 hours)

### 1.1 REST Endpoints in `class-tvs-rest.php` (~2h)

**Tasks:**
- [ ] Add route: `POST /tvs/v1/activities/manual/start`
  - Create temporary session (transient or custom table?)
  - Return session_id + initial state
  - Auth: Require logged-in user
  
- [ ] Add route: `PATCH /tvs/v1/activities/manual/{id}`
  - Update session metrics (distance, pace, incline, etc.)
  - Validate session ownership
  - Store in transient with 1-hour expiry
  
- [ ] Add route: `POST /tvs/v1/activities/manual/{id}/finish`
  - Convert session to `tvs_activity` post
  - Set meta: `is_manual=true`, `has_route=false`, `manual_type`, etc.
  - Clear session transient
  - Return activity post ID + permalink

**Files to modify:**
- `wp-content/plugins/tvs-virtual-sports/includes/class-tvs-rest.php`

**Testing:**
```bash
# Start session
curl -X POST http://localhost:8080/wp-json/tvs/v1/activities/manual/start \
  -H "X-WP-Nonce: xxx" \
  -d '{"type":"Run"}'

# Update metrics
curl -X PATCH http://localhost:8080/wp-json/tvs/v1/activities/manual/123 \
  -H "X-WP-Nonce: xxx" \
  -d '{"distance":2.5,"elapsed_time":900,"pace":6.0}'

# Finish
curl -X POST http://localhost:8080/wp-json/tvs/v1/activities/manual/123/finish \
  -H "X-WP-Nonce: xxx"
```

---

### 1.2 Strava Manual Upload in `class-tvs-strava.php` (~1h)

**Tasks:**
- [ ] Add method: `create_manual_activity($user_id, $payload)`
  - POST to Strava `/api/v3/activities`
  - Include `trainer=1` for indoor activities
  - Map TVS activity types to Strava types
  - Handle errors (rate limit, token expired)
  
- [ ] Add REST route: `POST /tvs/v1/activities/{id}/strava/manual`
  - Validate activity is manual (`is_manual=true`)
  - Check not already synced
  - Call `create_manual_activity()`
  - Update meta: `_tvs_synced_strava`, `_tvs_strava_remote_id`

**Files to modify:**
- `wp-content/plugins/tvs-virtual-sports/includes/class-tvs-strava.php`
- `wp-content/plugins/tvs-virtual-sports/includes/class-tvs-rest.php`

**Testing:**
```bash
# Upload manual activity to Strava
curl -X POST http://localhost:8080/wp-json/tvs/v1/activities/456/strava/manual \
  -H "X-WP-Nonce: xxx"
```

---

### 1.3 Activity Meta Schema (~30min)

**Tasks:**
- [ ] Document new post_meta keys for `tvs_activity`
- [ ] Add validation in `create_activity()` REST handler
- [ ] Update "My Activities" query to include manual activities

**New meta keys:**
```php
'is_manual'        => bool   // true for manual activities
'has_route'        => bool   // false for manual
'manual_type'      => string // 'Run', 'Ride', 'Walk'
'manual_metrics'   => json   // { pace_history: [], adjustments: [] }
'manual_incline'   => float  // treadmill incline %
'manual_cadence'   => int    // cycling RPM
'manual_power'     => int    // cycling watts (optional)
```

---

## Phase 2: Gutenberg Block Scaffolding (2-3 hours)

### 2.1 Block Registration (~1h)

**Tasks:**
- [ ] Create block directory: `src/blocks/manual-activity-tracker/`
- [ ] Register block in `class-tvs-plugin.php`
- [ ] Create `block.json` with attributes
- [ ] Create `edit.js` (editor interface)
- [ ] Create `view.js` (frontend mount point)

**Files to create:**
```
src/blocks/manual-activity-tracker/
├── block.json
├── edit.js
├── view.js
├── style.scss
└── editor.scss
```

**Block attributes:**
```json
{
  "showTypeSelector": { "type": "boolean", "default": true },
  "allowedTypes": { "type": "array", "default": ["Run", "Ride", "Walk"] },
  "autoStart": { "type": "boolean", "default": false }
}
```

---

### 2.2 Activity Type Selector Component (~1h)

**Tasks:**
- [ ] Create `ActivityTypeSelector.js`
- [ ] Preset buttons: Run, Ride, Walk
- [ ] Custom dropdown for other types
- [ ] Store preference in localStorage
- [ ] Emit `onTypeSelected` event

**Component structure:**
```jsx
<ActivityTypeSelector 
  onSelect={(type) => startActivity(type)}
  allowedTypes={['Run', 'Ride', 'Walk']}
  defaultType={localStorage.getItem('tvs_last_activity_type')}
/>
```

---

### 2.3 Build Configuration (~30min)

**Tasks:**
- [ ] Update `esbuild.config.js` to include new block
- [ ] Test build: `npm run build`
- [ ] Test watch mode: `npm run dev`

---

## Phase 3: Live Dashboard Component (4-5 hours)

### 3.1 Dashboard Container & State Management (~2h)

**Tasks:**
- [ ] Create `ManualActivityDashboard.js`
- [ ] State: `{ sessionId, type, startTime, elapsedTime, distance, speed, pace, incline, isPaused }`
- [ ] Timer logic: `setInterval` for elapsed time updates
- [ ] Distance calculation: `distance += (speed / 3600) * updateInterval`
- [ ] localStorage persistence for recovery on refresh

**State structure:**
```js
const [session, setSession] = useState({
  sessionId: null,
  type: 'Run',
  startTime: null,
  elapsedTime: 0,
  distance: 0,
  speed: 10.0,      // km/h
  pace: 6.0,        // min/km
  incline: 0,       // %
  cadence: 0,       // RPM (cycling)
  power: 0,         // W (cycling)
  isPaused: false,
  lastUpdate: Date.now()
});
```

---

### 3.2 Metrics Display (~1h)

**Tasks:**
- [ ] Timer display: `formatTime(elapsedTime)` → "00:15:32"
- [ ] Distance display: `distance.toFixed(2) + ' km'`
- [ ] Pace calculation: `pace = 60 / speed` (min/km)
- [ ] Responsive layout for mobile

**Display components:**
```jsx
<div className="tvs-manual-stats">
  <Stat icon="⏱️" label="Time" value={formatTime(elapsedTime)} />
  <Stat icon="📏" label="Distance" value={`${distance.toFixed(2)} km`} />
  <Stat icon="⚡" label="Pace" value={`${pace.toFixed(1)} /km`} />
</div>
```

---

### 3.3 Metrics Control Panel (~1.5h)

**Tasks:**
- [ ] Create `MetricsControl.js`
- [ ] Speed/Pace adjuster with increment buttons
- [ ] Incline adjuster (Run/Walk only)
- [ ] Cadence/Power adjusters (Ride only)
- [ ] Conditional rendering based on activity type
- [ ] Debounce updates (avoid spam to REST API)

**Control example:**
```jsx
<MetricsControl 
  type="Run"
  speed={speed}
  incline={incline}
  onSpeedChange={(newSpeed) => updateMetrics({ speed: newSpeed })}
  onInclineChange={(newIncline) => updateMetrics({ incline: newIncline })}
/>
```

---

### 3.4 Action Buttons & Session Management (~1h)

**Tasks:**
- [ ] Start button → `POST /manual/start`
- [ ] Pause button → stop timer, set `isPaused=true`
- [ ] Resume button → continue timer
- [ ] Stop & Save button → `POST /manual/{id}/finish`
- [ ] Confirmation modal before stopping
- [ ] Loading states & error handling

**Button logic:**
```jsx
const handleStart = async () => {
  const res = await fetch('/wp-json/tvs/v1/activities/manual/start', {
    method: 'POST',
    headers: { 'X-WP-Nonce': TVS_SETTINGS.nonce },
    body: JSON.stringify({ type: activityType })
  });
  const data = await res.json();
  setSession({ ...session, sessionId: data.session_id, startTime: Date.now() });
  localStorage.setItem('tvs_active_session', JSON.stringify(data));
};
```

---

## Phase 4: Auto-Save & Persistence (1-2 hours)

### 4.1 Auto-Save Logic (~1h)

**Tasks:**
- [ ] `useEffect` hook: auto-save every 30 seconds
- [ ] `PATCH /manual/{id}` with current metrics
- [ ] Skip if paused or no changes
- [ ] Error handling: retry on network failure

**Auto-save implementation:**
```js
useEffect(() => {
  if (!session.sessionId || session.isPaused) return;
  
  const interval = setInterval(async () => {
    await fetch(`/wp-json/tvs/v1/activities/manual/${session.sessionId}`, {
      method: 'PATCH',
      headers: { 'X-WP-Nonce': TVS_SETTINGS.nonce },
      body: JSON.stringify({
        elapsed_time: session.elapsedTime,
        distance: session.distance,
        speed: session.speed,
        incline: session.incline
      })
    });
  }, 30000); // 30 seconds
  
  return () => clearInterval(interval);
}, [session.sessionId, session.isPaused]);
```

---

### 4.2 Session Recovery on Refresh (~1h)

**Tasks:**
- [ ] Check `localStorage` on component mount
- [ ] Validate session still exists on backend
- [ ] Resume timer from saved elapsed time
- [ ] Show "Continue session?" prompt if found

**Recovery logic:**
```js
useEffect(() => {
  const saved = localStorage.getItem('tvs_active_session');
  if (saved) {
    const data = JSON.parse(saved);
    // Show modal: "Continue workout from 15 minutes ago?"
    setShowRecoveryModal(true);
    setPendingSession(data);
  }
}, []);
```

---

## Phase 5: Strava Upload UI (1-2 hours)

### 5.1 Upload Button on Activity Page (~1h)

**Tasks:**
- [ ] Detect manual activities: check `is_manual` meta
- [ ] Show "Upload to Strava" button below activity stats
- [ ] Disable if already synced
- [ ] Show sync status: "✓ Synced to Strava" with link

**Button implementation:**
```jsx
{isManual && !isSynced && (
  <button onClick={handleStravaUpload}>
    Upload to Strava (Treadmill)
  </button>
)}

{isSynced && (
  <p>
    ✓ Synced to Strava 
    <a href={`https://www.strava.com/activities/${remoteId}`} target="_blank">
      View on Strava →
    </a>
  </p>
)}
```

---

### 5.2 Upload Flow (~1h)

**Tasks:**
- [ ] Click → show confirmation modal
- [ ] POST to `/activities/{id}/strava/manual`
- [ ] Show loading spinner
- [ ] Handle errors: not connected, token expired, rate limit
- [ ] Success: update UI with Strava link

---

## Phase 6: Integration & Testing (2-3 hours)

### 6.1 My Activities Block Update (~1h)

**Tasks:**
- [ ] Update query to include manual activities
- [ ] Show icon/badge for manual vs. route-based
- [ ] Filter option: "Show manual only" / "Show route-based only"

---

### 6.2 Manual Testing (~1h)

**Test cases:**
1. Start Run → adjust pace → verify distance calculation
2. Pause → resume → check timer accuracy
3. Refresh page → verify session recovery
4. Stop & save → confirm in "My Activities"
5. Upload to Strava → verify on Strava.com

---

### 6.3 PHPUnit Tests (~1h)

**Tasks:**
- [ ] Test `manual_activity_start()` endpoint
- [ ] Test `manual_activity_update()` validation
- [ ] Test `manual_activity_finish()` creates post correctly
- [ ] Mock Strava API responses for `create_manual_activity()`

**Test file:**
```
tests/phpunit/test-manual-activities.php
```

---

## Phase 7: Polish & Documentation (1-2 hours)

### 7.1 Styling & Responsive Design (~1h)

**Tasks:**
- [ ] Dashboard mobile layout (stacked stats)
- [ ] Button states (hover, disabled, loading)
- [ ] Error messages styling
- [ ] Dark mode support (check theme tokens)

---

### 7.2 Documentation (~1h)

**Tasks:**
- [ ] Update `README.md` with manual activity feature
- [ ] Add to `MANUAL_TESTING.md`
- [ ] Update `CHANGELOG.md`
- [ ] Screenshots for GitHub issue

---

## Summary & Milestones

### Milestone 1: Backend Ready (Day 1)
- ✅ REST endpoints functional
- ✅ Strava upload method complete
- ✅ PHPUnit tests passing

### Milestone 2: Block & Dashboard (Day 2)
- ✅ Block registered and rendering
- ✅ Type selector working
- ✅ Live dashboard with timer + metrics

### Milestone 3: Full Feature (Day 3)
- ✅ Auto-save implemented
- ✅ Session recovery working
- ✅ Strava upload UI complete
- ✅ Manual testing passed

### Milestone 4: Ship It! (Day 3-4)
- ✅ All tests passing
- ✅ Documentation updated
- ✅ PR ready for review

---

## Next Steps

1. **Confirm branch**: `git checkout -b feature/issue-21-manual-activity-tracker`
2. **Start with Phase 1**: Backend foundation (easiest to test in isolation)
3. **Commit often**: Small, atomic commits with descriptive messages
4. **Test incrementally**: Don't wait until end to test
5. **Update issue**: Comment progress on GitHub issue #21

**Ready to start?** 🚀
