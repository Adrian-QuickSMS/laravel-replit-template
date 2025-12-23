import type { RcsRichCard } from '../schema';
import { renderMedia } from './RcsMedia';
import { renderButtons } from './RcsButton';

export function renderRichCard(card: RcsRichCard, isCarouselCard: boolean = false): string {
  const mediaHtml = renderMedia(card.media);
  const hasMedia = card.media && card.media.height !== 'none';
  
  const titleHtml = card.title 
    ? `<h3 class="rcs-card-title">${escapeHtml(card.title)}</h3>` 
    : '';
  
  const descriptionHtml = card.description 
    ? `<p class="rcs-card-description">${escapeHtml(card.description)}</p>` 
    : '';
  
  const buttonsHtml = renderButtons(card.buttons);
  
  const cardClass = isCarouselCard ? 'rcs-card rcs-carousel-card' : 'rcs-card';
  const mediaClass = hasMedia ? 'rcs-card--has-media' : 'rcs-card--no-media';

  return `
    <div class="${cardClass} ${mediaClass}">
      ${mediaHtml}
      <div class="rcs-card-content">
        ${titleHtml}
        ${descriptionHtml}
      </div>
      ${buttonsHtml}
    </div>
  `;
}

function escapeHtml(text: string): string {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
