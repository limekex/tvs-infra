# TVS Design Tokens Documentation

## Overview

The TVS design tokens system provides a comprehensive set of CSS custom properties and JSON values for building a consistent, dark-themed, mobile-first user interface with neon/gradient accents.

**Location:**
- CSS: `assets/css/tvs-tokens.css`
- JSON: `assets/css/tvs-tokens.json`

**Features:**
- ✅ Dark theme with AA contrast compliance
- ✅ Mobile-first responsive design
- ✅ Neon/gradient accent system
- ✅ Glass/blur effects (glassmorphism)
- ✅ Comprehensive spacing and typography scales
- ✅ Pre-built component tokens

---

## Color System

### Background Colors

Dark theme backgrounds with layered depth:

```css
/* Primary backgrounds */
background-color: var(--tvs-color-bg-primary);    /* #0a0a0b - Deepest dark */
background-color: var(--tvs-color-bg-secondary);  /* #16171a - Secondary dark */
background-color: var(--tvs-color-bg-tertiary);   /* #1e2023 - Tertiary dark */
background-color: var(--tvs-color-bg-elevated);   /* #252729 - Elevated surfaces */
```

**Usage Example:**
```html
<body style="background-color: var(--tvs-color-bg-primary);">
  <div style="background-color: var(--tvs-color-bg-secondary);">
    Content with elevated background
  </div>
</body>
```

### Surface Colors (Cards, Panels)

```css
background-color: var(--tvs-color-surface-base);     /* #1a1b1e - Base surface */
background-color: var(--tvs-color-surface-raised);   /* #242529 - Raised surface */
background-color: var(--tvs-color-surface-overlay);  /* #2d2e33 - Overlay surface */
```

### Neon Accent Colors

Vibrant neon colors for highlights and accents:

```css
color: var(--tvs-color-neon-cyan);      /* #00ffff - Cyan */
color: var(--tvs-color-neon-magenta);   /* #ff00ff - Magenta */
color: var(--tvs-color-neon-lime);      /* #ccff00 - Lime */
color: var(--tvs-color-neon-orange);    /* #ff6b35 - Orange */
```

**Visual Sample:**
```html
<div style="display: flex; gap: 1rem;">
  <span style="color: var(--tvs-color-neon-cyan);">■ Cyan</span>
  <span style="color: var(--tvs-color-neon-magenta);">■ Magenta</span>
  <span style="color: var(--tvs-color-neon-lime);">■ Lime</span>
  <span style="color: var(--tvs-color-neon-orange);">■ Orange</span>
</div>
```

### Primary & Accent Colors

```css
/* Primary colors */
background-color: var(--tvs-color-primary);       /* #4aa6e0 - Primary blue */
background-color: var(--tvs-color-primary-light); /* #6bc1f0 - Light variant */
background-color: var(--tvs-color-primary-dark);  /* #2a86c0 - Dark variant */

/* Accent colors */
background-color: var(--tvs-color-accent);        /* #00d9ff - Accent cyan */
background-color: var(--tvs-color-accent-warm);   /* #ff6b9d - Warm accent */
```

### Semantic Colors

```css
color: var(--tvs-color-success);  /* #2ecc71 - Success green */
color: var(--tvs-color-warning);  /* #f39c12 - Warning orange */
color: var(--tvs-color-error);    /* #e74c3c - Error red */
```

### Text Colors (AA Contrast Compliant)

All text colors meet WCAG AA contrast requirements:

```css
color: var(--tvs-color-text-primary);    /* #ffffff - Primary text */
color: var(--tvs-color-text-secondary);  /* #b8b9bc - Secondary text */
color: var(--tvs-color-text-tertiary);   /* #7a7b7e - Tertiary text */
color: var(--tvs-color-text-disabled);   /* #4a4b4e - Disabled text */
```

### Gradients

Pre-defined gradient combinations:

```css
/* Primary gradient */
background: var(--tvs-gradient-primary);
/* linear-gradient(135deg, #4aa6e0 0%, #00d9ff 100%) */

/* Neon gradient */
background: var(--tvs-gradient-neon);
/* linear-gradient(135deg, #00ffff 0%, #ff00ff 50%, #ccff00 100%) */

/* Warm gradient */
background: var(--tvs-gradient-warm);
/* linear-gradient(135deg, #ff6b35 0%, #ff6b9d 100%) */

/* Subtle gradient */
background: var(--tvs-gradient-subtle);
/* linear-gradient(135deg, #1e2023 0%, #252729 100%) */
```

