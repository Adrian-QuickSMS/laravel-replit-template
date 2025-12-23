import {
  RcsMessage,
  RcsRichCard,
  RcsCarousel,
  RcsButton,
  ButtonAction,
  RCS_CONSTRAINTS
} from './schema';

export type ValidationSeverity = 'error' | 'warning';

export interface ValidationResult {
  valid: boolean;
  errors: ValidationError[];
  warnings: ValidationError[];
}

export interface ValidationError {
  field: string;
  message: string;
  severity: ValidationSeverity;
  value?: unknown;
}

const URL_PATTERN = /^https?:\/\/[^\s/$.?#].[^\s]*$/i;
const PHONE_PATTERN = /^\+?[0-9\s\-()]{7,20}$/;
const ISO_DATE_PATTERN = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/;

function createError(field: string, message: string, value?: unknown): ValidationError {
  return { field, message, severity: 'error', value };
}

function createWarning(field: string, message: string, value?: unknown): ValidationError {
  return { field, message, severity: 'warning', value };
}

export function validateUrl(url: string, field: string): ValidationError | null {
  if (!url || typeof url !== 'string') {
    return createError(field, 'URL is required');
  }
  if (!URL_PATTERN.test(url)) {
    return createError(field, 'URL must be a valid http:// or https:// URL', url);
  }
  return null;
}

export function validatePhoneNumber(phone: string, field: string): ValidationError | null {
  if (!phone || typeof phone !== 'string') {
    return createError(field, 'Phone number is required');
  }
  const cleaned = phone.replace(/[\s\-()]/g, '');
  if (!PHONE_PATTERN.test(phone) || cleaned.length < 7) {
    return createError(field, 'Invalid phone number format. Use international format like +44 123 456 7890', phone);
  }
  return null;
}

export function validateButtonAction(action: ButtonAction, field: string): ValidationError[] {
  const errors: ValidationError[] = [];

  if (!action || !action.type) {
    errors.push(createError(field, 'Button action is required'));
    return errors;
  }

  switch (action.type) {
    case 'url':
      if (!action.url) {
        errors.push(createError(`${field}.url`, 'URL is required for URL button'));
      } else {
        const urlError = validateUrl(action.url, `${field}.url`);
        if (urlError) errors.push(urlError);
      }
      break;

    case 'dial':
      if (!action.phoneNumber) {
        errors.push(createError(`${field}.phoneNumber`, 'Phone number is required for dial button'));
      } else {
        const phoneError = validatePhoneNumber(action.phoneNumber, `${field}.phoneNumber`);
        if (phoneError) errors.push(phoneError);
      }
      break;

    case 'calendar':
      if (!action.title) {
        errors.push(createError(`${field}.title`, 'Title is required for calendar event'));
      }
      if (!action.startTime) {
        errors.push(createError(`${field}.startTime`, 'Start time is required for calendar event'));
      } else if (!ISO_DATE_PATTERN.test(action.startTime)) {
        errors.push(createError(`${field}.startTime`, 'Start time must be ISO 8601 format', action.startTime));
      }
      if (!action.endTime) {
        errors.push(createError(`${field}.endTime`, 'End time is required for calendar event'));
      } else if (!ISO_DATE_PATTERN.test(action.endTime)) {
        errors.push(createError(`${field}.endTime`, 'End time must be ISO 8601 format', action.endTime));
      }
      if (action.startTime && action.endTime) {
        const start = new Date(action.startTime);
        const end = new Date(action.endTime);
        if (end <= start) {
          errors.push(createError(`${field}.endTime`, 'End time must be after start time'));
        }
      }
      break;

    case 'reply':
      if (!action.text) {
        errors.push(createError(`${field}.text`, 'Reply text is required'));
      } else if (action.text.length > RCS_CONSTRAINTS.maxButtonLabelLength) {
        errors.push(createError(`${field}.text`, `Reply text exceeds ${RCS_CONSTRAINTS.maxButtonLabelLength} characters`, action.text.length));
      }
      break;

    default:
      errors.push(createError(field, `Unknown button action type: ${(action as ButtonAction).type}`));
  }

  return errors;
}

export function validateButton(button: RcsButton, field: string): ValidationError[] {
  const errors: ValidationError[] = [];

  if (!button.label) {
    errors.push(createError(`${field}.label`, 'Button label is required'));
  } else if (button.label.length > RCS_CONSTRAINTS.maxButtonLabelLength) {
    errors.push(createError(
      `${field}.label`,
      `Button label exceeds ${RCS_CONSTRAINTS.maxButtonLabelLength} characters`,
      button.label.length
    ));
  }

  errors.push(...validateButtonAction(button.action, `${field}.action`));

  return errors;
}

export function validateRichCard(card: RcsRichCard, field: string): ValidationError[] {
  const errors: ValidationError[] = [];

  if (!card.title && !card.description && !card.media) {
    errors.push(createError(field, 'Rich card must have at least a title, description, or media'));
  }

  if (card.title && card.title.length > RCS_CONSTRAINTS.maxTitleLength) {
    errors.push(createError(
      `${field}.title`,
      `Title exceeds ${RCS_CONSTRAINTS.maxTitleLength} characters`,
      card.title.length
    ));
  }

  if (card.description && card.description.length > RCS_CONSTRAINTS.maxDescriptionLength) {
    errors.push(createError(
      `${field}.description`,
      `Description exceeds ${RCS_CONSTRAINTS.maxDescriptionLength} characters`,
      card.description.length
    ));
  }

  if (!card.buttons || !Array.isArray(card.buttons)) {
    errors.push(createError(`${field}.buttons`, 'Buttons array is required'));
  } else {
    if (card.buttons.length > RCS_CONSTRAINTS.maxButtonsPerCard) {
      errors.push(createError(
        `${field}.buttons`,
        `Too many buttons. Maximum is ${RCS_CONSTRAINTS.maxButtonsPerCard}, got ${card.buttons.length}`,
        card.buttons.length
      ));
    }

    card.buttons.forEach((button, index) => {
      errors.push(...validateButton(button, `${field}.buttons[${index}]`));
    });
  }

  if (card.media) {
    if (!card.media.url) {
      errors.push(createError(`${field}.media.url`, 'Media URL is required'));
    } else {
      const urlError = validateUrl(card.media.url, `${field}.media.url`);
      if (urlError) errors.push(urlError);
    }

    if (!card.media.mimeType) {
      errors.push(createError(`${field}.media.mimeType`, 'Media MIME type is required'));
    } else if (!RCS_CONSTRAINTS.allowedMediaTypes.includes(card.media.mimeType)) {
      errors.push(createError(
        `${field}.media.mimeType`,
        `Invalid media type. Allowed: ${RCS_CONSTRAINTS.allowedMediaTypes.join(', ')}`,
        card.media.mimeType
      ));
    }

    if (!card.media.height || !['none', 'short', 'medium', 'tall'].includes(card.media.height)) {
      errors.push(createError(`${field}.media.height`, 'Valid media height is required (none, short, medium, tall)'));
    }
  }

  return errors;
}

export function validateCarousel(carousel: RcsCarousel, field: string): ValidationError[] {
  const errors: ValidationError[] = [];

  if (!carousel.cardWidth || !['small', 'medium'].includes(carousel.cardWidth)) {
    errors.push(createError(`${field}.cardWidth`, 'Card width must be "small" or "medium"'));
  }

  if (!carousel.cards || !Array.isArray(carousel.cards)) {
    errors.push(createError(`${field}.cards`, 'Cards array is required'));
    return errors;
  }

  if (carousel.cards.length < 2) {
    errors.push(createError(`${field}.cards`, 'Carousel must have at least 2 cards', carousel.cards.length));
  }

  if (carousel.cards.length > RCS_CONSTRAINTS.maxCarouselCards) {
    errors.push(createError(
      `${field}.cards`,
      `Too many cards. Maximum is ${RCS_CONSTRAINTS.maxCarouselCards}, got ${carousel.cards.length}`,
      carousel.cards.length
    ));
  }

  carousel.cards.forEach((card, index) => {
    errors.push(...validateRichCard(card, `${field}.cards[${index}]`));
  });

  return errors;
}

export function validateAgent(agent: RcsMessage['agent'], field: string): ValidationError[] {
  const errors: ValidationError[] = [];

  if (!agent) {
    errors.push(createError(field, 'Agent information is required'));
    return errors;
  }

  if (!agent.name) {
    errors.push(createError(`${field}.name`, 'Agent name is required'));
  } else if (agent.name.length > 100) {
    errors.push(createError(`${field}.name`, 'Agent name exceeds 100 characters', agent.name.length));
  }

  if (!agent.logo) {
    errors.push(createError(`${field}.logo`, 'Agent logo URL is required'));
  } else {
    const logoError = validateUrl(agent.logo, `${field}.logo`);
    if (logoError) errors.push(logoError);
  }

  if (agent.tagline && agent.tagline.length > 100) {
    errors.push(createError(`${field}.tagline`, 'Agent tagline exceeds 100 characters', agent.tagline.length));
  }

  return errors;
}

export function validateRcsMessage(message: RcsMessage): ValidationResult {
  const errors: ValidationError[] = [];
  const warnings: ValidationError[] = [];

  if (!message) {
    return {
      valid: false,
      errors: [createError('message', 'Message is required')],
      warnings: []
    };
  }

  if (!message.type || !['rich_card', 'carousel'].includes(message.type)) {
    errors.push(createError('type', 'Message type must be "rich_card" or "carousel"'));
  }

  errors.push(...validateAgent(message.agent, 'agent'));

  if (!message.content) {
    errors.push(createError('content', 'Message content is required'));
  } else if (message.type === 'rich_card') {
    errors.push(...validateRichCard(message.content as RcsRichCard, 'content'));
  } else if (message.type === 'carousel') {
    errors.push(...validateCarousel(message.content as RcsCarousel, 'content'));
  }

  if (message.fallbackText && message.fallbackText.length > RCS_CONSTRAINTS.maxFallbackTextLength) {
    warnings.push(createWarning(
      'fallbackText',
      `Fallback text exceeds ${RCS_CONSTRAINTS.maxFallbackTextLength} characters and may be truncated`,
      message.fallbackText.length
    ));
  }

  return {
    valid: errors.length === 0,
    errors,
    warnings
  };
}

export function formatValidationErrors(result: ValidationResult): string {
  const lines: string[] = [];

  if (result.errors.length > 0) {
    lines.push('Errors:');
    result.errors.forEach(err => {
      lines.push(`  - [${err.field}] ${err.message}`);
    });
  }

  if (result.warnings.length > 0) {
    lines.push('Warnings:');
    result.warnings.forEach(warn => {
      lines.push(`  - [${warn.field}] ${warn.message}`);
    });
  }

  return lines.join('\n');
}
