export interface RcsDesignTokens {
  card: {
    radius: string;
    radiusTop: string;
    padding: string;
    paddingCompact: string;
    shadow: string;
    background: string;
    maxWidth: string;
  };
  
  carousel: {
    cardWidth: string;
    cardWidthSmall: string;
    gap: string;
    peekWidth: string;
  };
  
  header: {
    height: string;
    logoSize: string;
    logoBorderRadius: string;
    verifiedBadgeSize: string;
    padding: string;
  };
  
  typography: {
    titleSize: string;
    titleWeight: string;
    titleLineHeight: string;
    titleColor: string;
    bodySize: string;
    bodyWeight: string;
    bodyLineHeight: string;
    bodyColor: string;
    timestampSize: string;
    timestampColor: string;
    fontFamily: string;
  };
  
  button: {
    height: string;
    minHeight: string;
    radius: string;
    paddingX: string;
    paddingY: string;
    gap: string;
    iconSize: string;
    fontSize: string;
    fontWeight: string;
    background: string;
    border: string;
    textColor: string;
    hoverBackground: string;
  };
  
  suggestionChip: {
    height: string;
    radius: string;
    paddingX: string;
    fontSize: string;
    gap: string;
    background: string;
    activeBackground: string;
    textColor: string;
    activeTextColor: string;
  };
  
  media: {
    shortHeight: string;
    mediumHeight: string;
    tallHeight: string;
    borderRadiusTop: string;
  };
  
  spacing: {
    cardGap: string;
    sectionGap: string;
    buttonGap: string;
    contentPadding: string;
  };
  
  colors: {
    primary: string;
    primaryLight: string;
    surface: string;
    surfaceVariant: string;
    onSurface: string;
    onSurfaceVariant: string;
    outline: string;
    outlineVariant: string;
    chatBackground: string;
    messageBubble: string;
  };
  
  animation: {
    duration: string;
    easing: string;
  };
}

export const RCS_TOKENS: RcsDesignTokens = {
  card: {
    radius: '12px',
    radiusTop: '12px 12px 0 0',
    padding: '16px',
    paddingCompact: '12px',
    shadow: '0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04)',
    background: '#ffffff',
    maxWidth: '320px'
  },
  
  carousel: {
    cardWidth: '256px',
    cardWidthSmall: '200px',
    gap: '8px',
    peekWidth: '24px'
  },
  
  header: {
    height: '56px',
    logoSize: '40px',
    logoBorderRadius: '50%',
    verifiedBadgeSize: '16px',
    padding: '16px'
  },
  
  typography: {
    titleSize: '16px',
    titleWeight: '500',
    titleLineHeight: '1.25',
    titleColor: '#1f1f1f',
    bodySize: '14px',
    bodyWeight: '400',
    bodyLineHeight: '1.43',
    bodyColor: '#5f6368',
    timestampSize: '12px',
    timestampColor: '#9aa0a6',
    fontFamily: "'Google Sans', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif"
  },
  
  button: {
    height: '48px',
    minHeight: '40px',
    radius: '24px',
    paddingX: '24px',
    paddingY: '12px',
    gap: '8px',
    iconSize: '20px',
    fontSize: '14px',
    fontWeight: '500',
    background: '#f8f9fa',
    border: '1px solid #e8eaed',
    textColor: '#1f1f1f',
    hoverBackground: '#f1f3f4'
  },
  
  suggestionChip: {
    height: '36px',
    radius: '18px',
    paddingX: '16px',
    fontSize: '14px',
    gap: '8px',
    background: '#ffffff',
    activeBackground: '#e8f0fe',
    textColor: '#1a73e8',
    activeTextColor: '#1a73e8'
  },
  
  media: {
    shortHeight: '112px',
    mediumHeight: '168px',
    tallHeight: '264px',
    borderRadiusTop: '12px 12px 0 0'
  },
  
  spacing: {
    cardGap: '8px',
    sectionGap: '12px',
    buttonGap: '8px',
    contentPadding: '16px'
  },
  
  colors: {
    primary: '#1a73e8',
    primaryLight: '#e8f0fe',
    surface: '#ffffff',
    surfaceVariant: '#f8f9fa',
    onSurface: '#1f1f1f',
    onSurfaceVariant: '#5f6368',
    outline: '#dadce0',
    outlineVariant: '#e8eaed',
    chatBackground: '#f0f4f9',
    messageBubble: '#ffffff'
  },
  
  animation: {
    duration: '200ms',
    easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
  }
};