**Visual Example:**
```html
<div style="
  background: var(--tvs-gradient-neon);
  padding: var(--tvs-space-8);
  border-radius: var(--tvs-radius-xl);
  color: var(--tvs-color-text-on-accent);
  font-weight: var(--tvs-font-bold);
  text-align: center;
">
  Neon Gradient Banner
</div>
```

---

## Spacing Scale

Consistent spacing system based on 4px increments:

```css
padding: var(--tvs-space-0);   /* 0 */
padding: var(--tvs-space-1);   /* 4px */
padding: var(--tvs-space-2);   /* 8px */
padding: var(--tvs-space-3);   /* 12px */
padding: var(--tvs-space-4);   /* 16px */
padding: var(--tvs-space-5);   /* 20px */
padding: var(--tvs-space-6);   /* 24px */
padding: var(--tvs-space-8);   /* 32px */
padding: var(--tvs-space-10);  /* 40px */
padding: var(--tvs-space-12);  /* 48px */
padding: var(--tvs-space-16);  /* 64px */
padding: var(--tvs-space-20);  /* 80px */
padding: var(--tvs-space-24);  /* 96px */
```

**Usage Example:**
```css
.container {
  padding: var(--tvs-space-8);
  margin-bottom: var(--tvs-space-6);
  gap: var(--tvs-space-4);
}
```

---

## Border Radius

Round corners for cards and UI elements:

```css
border-radius: var(--tvs-radius-none);  /* 0 */
border-radius: var(--tvs-radius-sm);    /* 6px - small elements */
border-radius: var(--tvs-radius-md);    /* 8px - buttons, inputs */
border-radius: var(--tvs-radius-lg);    /* 12px - cards */
border-radius: var(--tvs-radius-xl);    /* 16px - large cards */
border-radius: var(--tvs-radius-2xl);   /* 24px - hero elements */
border-radius: var(--tvs-radius-full);  /* 9999px - pills, badges */
```

---

## Shadows

### Standard Shadows

```css
box-shadow: var(--tvs-shadow-xs);   /* Subtle shadow */
box-shadow: var(--tvs-shadow-sm);   /* Small shadow */
box-shadow: var(--tvs-shadow-md);   /* Medium shadow (default) */
box-shadow: var(--tvs-shadow-lg);   /* Large shadow */
box-shadow: var(--tvs-shadow-xl);   /* Extra large shadow */
box-shadow: var(--tvs-shadow-2xl);  /* Huge shadow */
```

### Glow/Neon Shadows

Special effects for highlighted elements:

```css
box-shadow: var(--tvs-shadow-glow-cyan);     /* Cyan glow */
box-shadow: var(--tvs-shadow-glow-magenta);  /* Magenta glow */
box-shadow: var(--tvs-shadow-glow-primary);  /* Primary glow */
box-shadow: var(--tvs-shadow-glow-accent);   /* Accent glow */
```

**Example:**
```html
<button style="
  background: var(--tvs-color-primary);
  color: var(--tvs-color-text-on-primary);
  border: none;
  padding: var(--tvs-space-3) var(--tvs-space-6);
  border-radius: var(--tvs-radius-md);
  box-shadow: var(--tvs-shadow-glow-primary);
">
  Glowing Button
</button>
```

---

## Typography

### Font Families

```css
font-family: var(--tvs-font-sans);  /* System sans-serif stack */
font-family: var(--tvs-font-mono);  /* Monospace stack */
```

### Font Sizes

```css
font-size: var(--tvs-text-xs);    /* 12px */
font-size: var(--tvs-text-sm);    /* 14px */
font-size: var(--tvs-text-base);  /* 16px */
font-size: var(--tvs-text-lg);    /* 18px */
font-size: var(--tvs-text-xl);    /* 20px */
font-size: var(--tvs-text-2xl);   /* 24px */
font-size: var(--tvs-text-3xl);   /* 30px */
font-size: var(--tvs-text-4xl);   /* 36px */
font-size: var(--tvs-text-5xl);   /* 48px */
font-size: var(--tvs-text-6xl);   /* 60px */
```

### Font Weights

