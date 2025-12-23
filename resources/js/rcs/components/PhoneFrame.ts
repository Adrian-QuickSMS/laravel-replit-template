import type { RcsAgent } from '../schema';

export function renderPhoneFrame(agent: RcsAgent, messageContent: string): string {
  const verifiedBadge = agent.verified !== false 
    ? `<svg class="rcs-verified-badge" viewBox="0 0 24 24" fill="#1a73e8"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>`
    : '';

  const tagline = agent.tagline 
    ? `<span class="rcs-agent-tagline">${escapeHtml(agent.tagline)}</span>` 
    : '';

  return `
    <div class="rcs-phone-frame">
      <div class="rcs-status-bar">
        <span class="rcs-status-time">9:30</span>
        <div class="rcs-status-icons">
          <span class="rcs-status-5g">5G</span>
          <svg class="rcs-status-signal" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M2 22h20V2L2 22zm18-2H6.83L20 6.83V20z"/></svg>
          <svg class="rcs-status-battery" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z"/></svg>
        </div>
      </div>
      
      <div class="rcs-header">
        <button class="rcs-back-button">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        </button>
        <div class="rcs-agent-info">
          <div class="rcs-agent-logo-wrapper">
            <img src="${escapeHtml(agent.logo)}" alt="${escapeHtml(agent.name)}" class="rcs-agent-logo" />
            ${verifiedBadge}
          </div>
          <div class="rcs-agent-details">
            <span class="rcs-agent-name">${escapeHtml(agent.name)}</span>
            ${tagline}
          </div>
        </div>
        <div class="rcs-header-actions">
          <button class="rcs-header-btn">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
          </button>
          <button class="rcs-header-btn">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
          </button>
        </div>
      </div>
      
      <div class="rcs-chat-area">
        <div class="rcs-timestamp">Today</div>
        ${messageContent}
      </div>
      
      <div class="rcs-input-bar">
        <button class="rcs-input-action">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        </button>
        <input type="text" class="rcs-input-field" placeholder="RCS message" readonly />
        <button class="rcs-input-action">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
        </button>
        <button class="rcs-input-action">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
        </button>
        <button class="rcs-send-button">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 14c1.66 0 2.99-1.34 2.99-3L15 5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/></svg>
        </button>
      </div>
    </div>
  `;
}

function escapeHtml(text: string): string {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
