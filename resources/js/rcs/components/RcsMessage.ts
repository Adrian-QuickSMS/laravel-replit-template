import type { RcsMessage, RcsRichCard, RcsCarousel } from '../schema';
import { renderRichCard } from './RcsRichCard';
import { renderCarousel } from './RcsCarousel';

export function renderMessage(message: RcsMessage): string {
  let contentHtml = '';

  if (message.type === 'rich_card') {
    contentHtml = renderRichCard(message.content as RcsRichCard);
  } else if (message.type === 'carousel') {
    contentHtml = renderCarousel(message.content as RcsCarousel);
  }

  return `
    <div class="rcs-message" data-message-type="${message.type}">
      ${contentHtml}
    </div>
  `;
}

export function renderTimestamp(): string {
  return `<div class="rcs-timestamp">Today</div>`;
}
