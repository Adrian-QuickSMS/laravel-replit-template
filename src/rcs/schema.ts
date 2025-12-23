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

export const JSON_SCHEMA = {
  $schema: "http://json-schema.org/draft-07/schema#",
  title: "RCS Message Schema",
  type: "object",
  required: ["type", "agent", "content"],
  properties: {
    id: { type: "string" },
    type: { enum: ["rich_card", "carousel"] },
    agent: {
      type: "object",
      required: ["name", "logo"],
      properties: {
        name: { type: "string", maxLength: 100 },
        logo: { type: "string", format: "uri" },
        verified: { type: "boolean" },
        tagline: { type: "string", maxLength: 100 }
      }
    },
    content: {
      oneOf: [
        { $ref: "#/definitions/richCard" },
        { $ref: "#/definitions/carousel" }
      ]
    },
    fallbackText: { type: "string", maxLength: 3072 },
    createdAt: { type: "string", format: "date-time" },
    updatedAt: { type: "string", format: "date-time" }
  },
  definitions: {
    richCard: {
      type: "object",
      required: ["buttons"],
      properties: {
        media: { $ref: "#/definitions/media" },
        title: { type: "string", maxLength: 200 },
        description: { type: "string", maxLength: 2000 },
        buttons: {
          type: "array",
          maxItems: 4,
          items: { $ref: "#/definitions/button" }
        }
      }
    },
    carousel: {
      type: "object",
      required: ["cardWidth", "cards"],
      properties: {
        cardWidth: { enum: ["small", "medium"] },
        cards: {
          type: "array",
          minItems: 2,
          maxItems: 10,
          items: { $ref: "#/definitions/richCard" }
        }
      }
    },
    media: {
      type: "object",
      required: ["url", "mimeType", "height"],
      properties: {
        url: { type: "string", format: "uri" },
        mimeType: { enum: ["image/jpeg", "image/png", "image/gif"] },
        height: { enum: ["none", "short", "medium", "tall"] },
        thumbnailUrl: { type: "string", format: "uri" },
        altText: { type: "string", maxLength: 100 }
      }
    },
    button: {
      type: "object",
      required: ["label", "action"],
      properties: {
        label: { type: "string", maxLength: 25 },
        action: { $ref: "#/definitions/buttonAction" },
        icon: { type: "string" }
      }
    },
    buttonAction: {
      oneOf: [
        {
          type: "object",
          required: ["type", "url"],
          properties: {
            type: { const: "url" },
            url: { type: "string", format: "uri", pattern: "^https?://" }
          }
        },
        {
          type: "object",
          required: ["type", "phoneNumber"],
          properties: {
            type: { const: "dial" },
            phoneNumber: { type: "string", pattern: "^\\+?[0-9\\s\\-()]+$" }
          }
        },
        {
          type: "object",
          required: ["type", "title", "startTime", "endTime"],
          properties: {
            type: { const: "calendar" },
            title: { type: "string", maxLength: 100 },
            description: { type: "string", maxLength: 500 },
            startTime: { type: "string", format: "date-time" },
            endTime: { type: "string", format: "date-time" }
          }
        },
        {
          type: "object",
          required: ["type", "text"],
          properties: {
            type: { const: "reply" },
            text: { type: "string", maxLength: 25 },
            postbackData: { type: "string", maxLength: 2048 }
          }
        }
      ]
    }
  }
};
