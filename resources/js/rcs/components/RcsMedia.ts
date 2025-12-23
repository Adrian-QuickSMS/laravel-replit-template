import type { RcsMedia, MediaHeight } from '../schema';
import { getMediaHeight } from '../tokens';

export function renderMedia(media: RcsMedia | undefined): string {
  if (!media || media.height === 'none') {
    return '';
  }

  const height = getMediaHeight(media.height);
  const altText = media.altText ? escapeHtml(media.altText) : 'RCS card image';

  return `
    <div class="rcs-media rcs-media--${media.height}" style="height: ${height};">
      <img 
        src="${escapeHtml(media.url)}" 
        alt="${altText}"
        class="rcs-media-image"
        loading="lazy"
      />
    </div>
  `;
}

function escapeHtml(text: string): string {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
