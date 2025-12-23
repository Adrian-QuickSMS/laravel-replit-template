import type { RcsCarousel } from '../schema';
import { renderRichCard } from './RcsRichCard';
import { RCS_TOKENS } from '../tokens';

export function renderCarousel(carousel: RcsCarousel): string {
  const cardWidth = carousel.cardWidth === 'small' 
    ? RCS_TOKENS.carousel.cardWidthSmall 
    : RCS_TOKENS.carousel.cardWidth;

  const cardsHtml = carousel.cards
    .map(card => `
      <div class="rcs-carousel-item" style="min-width: ${cardWidth}; max-width: ${cardWidth};">
        ${renderRichCard(card, true)}
      </div>
    `)
    .join('');

  return `
    <div class="rcs-carousel" data-card-width="${carousel.cardWidth}">
      <div class="rcs-carousel-track">
        ${cardsHtml}
      </div>
      <div class="rcs-carousel-indicators">
        ${carousel.cards.map((_, i) => `
          <button class="rcs-carousel-dot ${i === 0 ? 'active' : ''}" data-index="${i}"></button>
        `).join('')}
      </div>
    </div>
  `;
}
