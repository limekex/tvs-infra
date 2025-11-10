# Full-Screen Cinematic Mode for TVS Video Player

## Overview
Create an immersive full-screen viewing experience for the TVS video player (tvs-app Vimeo player) with fixed controls and video overlays.

## Requirements

### 1. Fixed Control Panel
- Create a fixed control bar anchored to the bottom of the screen
- Move progress bar and time indicators into a thick top border/edge of this control panel
- Panel should contain:
  - Play/pause controls
  - Speed controls
  - Session start/stop
  - Activity save controls
  - Other relevant player controls

### 2. Full-Screen Toggle Button
- **Normal mode**: Display "Go Cinematic/Full-Screen" icon button fixed in top-right corner (discreet/minimal styling)
- **Full-screen mode**: Hide the toggle button completely
- Button should trigger smooth transition between modes

### 3. Video Overlays
- Add overlay containers on the video itself (corner placement) for:
  - **Mini-map**: Small route map showing current position
  - **Route info**: Key route statistics (distance, elevation, etc.)
- These overlays should be positioned to avoid obstructing the main video content
- Consider semi-transparent backgrounds for better video visibility

### 4. Visual Design
- Control panel should use TVS glass styling (consistent with existing widgets)
- Progress bar should be easily visible and interactive
- Overlays should be collapsible/hideable (optional enhancement)
- Smooth animations for transitions between normal and full-screen modes

## Technical Notes
- Component: `src/app.js` (main TVS video player)
- Ensure controls remain accessible when in full-screen mode
- Test on different screen sizes/aspect ratios
- Consider keyboard shortcuts for full-screen toggle (e.g., 'F' key)
- Maintain compatibility with existing save/upload functionality

## Acceptance Criteria
- [ ] Fixed control panel at bottom with progress bar integrated into top edge
- [ ] Full-screen toggle button appears in normal mode, disappears in full-screen
- [ ] Overlay placeholders for mini-map and route info positioned on video
- [ ] Smooth transitions between viewing modes
- [ ] All player controls remain functional in both modes
- [ ] Responsive design works on various screen sizes

## Milestone
1.2
