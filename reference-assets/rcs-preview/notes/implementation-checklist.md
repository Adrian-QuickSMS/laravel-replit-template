# RCS Preview Implementation Checklist

## Current State
The RCS preview uses a simplified card layout that doesn't fully match how messages appear on real devices.

## Improvements Needed

### Device Frame
- [ ] Add phone device frame around preview
- [ ] Include status bar (time, battery, signal)
- [ ] Show messaging app header (agent name, verified badge)

### Rich Card Styling
- [ ] Update media aspect ratios (16:9 default, with orientation options)
- [ ] Add proper safe zones around media
- [ ] Implement rounded corners matching real RCS cards
- [ ] Add subtle shadow/elevation to card

### Typography
- [ ] Match Google RCS font sizes (title ~16sp, description ~14sp)
- [ ] Proper line height and letter spacing
- [ ] Truncation with ellipsis for long text

### Buttons
- [ ] Primary button: filled, elevated
- [ ] Secondary button: outlined
- [ ] Tertiary button: text only
- [ ] Proper button height (48dp touch target)
- [ ] Button icon support (URL, phone, calendar icons)

### Carousel Mode
- [ ] Horizontal scroll container
- [ ] Dot pagination indicators
- [ ] Visible card edges as scroll affordance
- [ ] Smooth card transitions

### Message States
- [ ] Timestamp display
- [ ] Sent/Delivered/Read indicators
- [ ] Message bubble tail/pointer

### Colors
- [ ] Map Fillow theme colors to RCS-appropriate palette
- [ ] Ensure sufficient contrast
- [ ] Support dark mode preview option

## Priority Order
1. Device frame and header (establishes context)
2. Card styling and typography (visual accuracy)
3. Button improvements (interaction clarity)
4. Carousel mode (advanced feature)
5. Message states (polish)