export function getMediaHeight(height: 'none' | 'short' | 'medium' | 'tall'): string {
  switch (height) {
    case 'short': return RCS_TOKENS.media.shortHeight;
    case 'medium': return RCS_TOKENS.media.mediumHeight;
    case 'tall': return RCS_TOKENS.media.tallHeight;
    default: return '0';
  }
}

export function generateCssVariables(tokens: RcsDesignTokens = RCS_TOKENS): string {
  return `
:root {
  /* Card */
  --rcs-card-radius: ${tokens.card.radius};
  --rcs-card-padding: ${tokens.card.padding};
  --rcs-card-padding-compact: ${tokens.card.paddingCompact};
  --rcs-card-shadow: ${tokens.card.shadow};
  --rcs-card-background: ${tokens.card.background};
  --rcs-card-max-width: ${tokens.card.maxWidth};

  /* Carousel */
  --rcs-carousel-card-width: ${tokens.carousel.cardWidth};
  --rcs-carousel-card-width-small: ${tokens.carousel.cardWidthSmall};
  --rcs-carousel-gap: ${tokens.carousel.gap};
  --rcs-carousel-peek: ${tokens.carousel.peekWidth};

  /* Header */
  --rcs-header-height: ${tokens.header.height};
  --rcs-header-logo-size: ${tokens.header.logoSize};
  --rcs-header-badge-size: ${tokens.header.verifiedBadgeSize};

  /* Typography */
  --rcs-font-family: ${tokens.typography.fontFamily};
  --rcs-title-size: ${tokens.typography.titleSize};
  --rcs-title-weight: ${tokens.typography.titleWeight};
  --rcs-title-color: ${tokens.typography.titleColor};
  --rcs-body-size: ${tokens.typography.bodySize};
  --rcs-body-weight: ${tokens.typography.bodyWeight};
  --rcs-body-color: ${tokens.typography.bodyColor};
  --rcs-timestamp-size: ${tokens.typography.timestampSize};
  --rcs-timestamp-color: ${tokens.typography.timestampColor};

  /* Buttons */
  --rcs-button-height: ${tokens.button.height};
  --rcs-button-radius: ${tokens.button.radius};
  --rcs-button-padding-x: ${tokens.button.paddingX};
  --rcs-button-gap: ${tokens.button.gap};
  --rcs-button-icon-size: ${tokens.button.iconSize};
  --rcs-button-font-size: ${tokens.button.fontSize};
  --rcs-button-background: ${tokens.button.background};
  --rcs-button-border: ${tokens.button.border};
  --rcs-button-text-color: ${tokens.button.textColor};

  /* Suggestion Chips */
  --rcs-chip-height: ${tokens.suggestionChip.height};
  --rcs-chip-radius: ${tokens.suggestionChip.radius};
  --rcs-chip-text-color: ${tokens.suggestionChip.textColor};

  /* Media Heights */
  --rcs-media-short: ${tokens.media.shortHeight};
  --rcs-media-medium: ${tokens.media.mediumHeight};
  --rcs-media-tall: ${tokens.media.tallHeight};

  /* Spacing */
  --rcs-card-gap: ${tokens.spacing.cardGap};
  --rcs-section-gap: ${tokens.spacing.sectionGap};
  --rcs-button-gap: ${tokens.spacing.buttonGap};

  /* Colors */
  --rcs-color-primary: ${tokens.colors.primary};
  --rcs-color-primary-light: ${tokens.colors.primaryLight};
  --rcs-color-surface: ${tokens.colors.surface};
  --rcs-color-surface-variant: ${tokens.colors.surfaceVariant};
  --rcs-color-on-surface: ${tokens.colors.onSurface};
  --rcs-color-on-surface-variant: ${tokens.colors.onSurfaceVariant};
  --rcs-color-outline: ${tokens.colors.outline};
  --rcs-color-chat-bg: ${tokens.colors.chatBackground};

  /* Animation */
  --rcs-animation-duration: ${tokens.animation.duration};
  --rcs-animation-easing: ${tokens.animation.easing};
}
  `.trim();
}
