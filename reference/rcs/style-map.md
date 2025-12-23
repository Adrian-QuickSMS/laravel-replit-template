# RCS Style Map

This document maps the design tokens in `/src/rcs/tokens.ts` to the reference screenshots used to derive each value.

## Token Derivation

### Card Tokens

| Token | Value | Source Screenshot | Notes |
|-------|-------|-------------------|-------|
| `cardRadius` | 12px | Phone Preview Rich Card.png, Short Media - Description - Text - Buttons.png | Visible rounded corners on card container; matches Material Design 3 medium rounding |
| `cardPadding` | 16px | Short Media - Description - Text - Buttons.png | Text content inset from card edges |
| `cardPaddingCompact` | 12px | No Media - Text - Button.png | Reduced padding in text-only cards |
| `cardShadow` | subtle | Phone Preview Rich Card.png | Very light shadow visible beneath cards on chat background |
| `cardMaxWidth` | 320px | Phone Preview Rich Card.png | Card width relative to phone screen (~85% of viewport) |

### Header Tokens

| Token | Value | Source Screenshot | Notes |
|-------|-------|-------------------|-------|
| `headerHeight` | 56px | Phone Preview Rich Card.png, Phone Preview Rich Card 2.png | Agent header area from top of screen to chat area |
| `agentLogoSize` | 40px | Phone Preview Rich Card.png (Bridgepoint Runners), Phone Preview Rich Card 2.png (The Big Bank) | Circular agent logo dimensions |
| `logoBorderRadius` | 50% | All Phone Examples | Logos are perfectly circular |
| `verifiedBadgeSize` | 16px | Phone Preview Rich Card.png | Blue checkmark badge next to logo |

### Typography Tokens

| Token | Value | Source Screenshot | Notes |
|-------|-------|-------------------|-------|
| `titleFontSize` | 16px | Short Media - Description - Text - Buttons.png ("Create your custom travel package today") | Bold title text measurement |
| `titleWeight` | 500 | All rich cards | Medium weight for titles |
| `bodyFontSize` | 14px | Short Media - Description - Text - Buttons.png (description text) | Secondary description text |
| `bodyColor` | #5f6368 | All screenshots | Lighter gray for body text vs darker title |
| `timestampSize` | 12px | Phone Preview Rich Card.png ("Today") | Small centered timestamp above message |

### Button Tokens

| Token | Value | Source Screenshot | Notes |
|-------|-------|-------------------|-------|
| `buttonHeight` | 48px | Phone Preview Rich Card.png ("Book gait analysis", "Bestsellers", "Shop now") | Touch-friendly button height per Material guidelines |
| `buttonRadius` | 24px | All button examples | Full pill-shaped rounded corners (height/2) |
| `buttonIconSize` | 20px | Short Media - Description - Text - Buttons.png (globe icons) | Left-aligned icons in buttons |
| `buttonGap` | 8px | Short Media - Description - Text - 4 Buttons.png | Vertical spacing between stacked buttons |
| `buttonPaddingX` | 24px | All buttons | Horizontal padding inside buttons |

### Media Height Tokens

| Token | Value | Source Screenshot | Notes |
|-------|-------|-------------------|-------|
| `shortHeight` | 112px | Short Media - Text - One Button.png | Approximately 1/3 of card height for short media |
| `mediumHeight` | 168px | Medium Media - Description - Text - Buttons.png | Mid-height media taking ~40% of card |
| `tallHeight` | 264px | Rich card - Tall Media - One Button.png | Large media dominating card (~60%+) |
| `mediaBorderRadiusTop` | 12px 12px 0 0 | All cards with media | Rounded top corners only, flat bottom where text begins |

### Carousel Tokens

| Token | Value | Source Screenshot | Notes |
|-------|-------|-------------------|-------|
| `carouselCardWidth` | 256px | Phone Preview Medium Carousel.png | Card width showing partial next card |
| `carouselCardWidthSmall` | 200px | Phone Preview Small Carousel.png | Narrower cards for small carousel mode |
| `carouselGap` | 8px | Carousel - Large Media - Description - Text - Button.png, Phone Preview Medium Carousel.png | Gap between adjacent cards |
| `carouselPeekWidth` | 24px | All carousel screenshots | Visible portion of next card as scroll affordance |

### Suggestion Chip Tokens

| Token | Value | Source Screenshot | Notes |
|-------|-------|-------------------|-------|
| `chipHeight` | 36px | Phone Preview Rich Card 2.png ("Nearest bank location", "Call support") | Compact chip height |
| `chipRadius` | 18px | Phone Preview Rich Card 2.png | Full pill shape |
| `chipBackground` | #ffffff | Phone Preview Rich Card 2.png | White background with border |
| `chipTextColor` | #1a73e8 | Phone Preview Rich Card 2.png, Phone Preview Medium Carousel.png | Blue text matching primary color |

### Color Tokens

| Token | Value | Source Screenshot | Notes |
|-------|-------|-------------------|-------|
| `primary` | #1a73e8 | Phone Preview Medium Carousel.png ("Ideas for TanTan" chip) | Google Blue for interactive elements |
| `chatBackground` | #f0f4f9 | All Phone Examples | Light blue-gray chat area background |
| `surface` | #ffffff | All cards | Pure white card backgrounds |
| `onSurface` | #1f1f1f | Title text in all cards | Near-black for primary text |
| `onSurfaceVariant` | #5f6368 | Description text | Medium gray for secondary text |
| `outline` | #dadce0 | Button borders | Light gray for outlines |

## Assumptions

1. **Font Family**: Assumed Google Sans / Roboto stack based on Android Messages app styling. Fallback to system fonts for broad compatibility.

2. **Shadow Values**: Exact shadow values not measurable from screenshots; derived from Material Design 3 elevation level 1 guidelines.

3. **Animation Timing**: Not visible in static screenshots; using standard Material Design motion values (200ms, ease-out curve).

4. **Touch Targets**: Button heights follow Android accessibility guidelines (minimum 48dp touch target).

5. **Color Accuracy**: Colors extracted visually and may have slight variations from actual Google RCS implementation. Values aligned with Material Design 3 color system.

## Usage

Import tokens in your renderer:

```typescript
import { RCS_TOKENS, generateCssVariables } from '@/rcs/tokens';

// Use directly
const cardRadius = RCS_TOKENS.card.radius; // '12px'

// Or generate CSS variables
const cssVars = generateCssVariables();
// Inject into <style> tag or CSS file
```

The renderer should be entirely schema-driven using these tokens. Screenshots serve only as visual reference for deriving values, not as embedded assets.