```css
font-weight: var(--tvs-font-light);      /* 300 */
font-weight: var(--tvs-font-normal);     /* 400 */
font-weight: var(--tvs-font-medium);     /* 500 */
font-weight: var(--tvs-font-semibold);   /* 600 */
font-weight: var(--tvs-font-bold);       /* 700 */
font-weight: var(--tvs-font-extrabold);  /* 800 */
```

---

## Component Patterns

### Card Component

```html
<div class="tvs-card">
  <h3 style="
    color: var(--tvs-color-text-primary);
    margin-bottom: var(--tvs-space-3);
  ">
    Card Title
  </h3>
  <p style="color: var(--tvs-color-text-secondary);">
    Card content with secondary text color
  </p>
</div>
```

**CSS:**
```css
.tvs-card {
  background: var(--tvs-card-bg);
  border: 1px solid var(--tvs-card-border);
  border-radius: var(--tvs-card-radius);
  padding: var(--tvs-card-padding);
  box-shadow: var(--tvs-card-shadow);
  transition: var(--tvs-transition-transform), var(--tvs-transition-shadow);
}

.tvs-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--tvs-card-shadow-hover);
}
```

**Visual Preview:**
```
┌─────────────────────────────────┐
│                                 │
│  Card Title                     │
│  Card content with secondary    │
│  text color                     │
│                                 │
└─────────────────────────────────┘
Background: #1a1b1e
Border: #2d2e33
Radius: 12px
Padding: 24px
```

### Badge Component

```html
<span class="tvs-badge tvs-badge-primary">Primary</span>
<span class="tvs-badge tvs-badge-accent">Accent</span>
<span class="tvs-badge tvs-badge-neon">Neon</span>
```

**CSS:**
```css
.tvs-badge {
  display: inline-flex;
  align-items: center;
  border-radius: var(--tvs-badge-radius);
  padding: var(--tvs-badge-padding-y) var(--tvs-badge-padding-x);
  font-size: var(--tvs-badge-font-size);
  font-weight: var(--tvs-badge-font-weight);
  text-transform: uppercase;
  letter-spacing: var(--tvs-tracking-wide);
}

.tvs-badge-primary {
  background: var(--tvs-color-primary);
  color: var(--tvs-color-text-on-primary);
}

.tvs-badge-neon {
  background: var(--tvs-gradient-neon);
  color: var(--tvs-color-text-on-accent);
  box-shadow: var(--tvs-shadow-glow-cyan);
}
```

### Panel Component

```html
<div class="tvs-panel">
  <h2>Panel Title</h2>
  <p>Panel content area</p>
</div>
```

**CSS:**
```css
.tvs-panel {
  background: var(--tvs-panel-bg);
  border: 1px solid var(--tvs-panel-border);
  border-radius: var(--tvs-panel-radius);
  padding: var(--tvs-panel-padding);
}
```

### Glass Effect (Glassmorphism)

```html
<div class="tvs-glass" style="
  padding: var(--tvs-space-8);
  border-radius: var(--tvs-radius-xl);
">
  Glass effect content
</div>
```

**CSS:**
```css
.tvs-glass {
  background: var(--tvs-glass-bg);
  backdrop-filter: blur(var(--tvs-glass-blur));
  -webkit-backdrop-filter: blur(var(--tvs-glass-blur));
  border: 1px solid var(--tvs-glass-border);
}
```

---

## React/JavaScript Usage

Import tokens in React components:

```javascript
import tokens from '../../assets/css/tvs-tokens.json';

const MyComponent = () => {
  const styles = {
    container: {
      backgroundColor: tokens.colors.surface.base,
      borderRadius: tokens.radius.lg,
      padding: tokens.spacing[6],
      boxShadow: tokens.shadows.md,
    },
    title: {
      color: tokens.colors.text.primary,
      fontSize: tokens.typography.fontSize['2xl'],
      fontWeight: tokens.typography.fontWeight.bold,
    },
  };

  return (
    <div style={styles.container}>
      <h2 style={styles.title}>Card Title</h2>
    </div>
  );
};
```

---

## Accessibility (AA Contrast Compliance)

All text and icon colors meet WCAG AA contrast requirements:

