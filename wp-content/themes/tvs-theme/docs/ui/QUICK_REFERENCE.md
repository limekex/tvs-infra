# TVS Design Tokens - Quick Reference

## Most Common Tokens

### Colors
```css
/* Backgrounds */
--tvs-color-bg-primary         /* #0a0a0b - Main background */
--tvs-color-surface-base       /* #1a1b1e - Card background */

/* Text */
--tvs-color-text-primary       /* #ffffff - Main text */
--tvs-color-text-secondary     /* #b8b9bc - Secondary text */

/* Accent */
--tvs-color-primary            /* #4aa6e0 - Primary blue */
--tvs-color-accent             /* #00d9ff - Accent cyan */
--tvs-color-neon-cyan          /* #00ffff - Neon highlight */
```

### Spacing
```css
--tvs-space-2    /* 8px  - Tight spacing */
--tvs-space-4    /* 16px - Base spacing */
--tvs-space-6    /* 24px - Card padding */
--tvs-space-8    /* 32px - Section spacing */
```

### Typography
```css
--tvs-text-sm    /* 14px - Small text */
--tvs-text-base  /* 16px - Body text */
--tvs-text-xl    /* 20px - Headings */
--tvs-text-2xl   /* 24px - Large headings */

--tvs-font-normal    /* 400 */
--tvs-font-semibold  /* 600 */
--tvs-font-bold      /* 700 */
```

### Component Shortcuts
```css
/* Card */
--tvs-card-bg        /* Background */
--tvs-card-border    /* Border color */
--tvs-card-radius    /* Border radius (12px) */
--tvs-card-padding   /* Padding (24px) */
--tvs-card-shadow    /* Box shadow */

/* Badge */
--tvs-badge-radius   /* Border radius (full) */
--tvs-badge-padding-x
--tvs-badge-padding-y
```

### Effects
```css
/* Shadows */
--tvs-shadow-md              /* Standard shadow */
--tvs-shadow-glow-cyan       /* Neon glow */

/* Blur */
--tvs-blur-lg                /* Glass effect (16px) */

/* Transitions */
--tvs-transition-all         /* Smooth transitions */
```

## Common Patterns

### Card Component
```css
.my-card {
  background: var(--tvs-card-bg);
  border: 1px solid var(--tvs-card-border);
  border-radius: var(--tvs-card-radius);
  padding: var(--tvs-card-padding);
  box-shadow: var(--tvs-card-shadow);
  transition: var(--tvs-transition-transform), var(--tvs-transition-shadow);
}

.my-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--tvs-card-shadow-hover);
}
```

### Badge
```html
<span class="tvs-badge tvs-badge-primary">Label</span>
<span class="tvs-badge tvs-badge-neon">Neon</span>
```

### Glass Effect
```html
<div class="tvs-glass" style="
  padding: var(--tvs-space-6);
  border-radius: var(--tvs-radius-lg);
">
  Content with glass effect
</div>
```

### Gradient Background
```css
.hero {
  background: var(--tvs-gradient-primary);
  /* or */
  background: var(--tvs-gradient-neon);
}
```

### Stats Display
```html
<div class="tvs-card">
  <span class="tvs-badge tvs-badge-neon">ELITE</span>
  <div style="
    font-size: var(--tvs-text-5xl);
    font-weight: var(--tvs-font-extrabold);
    color: var(--tvs-color-text-primary);
  ">
    1,234
  </div>
  <div style="color: var(--tvs-color-text-secondary);">
    Total Distance
  </div>
</div>
```

## React Usage
```javascript
import tokens from '../../assets/css/tvs-tokens.json';

const styles = {
  card: {
    backgroundColor: tokens.colors.surface.base,
    padding: tokens.spacing[6],
    borderRadius: tokens.radius.lg,
    boxShadow: tokens.shadows.md,
  },
  title: {
    color: tokens.colors.text.primary,
    fontSize: tokens.typography.fontSize['2xl'],
    fontWeight: tokens.typography.fontWeight.bold,
  },
};
```

## Color Combinations (AA Compliant)

✅ **Safe Combinations:**
```css
/* Primary text on dark backgrounds */
color: var(--tvs-color-text-primary);
background: var(--tvs-color-bg-primary);

/* Secondary text on dark backgrounds */
color: var(--tvs-color-text-secondary);
background: var(--tvs-color-surface-base);

/* Dark text on bright accents */
color: var(--tvs-color-text-on-accent);  /* #0a0a0b */
background: var(--tvs-color-accent);     /* #00d9ff */
```

❌ **Avoid:**
```css
/* White text on bright colors */
color: #ffffff;
background: var(--tvs-color-accent);  /* Poor contrast */
```

## Mobile-First Tips

```css
/* Mobile base */
.component {
  padding: var(--tvs-space-4);
  font-size: var(--tvs-text-base);
}

/* Tablet and up */
@media (min-width: 768px) {
  .component {
    padding: var(--tvs-space-6);
    font-size: var(--tvs-text-lg);
  }
}
```

## Z-Index Layers

```css
/* Use these for proper stacking */
--tvs-z-dropdown      /* 1000 */
--tvs-z-sticky        /* 1100 */
--tvs-z-fixed         /* 1200 */
--tvs-z-modal         /* 1400 */
--tvs-z-tooltip       /* 1600 */
--tvs-z-notification  /* 1700 */
```

## Full Documentation

- **Complete guide**: `docs/ui/tokens.md`
- **Visual examples**: `docs/ui/examples.html` (open in browser)
- **Token files**: `assets/css/tvs-tokens.css` and `tvs-tokens.json`
