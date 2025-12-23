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