| Combination | Contrast Ratio | Status |
|-------------|----------------|--------|
| Primary text (#fff) on Primary BG (#0a0a0b) | 19.79:1 | ✅ AAA |
| Secondary text (#b8b9bc) on Primary BG | 10.09:1 | ✅ AAA |
| Tertiary text (#7a7b7e) on Primary BG | 4.68:1 | ✅ AA |
| Primary text (#fff) on Surface Base (#1a1b1e) | 17.22:1 | ✅ AAA |
| Dark text (#0a0a0b) on Accent (#00d9ff) | 11.66:1 | ✅ AAA |

**Important:** When using bright accent colors like Primary (#4aa6e0) or Accent (#00d9ff) as backgrounds, always use dark text (`--tvs-color-text-on-accent`) to maintain AA contrast compliance.

**Testing:**
Use browser DevTools or online contrast checkers to verify contrast ratios when combining colors.

---

## Examples in Context

### Navigation Bar

```html
<nav style="
  background: var(--tvs-color-surface-base);
  border-bottom: 1px solid var(--tvs-color-border-subtle);
  padding: var(--tvs-space-4) var(--tvs-space-6);
">
  <a href="#" style="
    color: var(--tvs-color-text-primary);
    font-weight: var(--tvs-font-medium);
  ">
    Home
  </a>
</nav>
```

### Player Stats Card

```html
<div class="tvs-card">
  <div style="margin-bottom: var(--tvs-space-4);">
    <span class="tvs-badge tvs-badge-neon">ELITE</span>
  </div>
  
  <h3 style="
    color: var(--tvs-color-text-primary);
    font-size: var(--tvs-text-2xl);
    font-weight: var(--tvs-font-bold);
    margin-bottom: var(--tvs-space-2);
  ">
    1,234 km
  </h3>
  
  <p style="
    color: var(--tvs-color-text-secondary);
    font-size: var(--tvs-text-sm);
  ">
    Total Distance
  </p>
</div>
```

### Activity Chip

```html
<span style="
  display: inline-flex;
  align-items: center;
  gap: var(--tvs-space-2);
  background: var(--tvs-color-surface-raised);
  border: 1px solid var(--tvs-color-border-default);
  border-radius: var(--tvs-radius-full);
  padding: var(--tvs-space-2) var(--tvs-space-4);
  font-size: var(--tvs-text-sm);
  color: var(--tvs-color-text-secondary);
">
  <span style="
    width: 8px;
    height: 8px;
    background: var(--tvs-color-success);
    border-radius: var(--tvs-radius-full);
    box-shadow: var(--tvs-shadow-glow-primary);
  "></span>
  Active
</span>
```

---

## Mobile-First Approach

All tokens are designed to work seamlessly on mobile devices:

- Touch-friendly spacing (minimum 44px tap targets)
- Readable text sizes (minimum 16px base font)
- High contrast for outdoor visibility
- Optimized for smaller screens

```css
/* Mobile base styles using tokens */
.mobile-card {
  padding: var(--tvs-space-4);  /* 16px on mobile */
}

/* Tablet and up */
@media (min-width: 768px) {
  .mobile-card {
    padding: var(--tvs-space-6);  /* 24px on larger screens */
  }
}
```

---

## Best Practices

1. **Always use tokens instead of hard-coded values**
   ```css
   /* ❌ Bad */
   color: #ffffff;
   padding: 16px;
   
   /* ✅ Good */
   color: var(--tvs-color-text-primary);
   padding: var(--tvs-space-4);
   ```

2. **Use semantic color names**
   - Use `--tvs-color-text-primary` instead of `--tvs-color-white`
   - Use `--tvs-color-success` for positive actions
   - Use `--tvs-color-error` for errors

3. **Combine tokens for complex effects**
   ```css
   .special-card {
     background: var(--tvs-color-surface-base);
     border: 1px solid var(--tvs-color-border-accent);
     border-radius: var(--tvs-radius-xl);
     padding: var(--tvs-space-6);
     box-shadow: var(--tvs-shadow-lg), var(--tvs-shadow-glow-primary);
   }
   ```

4. **Maintain consistency across components**
   - Reuse component tokens (e.g., `--tvs-card-*`) for similar elements
   - Use the spacing scale consistently
   - Apply the same transition timings

---

## Token Updates

When updating tokens:

1. Modify `assets/css/tvs-tokens.css`
2. Update `assets/css/tvs-tokens.json` to match
3. Test affected components
4. Document any breaking changes

---

## Support

For questions or issues with design tokens, please refer to:
- This documentation
- Theme source code in `assets/css/`
- Block implementations in `blocks/`
