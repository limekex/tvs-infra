# TVS Theme - UI Documentation

This directory contains documentation for the TVS (Norway Virtual Sport) theme's design system and UI components.

## Files

### tokens.md
Comprehensive documentation of the TVS design token system, including:
- Color palette and usage
- Spacing scale
- Typography system
- Shadows and effects
- Component patterns
- Accessibility guidelines
- React/JavaScript integration

### examples.html
Interactive visual showcase of all design tokens and components. Open this file directly in your browser to see:
- Card components
- Badge and chip variations
- Panel layouts
- Button styles
- Color palette
- Typography scale
- Glass effects

## Quick Start

### View Visual Examples
```bash
# Open in your default browser
open docs/ui/examples.html

# Or navigate to the file and open it
```

### Read Documentation
```bash
# View in terminal
cat docs/ui/tokens.md

# Or open in your preferred markdown viewer
```

### Use Tokens in Your Code

**CSS:**
```css
.my-component {
  background: var(--tvs-card-bg);
  color: var(--tvs-color-text-primary);
  padding: var(--tvs-space-6);
  border-radius: var(--tvs-radius-lg);
}
```

**React:**
```javascript
import tokens from '../../assets/css/tvs-tokens.json';

const styles = {
  card: {
    backgroundColor: tokens.colors.surface.base,
    padding: tokens.spacing[6],
  }
};
```

## Design System Files

The actual design token files are located at:
- `assets/css/tvs-tokens.css` - CSS custom properties (loaded globally)
- `assets/css/tvs-tokens.json` - JSON format for JavaScript/React

## Contributing

When updating design tokens:
1. Modify both `.css` and `.json` files to keep them in sync
2. Update documentation in `tokens.md` if adding new tokens
3. Add visual examples to `examples.html` for new components
4. Verify AA contrast compliance for any color changes
5. Test on mobile devices for responsive behavior

## Support

For questions about the design system or how to use tokens, refer to `tokens.md` or check the visual examples in `examples.html`.
