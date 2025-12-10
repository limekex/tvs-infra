# Testing Guide for TVS Design Tokens

## Quick Testing Steps

### 1. Visual Verification (Fastest)
Open the visual examples file directly in your browser:

```bash
# From the theme root directory
open docs/ui/examples.html
# or on Linux:
xdg-open docs/ui/examples.html
# or on Windows:
start docs/ui/examples.html
```

**What to verify:**
- [ ] Dark background is visible (#0a0a0b)
- [ ] Cards have rounded corners and subtle shadows
- [ ] Badges show with different color variations
- [ ] Neon gradients are visible and vibrant
- [ ] Text is readable (good contrast)
- [ ] Glass effects show blur (modern browsers)
- [ ] Typography scale looks proportional

### 2. WordPress Integration Test

If you have a WordPress environment set up:

```bash
# 1. Navigate to your WordPress themes directory
cd /path/to/wordpress/wp-content/themes/

# 2. Copy or link this theme
# (or activate if already installed)

# 3. Visit your WordPress site
# The tokens are automatically loaded globally via inc/enqueue.php
```

**Pages to check:**
- [ ] Any page with route cards (should use dark theme)
- [ ] Check browser console - no CSS errors
- [ ] Responsive behavior on mobile (resize browser)
- [ ] Dev overlay (if enabled) uses new token styles

### 3. Token Validation

Verify the tokens are properly structured:

```bash
# Check JSON is valid
node -e "console.log(require('./assets/css/tvs-tokens.json'))"

# Check CSS has no syntax errors
# Open in browser console or use a CSS validator
```

### 4. Contrast Verification

All color combinations have been tested for AA compliance:

| Combination | Ratio | Status |
|-------------|-------|--------|
| Primary text on Primary BG | 19.79:1 | ✅ AAA |
| Secondary text on Primary BG | 10.09:1 | ✅ AAA |
| Tertiary text on Primary BG | 4.68:1 | ✅ AA |
| Dark text on Accent | 11.66:1 | ✅ AAA |

**Manual verification:**
1. Use browser DevTools contrast checker
2. Or use online tools like [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

### 5. Mobile Responsive Test

```bash
# In browser DevTools:
# 1. Open docs/ui/examples.html
# 2. Open DevTools (F12)
# 3. Toggle device toolbar (Ctrl+Shift+M)
# 4. Test different viewports:
#    - iPhone SE (375px)
#    - iPad (768px)
#    - Desktop (1920px)
```

**What to verify:**
- [ ] Cards resize properly
- [ ] Text is readable at all sizes
- [ ] Spacing adjusts for small screens
- [ ] No horizontal scrolling
- [ ] Touch targets are 44px+ on mobile

## Integration Testing in WordPress

### Check Token Loading

1. View page source in WordPress
2. Look for `<link>` tag loading `tvs-tokens.css`
3. Should appear BEFORE `style.css`

```html
<!-- Should see this in page source -->
<link rel='stylesheet' id='tvs-tokens-css' href='.../assets/css/tvs-tokens.css' />
<link rel='stylesheet' id='nvs-style-css' href='.../style.css' />
```

### Verify Route Cards

If you have route cards on any page:

```bash
# Check in browser DevTools:
# 1. Inspect a route card
# 2. Check computed styles
# 3. Should see CSS variables like:
#    background-color: var(--tvs-card-bg)
#    border-radius: var(--tvs-card-radius)
```

### Test Dev Overlay

If the dev overlay is enabled:

```bash
# Should see:
# - Dark background (#1a1b1e)
# - Subtle border
# - Proper spacing
# - Blue pill badge
```

## React Integration Test

Create a simple test component:

```javascript
// test-component.js
import tokens from './assets/css/tvs-tokens.json';

console.log('Tokens loaded:', {
  primaryBg: tokens.colors.bg.primary,
  spacing: tokens.spacing[6],
  radius: tokens.radius.lg,
});

// Should output:
// {
//   primaryBg: "#0a0a0b",
//   spacing: "1.5rem",
//   radius: "0.75rem"
// }
```

## Common Issues & Solutions

### Issue: Examples.html shows white background
**Solution:** Make sure you opened the file in a browser. The dark theme only works when CSS is loaded.

### Issue: Tokens not applied in WordPress
**Solution:** 
1. Check if `tvs-tokens.css` exists in `assets/css/`
2. Clear WordPress cache
3. Hard refresh browser (Ctrl+Shift+R)

### Issue: Glass effect not visible
**Solution:** 
- Requires modern browser (Chrome 76+, Firefox 103+, Safari 9+)
- `backdrop-filter` may not be supported in older browsers
- This is an enhancement, not critical

### Issue: Colors look different than expected
**Solution:**
1. Check browser color profile settings
2. Verify hex values in DevTools
3. Test on different display

## Pre-Commit Checklist

Before committing the changes, verify:

- [ ] `docs/ui/examples.html` opens and displays correctly
- [ ] No console errors in browser
- [ ] Dark theme is consistent across all examples
- [ ] Text is readable (AA contrast)
- [ ] Mobile responsive works
- [ ] JSON is valid (no syntax errors)
- [ ] All new files are tracked by git

## Files to Stage

When ready to commit:

```bash
git add assets/css/
git add docs/
git add README.md
git add IMPLEMENTATION_SUMMARY.md
git add TESTING_GUIDE.md
git add inc/enqueue.php
git add style.css
git add blocks/routes-grid/style.css
git add assets/tvs-app.css

# Review changes
git diff --staged

# Commit
git commit -m "Add global UI theme and design tokens system

- Create comprehensive design token system (CSS + JSON)
- Implement dark theme with neon/gradient accents
- Add documentation with visual examples
- Update existing components to use tokens
- Ensure AA contrast compliance
- Support mobile-first responsive design"
```

## Performance Check

The token system is lightweight:
- `tvs-tokens.css`: ~12KB (minified would be ~8KB)
- `tvs-tokens.json`: ~4.8KB
- No JavaScript required (CSS-only)
- Loaded once, cached by browser

## Browser Compatibility

Tokens work in all modern browsers:
- ✅ Chrome/Edge 88+
- ✅ Firefox 85+
- ✅ Safari 14+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

CSS custom properties (variables) have 97%+ global support.

## Next Steps After Testing

Once testing is complete and you're satisfied:

1. Commit the changes (see above)
2. Push to the branch
3. The tokens are ready to use in:
   - Navigation components
   - Player/stats displays
   - Forms and inputs
   - Modals and overlays
   - Any new UI components

## Documentation References

- **Full token documentation**: `docs/ui/tokens.md`
- **Quick reference**: `docs/ui/QUICK_REFERENCE.md`
- **Visual examples**: `docs/ui/examples.html`
- **Implementation details**: `IMPLEMENTATION_SUMMARY.md`

## Support

If you encounter any issues or have questions:
1. Check the documentation in `docs/ui/`
2. Review the implementation summary
3. Inspect the token definitions in `assets/css/tvs-tokens.css`
4. Test with `docs/ui/examples.html`
