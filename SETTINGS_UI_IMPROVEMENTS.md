# Settings UI Improvements - Uniform Dark Mode & Filtering

## Overview
Complete redesign of the Settings page with:
- **Uniform styling** across all setting controls
- **Dark mode consistency** throughout entire page
- **Difficulty level filtering** for theorems
- **Math domain filtering** based on selected subjects
- **Responsive design** for all screen sizes

---

## Key Improvements

### 1. Uniform CSS Variables System
```css
:root {
  --color-bg-light: #ffffff;
  --color-bg-dark: #111827;
  --color-text-primary-light: #1f2937;
  --color-text-primary-dark: #f3f4f6;
  /* ... all colors defined here ... */
}

body.theme-dark {
  /* Automatically switches all variables for dark mode */
}
```

**Benefits:**
- Single source of truth for all colors
- Automatic dark mode application to entire page
- Consistent styling across all controls
- Easy to update theme colors globally

### 2. Dark Mode Consistency
When user selects **Dark Mode**:
- ✓ Page background turns dark (#111827)
- ✓ Section backgrounds (#1f2937)
- ✓ Text becomes readable (#f3f4f6)
- ✓ Borders adjust (#374151)
- ✓ Buttons, toggles, and selects all adapt
- ✓ Domain cards maintain visibility with green accents
- ✓ All interactive elements remain functional

**Example Dark Mode Colors:**
```
Background:    #111827 (very dark navy)
Sections:      #1f2937 (dark gray)
Text Primary:  #f3f4f6 (light gray)
Text Secondary:#d1d5db (medium gray)
Borders:       #374151 (dark border)
Accent:        #60a5fa (light blue)
Success:       #10b981 (green - for active states)
```

### 3. Difficulty Level Selector
**Before:** Dropdown select element
**After:** Three button buttons for easy selection

```html
<div class="difficulty-selector">
  <button class="difficulty-btn active" data-difficulty="beginner">🟢 Beginner</button>
  <button class="difficulty-btn" data-difficulty="intermediate">🟡 Intermediate</button>
  <button class="difficulty-btn" data-difficulty="advanced">🔴 Advanced</button>
</div>
```

**Features:**
- Visual emoji indicators (green, yellow, red)
- Click any button to select difficulty
- Active button highlighted in blue
- Hover effects for better UX
- Automatically filters theorems on the Theorems page

### 4. Math Domain Filtering
User can select multiple math domains, and theorems page will filter based on selection:

**Selected Domains:**
- Real Analysis (primary, always highlighted)
- Algebra
- Calculus
- Linear Algebra
- Discrete Math
- Geometry
- Logic & Set Theory
- Probability & Statistics
- Abstract Algebra
- Topology
- Numerical Analysis
- Functional Analysis

**Filtering Logic:**
1. User selects/deselects domain checkbox
2. Settings saves selection to `preferredDomains`
3. Event dispatched: `domains-changed`
4. Theorems page listens and filters results
5. Visual feedback confirms action

### 5. Uniform Control Styling

**Toggle Switches:**
- Consistent 54px × 28px size
- Green when enabled (#10b981)
- Blue hover when disabled
- Smooth 0.3s transitions

**Button Groups:**
- All buttons same height/padding
- Blue accent when active
- Hover effects consistent
- Full width on mobile

**Select Dropdowns:**
- Dropdown styling matches button group
- Dark mode text readable
- Focus ring with accent color
- Proper dark mode option rendering

**Domain Cards:**
- Uniform grid layout (auto-fit columns)
- Green accent for primary (Real Analysis)
- Checkmark badge appears when selected
- Hover effects consistent
- Fully dark-mode compatible

### 6. Color Consistency

**Light Mode:**
- Background: White (#ffffff)
- Sections: Off-white (#f9fafb)
- Text: Dark gray (#1f2937)
- Borders: Light gray (#e5e7eb)
- Accents: Blue (#3b82f6)

**Dark Mode:**
- Background: Very dark (#111827)
- Sections: Dark gray (#1f2937)
- Text: Light gray (#f3f4f6)
- Borders: Darker gray (#374151)
- Accents: Light blue (#60a5fa)

**All Controls Update Automatically** based on `body.theme-dark` class

---

## Event System

### Custom Events Dispatched

**1. difficulty-changed**
```javascript
window.dispatchEvent(new CustomEvent('difficulty-changed', { 
  detail: { difficulty: 'beginner' } 
}));
```
Listener can filter theorems by difficulty.

**2. domains-changed**
```javascript
window.dispatchEvent(new CustomEvent('domains-changed', { 
  detail: { domains: ['real-analysis', 'calculus'] } 
}));
```
Listener can filter theorems by selected domains.

### Data Storage

Settings stored in localStorage with keys:
- `selectedDifficulty` - Current difficulty level
- `selectedDomains` - JSON array of selected domain IDs

---

## JavaScript Implementation

### Theme Application
```javascript
applyTheme() {
  const theme = globalSettings.getAllSettings().theme;
  
  if (theme === 'dark') {
    document.body.classList.add('theme-dark');
    // All CSS variables automatically switch
  }
  // Theme applied to entire page and all child elements
}
```

### Difficulty Level Handler
```javascript
// Listen for difficulty selection
document.querySelectorAll('.difficulty-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    const difficulty = e.target.dataset.difficulty;
    
    // Save to settings
    globalSettings?.updateSetting('difficultyLevel', difficulty);
    
    // Update UI
    document.querySelectorAll('.difficulty-btn').forEach(b => 
      b.classList.remove('active')
    );
    e.target.classList.add('active');
    
    // Filter theorems
    this.filterTheoremsByDifficulty(difficulty);
    
    // Show feedback
    showSuccess(`✓ Difficulty level set to ${difficulty}!`);
  });
});
```

### Domain Filtering Handler
```javascript
// Listen for domain selection changes
document.querySelectorAll('[name="domains"]').forEach(checkbox => {
  checkbox.addEventListener('change', (e) => {
    // Get all selected domains
    const selectedDomains = Array.from(
      document.querySelectorAll('[name="domains"]:checked')
    ).map(checkbox => checkbox.value);
    
    // Save and dispatch event
    globalSettings?.updateSetting('preferredDomains', selectedDomains);
    window.dispatchEvent(new CustomEvent('domains-changed', { 
      detail: { domains: selectedDomains } 
    }));
  });
});
```

---

## Responsive Design

### Desktop (1024px+)
- Domain cards: 4-5 columns per row
- Settings items: horizontal layout
- Full-size buttons and controls

### Tablet (768px - 1023px)
- Domain cards: 3-4 columns per row
- Settings items: flex-wrap to next line if needed
- Controls: medium size

### Mobile (480px - 767px)
- Domain cards: 2 columns per row
- Settings items: vertical stack (flex-direction: column)
- Full-width controls

### Extra Small (<480px)
- Domain cards: 2 columns per row
- Horizontal scrolling for difficulty buttons
- Single column layout

---

## Integration with Theorems Page

### To Filter Theorems by Difficulty:
```javascript
// In theorems.html
window.addEventListener('difficulty-changed', (e) => {
  const difficulty = e.detail.difficulty;
  
  // Filter displayed theorems
  theorems = theorems.filter(t => t.difficulty === difficulty);
  renderTheorems(theorems);
});
```

### To Filter Theorems by Domains:
```javascript
// In theorems.html
window.addEventListener('domains-changed', (e) => {
  const domains = e.detail.domains;
  
  // Filter theorems that match selected domains
  theorems = allTheorems.filter(t => 
    t.domains.some(d => domains.includes(d))
  );
  renderTheorems(theorems);
});
```

---

## Testing Checklist

- [ ] Light mode displays correctly with white backgrounds
- [ ] Dark mode enabled - entire page becomes dark and readable
- [ ] Difficulty buttons - click each level, verify settings save
- [ ] Domain cards - select/deselect multiple domains
- [ ] Theme toggle - immediate page update
- [ ] Font size buttons - text resizes correctly
- [ ] Toggle switches - all can be enabled/disabled
- [ ] Mobile responsive - test on 480px, 768px, 1024px widths
- [ ] Dark mode on mobile - still readable
- [ ] Color contrast - text readable in both light and dark modes
- [ ] Domain cards hover - animations smooth
- [ ] Save button - all settings persist after reload

---

## Files Modified

1. **frontend/pages/settings.html**
   - Complete CSS rewrite with variables
   - Difficulty level selector (buttons instead of dropdown)
   - Domain card filtering improvements
   - Enhanced JavaScript with event dispatching
   - Dark mode applied to all elements

---

## Color Reference

### Light Mode Palette
```
Primary Background:    #ffffff (white)
Section Background:    #f9fafb (off-white)
Primary Text:          #1f2937 (dark gray)
Secondary Text:        #6b7280 (medium gray)
Borders:               #e5e7eb (light gray)
Accent:                #3b82f6 (blue)
Success:               #10b981 (green)
```

### Dark Mode Palette
```
Primary Background:    #111827 (very dark navy)
Section Background:    #1f2937 (dark gray)
Primary Text:          #f3f4f6 (light gray)
Secondary Text:        #d1d5db (medium gray)
Borders:               #374151 (dark gray)
Accent:                #60a5fa (light blue)
Success:               #10b981 (green)
```

---

## Performance Notes

- CSS variables enable instant theme switching without repainting entire DOM
- Event-based filtering prevents unnecessary page reloads
- localStorage caching for faster settings retrieval
- No external libraries required - pure vanilla JavaScript/CSS

---

## Future Enhancements

- [ ] Add color theme selector (beyond light/dark)
- [ ] Export/import settings
- [ ] Settings sync across devices via backend
- [ ] Settings history/undo
- [ ] Keyboard shortcuts for quick access
