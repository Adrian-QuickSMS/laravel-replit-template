export type MediaHeight = 'none' | 'short' | 'medium' | 'tall';

export type ButtonActionType = 'url' | 'dial' | 'calendar' | 'reply';

export interface UrlAction {
  type: 'url';
  url: string;
}

export interface DialAction {
  type: 'dial';
  phoneNumber: string;
}

export interface CalendarAction {
  type: 'calendar';
  title: string;
  description?: string;
  startTime: string;
  endTime: string;
}

export interface ReplyAction {
  type: 'reply';
  text: string;
  postbackData?: string;
}

export type ButtonAction = UrlAction | DialAction | CalendarAction | ReplyAction;

export interface RcsButton {
  label: string;
  action: ButtonAction;
  icon?: string;
}

export interface RcsMedia {
  url: string;
  mimeType: 'image/jpeg' | 'image/png' | 'image/gif';
  height: MediaHeight;
  thumbnailUrl?: string;
  altText?: string;
}

export interface RcsRichCard {
  media?: RcsMedia;
  title?: string;
  description?: string;
  buttons: RcsButton[];
}

export interface RcsCarousel {
  cardWidth: 'small' | 'medium';
  cards: RcsRichCard[];
}

export interface RcsAgent {
  name: string;
  logo: string;
  verified?: boolean;
  tagline?: string;
}

export interface RcsMessage {
  id?: string;
  type: 'rich_card' | 'carousel';
  agent: RcsAgent;
  content: RcsRichCard | RcsCarousel;
  fallbackText?: string;
  createdAt?: string;
  updatedAt?: string;
}

export interface RcsConstraints {
  maxCarouselCards: number;
  maxButtonsPerCard: number;
  maxTitleLength: number;
  maxDescriptionLength: number;
  maxBodyLength: number;
  maxButtonLabelLength: number;
  maxFallbackTextLength: number;
  allowedMediaTypes: string[];
  maxMediaSizeBytes: number;
}

export const RCS_CONSTRAINTS: RcsConstraints = {
  maxCarouselCards: 10,
  maxButtonsPerCard: 4,
  maxTitleLength: 200,
  maxDescriptionLength: 2000,
  maxBodyLength: 2000,
  maxButtonLabelLength: 25,
  maxFallbackTextLength: 3072,
  allowedMediaTypes: ['image/jpeg', 'image/png', 'image/gif'],
  maxMediaSizeBytes: 256000
};
