# RCS Rich Card Design Specifications

Reference specifications from Google Business Messages and GSMA Universal Profile.

## Media Dimensions

| Orientation | Aspect Ratio | Recommended Size |
|-------------|--------------|------------------|
| Landscape   | 16:9         | 1440 x 810 px    |
| Square      | 1:1          | 1440 x 1440 px   |
| Portrait    | 3:4          | 1080 x 1440 px   |

**File Requirements:**
- Formats: JPEG, PNG, GIF
- Max file size: 250 KB (for fast loading)
- Safe zone: 10% margin from edges for critical content

## Typography

| Element      | Size  | Weight   | Line Height |
|--------------|-------|----------|-------------|
| Card Title   | 16sp  | Medium   | 1.25        |
| Description  | 14sp  | Regular  | 1.4         |
| Button Label | 14sp  | Medium   | 1.0         |
| Timestamp    | 12sp  | Regular  | 1.0         |

## Spacing

| Element           | Value    |
|-------------------|----------|
| Card padding      | 12-16 px |
| Button padding    | 12-24 px |
| Button margin     | 8 px     |
| Text block margin | 8-12 px  |
| Card corner radius| 8-12 px  |

## Button Specifications

**Dimensions:**
- Height: 48 dp (touch target)
- Min width: 64 dp
- Max width: full card width minus padding

**Styles:**
1. **Primary (Filled)**: Background color, white text, 4dp elevation
2. **Secondary (Outlined)**: Transparent, colored border, colored text
3. **Tertiary (Text)**: No background, no border, colored text

**States:**
- Default, Hover, Pressed, Disabled

## Carousel Guidelines

- Maximum 10 cards
- Card width: 85% of viewport (shows peek of next card)
- Pagination: Dot indicators below cards
- Scroll: Horizontal, snaps to card center

## Colors (Material Design 3)

| Purpose      | Light Mode | Dark Mode  |
|--------------|------------|------------|
| Primary      | #6750A4    | #D0BCFF    |
| Surface      | #FFFBFE    | #1C1B1F    |
| On Surface   | #1C1B1F    | #E6E1E5    |
| Outline      | #79747E    | #938F99    |

## Agent Header

- Agent logo: 40x40 px, rounded
- Agent name: 14sp, medium weight
- Verified badge: 16x16 px icon
- Header height: 56 dp
