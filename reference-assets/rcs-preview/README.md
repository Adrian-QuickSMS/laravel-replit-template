# RCS Preview Reference Assets

This folder contains design reference materials to improve the RCS message preview appearance in the Send Message page.

## Folder Structure

```
rcs-preview/
├── docs/           - Official design specifications and guidelines
├── screenshots/    - Real device RCS message screenshots
│   ├── pixel/      - Google Pixel device screenshots
│   ├── samsung/    - Samsung Messages app screenshots
│   └── iphone/     - iOS RCS screenshots (if available)
├── templates/      - Figma exports, SVG frames, UI components
└── notes/          - Implementation checklists and gap analyses
```

## What to Collect

### docs/
- Google Business Messages rich card documentation
- GSMA Universal Profile RCS Rich Cards specifications
- Material Design 3 visual tokens reference
- Typography and spacing guidelines

### screenshots/
Capture real RCS messages showing:
- Single rich cards with media
- Carousel layouts (2-10 cards)
- Button styles (primary, secondary, ghost)
- Suggestion chips
- Agent headers and branding
- Delivered/read states

For each screenshot, note:
- Device model and OS version
- Messaging app used
- Screen resolution

### templates/
- Device frame mockups (SVG/PNG)
- Button component assets
- Icon sets used in RCS
- Color palette swatches

### notes/
- Implementation checklists
- Gap analysis comparing current vs. target design
- Open questions for design decisions

## Key Design Elements to Reference

1. **Device Chrome**: Agent header with logo, name, verified badge
2. **Media Ratios**: 16:9 landscape, 1:1 square, 3:4 portrait
3. **Typography**: Font sizes, weights, line heights
4. **Spacing**: Card padding (12-16px typical), button margins
5. **Button States**: Elevated primary, outlined secondary, text-only tertiary
6. **Carousel Controls**: Dot indicators, swipe affordances
7. **Message States**: Sent, delivered, read indicators
