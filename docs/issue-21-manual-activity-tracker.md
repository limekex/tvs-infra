# Issue #21: Manual Activity Tracker (Treadmill/Indoor)

## Summary

Implement complete manual activity tracking for indoor workouts (treadmill, spin bike, etc.) with live dashboard and real-time metric adjustments. Users can start, track, and save activities without GPS/route data, then optionally upload to Strava as manual activities.

## Scope

### 1. Gutenberg Block: "Manual Activity Tracker"
- **Activity Type Selector**: Run, Ride, Walk, etc.
- **Live Dashboard** during active session:
  - Real-time timer (elapsed time)
  - Distance (manually adjusted or auto-calculated from pace)
  - Average pace/speed
  - Activity-specific controls
- **Control Panel** based on type:
  - **Run/Walk**: Pace (min/km), Speed (km/h), Incline (%)
  - **Ride**: Speed (km/h), Cadence (RPM), Power (W) - optional
- **Action Buttons**: Pause, Resume, Stop & Save

### 2. Backend
- **REST Endpoints**: 
  - `POST /tvs/v1/activities/manual/start` - Start new manual activity (returns session ID)
  - `PATCH /tvs/v1/activities/manual/{id}` - Update metrics (real-time or on pause)
  - `POST /tvs/v1/activities/manual/{id}/finish` - Complete and save as `tvs_activity`
- **Activity Meta**:
  - `is_manual=true`
  - `has_route=false`
  - `manual_type` (Run/Ride/Walk)
  - `manual_metrics` (JSON: pace history, adjustments)
  - `trainer=1` (for Strava compliance)

### 3. Strava Integration (Secondary)
- **REST**: `POST /tvs/v1/activities/{id}/strava/manual`
- **Strava API**: Use `Create Activity` endpoint with `trainer=1`
- Body: `distance`, `elapsed_time`, `name`, `sport_type`, `trainer=1`
- Show "Upload to Strava (treadmill)" button on completed manual activities

## Acceptance Criteria (AC)

1. ✅ User can start manual activity from block/dashboard
2. ✅ Live dashboard shows elapsed time, distance, avg pace during activity
3. ✅ User can adjust pace/speed in real-time (affects distance calculation)
4. ✅ Activity saves as `tvs_activity` with `is_manual=true`
5. ✅ Activity appears in "My Activities" block alongside route-based activities
6. ✅ User can upload completed manual activity to Strava (without GPS track)
7. ✅ Strava response shows `remote_id` and `synced=true`
8. ✅ Error handling: token expired, rate limit, network errors
9. ✅ Compliance with Strava Guidelines (https://developers.strava.com/guidelines/)

## Implementation Notes

### Frontend
- **Block**: `src/blocks/manual-activity-tracker/`
  - `edit.js` - Block editor interface
  - `view.js` - Frontend React component (dashboard)
  - `ManualActivityDashboard.js` - Live tracking UI
  - `ActivityTypeSelector.js` - Initial prompt
  - `MetricsControl.js` - Pace/speed adjusters

### Backend
- **REST**: `class-tvs-rest.php`
  - `manual_activity_start()` - Create temporary session
  - `manual_activity_update()` - Update metrics
  - `manual_activity_finish()` - Save as tvs_activity post
  - `strava_upload_manual()` - Upload to Strava API
- **Strava**: `class-tvs-strava.php`
  - `create_manual_activity($user_id, $payload)` - Call Strava `/api/v3/activities`
  - Payload includes `trainer=1` for indoor activities

### State Management
- **Session storage**: `localStorage` for active session (recovery on refresh)
- **Interval updates**: Auto-save metrics every 30s during active session
- **Completion**: POST to finish endpoint clears session, creates post

## Testplan

### Manual
1. Start manual run session → adjust pace → verify distance updates
2. Pause/resume → check timer stops/continues
3. Complete → confirm appears in "My Activities"
4. Upload to Strava → verify activity shows without GPS track

### Automated
- PHPUnit: Mock Strava API responses (success, rate limit, token expired)
- Jest: Dashboard component rendering, timer logic, metric calculations

## Release/Docs

- **Changelog**: Added manual activity tracking for indoor workouts (treadmill, spin bike)
- **User docs**: How to start manual activity, adjust metrics, upload to Strava

## Meta

**Labels**: `type:feature`, `area:backend`, `area:frontend`, `prio:P2`  
**Milestone**: `v1.2.0`  
**Estimate**: ~12-16 hours (Block: 4h, Dashboard: 4h, Backend: 3h, Strava: 2h, Testing: 3h)

## Technical Architecture

### Data Flow

```
User clicks "Start Activity" 
  → POST /tvs/v1/activities/manual/start
  → Returns session_id, stores in localStorage
  
User adjusts pace during workout
  → Updates local state
  → Auto-saves via PATCH every 30s
  
User clicks "Finish"
  → POST /tvs/v1/activities/manual/{id}/finish
  → Creates tvs_activity post with is_manual=true
  → Clears localStorage session
  → Redirects to activity page
  
User clicks "Upload to Strava"
  → POST /tvs/v1/activities/{id}/strava/manual
  → Strava API: POST /api/v3/activities with trainer=1
  → Updates activity meta: synced_strava=true, strava_remote_id
```

### Database Schema Changes

**New post_meta keys for `tvs_activity`:**
- `is_manual` (bool) - Distinguishes manual from route-based activities
- `has_route` (bool) - False for manual activities
- `manual_type` (string) - Run, Ride, Walk, etc.
- `manual_metrics` (JSON) - Historical pace/speed adjustments
- `manual_incline` (float) - Treadmill incline percentage
- `manual_cadence` (int) - RPM for cycling
- `manual_power` (int) - Watts for cycling (optional)

## UI/UX Considerations

### Dashboard Layout
```
┌─────────────────────────────────┐
│  Manual Activity - Running      │
├─────────────────────────────────┤
│  ⏱️  Time: 00:15:32             │
│  📏 Distance: 2.58 km           │
│  ⚡ Avg Pace: 6:01 /km          │
├─────────────────────────────────┤
│  Current Speed: [5.2] [▲][▼]   │
│  Incline: [2.5%] [▲][▼]        │
├─────────────────────────────────┤
│  [⏸️ Pause]  [⏹️ Stop & Save]   │
└─────────────────────────────────┘
```

### Activity Type Selection
- **Preset buttons**: Run 🏃, Ride 🚴, Walk 🚶
- **Custom type**: Dropdown with all Strava activity types
- **Remember last used**: localStorage preference

### Strava Upload Button
- Only visible on completed manual activities
- Disabled if already synced
- Shows sync status and Strava activity link after upload

## Open Questions

1. Should we support manual entry of historical activities (past date/time)?
2. Heart rate monitor integration via Web Bluetooth API?
3. Auto-pause detection based on inactivity?
4. Export manual activities as GPX with synthetic timestamps?
5. Support for interval training with pace/speed zones?

## Related Issues

- #13 - Tracking: v1.2 — Blocks + Strava routes + Treadmill + Player UX (parent)
- #3 - Strava OAuth integration (dependency)
- #4 - Activity upload to Strava (related)
