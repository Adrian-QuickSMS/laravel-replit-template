import type { RcsMessage, RcsRichCard, RcsCarousel } from './schema';
import { validateRcsMessage, ValidationResult } from './validate';
import { renderMessage } from './components/RcsMessage';
import { renderPhoneFrame } from './components/PhoneFrame';

export interface RcsPreviewState {
  currentPayload: RcsMessage | null;
  selectedExample: string;
  validationResult: ValidationResult | null;
  isLoading: boolean;
}

export const samplePayloads: Record<string, RcsMessage> = {
  'rich-card-short': {
    type: 'rich_card',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true,
      tagline: 'Fast messaging for everyone'
    },
    content: {
      media: {
        url: 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=400&h=200&fit=crop',
        mimeType: 'image/jpeg',
        height: 'short',
        altText: 'Running shoes'
      },
      title: 'New arrivals just dropped!',
      description: 'Check out our latest collection of premium running shoes.',
      buttons: [
        { label: 'Shop now', action: { type: 'url', url: 'https://example.com/shop' } },
        { label: 'Call us', action: { type: 'dial', phoneNumber: '+44 20 1234 5678' } }
      ]
    } as RcsRichCard
  },
  'rich-card-medium': {
    type: 'rich_card',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true
    },
    content: {
      media: {
        url: 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=400&h=300&fit=crop',
        mimeType: 'image/jpeg',
        height: 'medium',
        altText: 'Banking app'
      },
      title: 'Secure mobile banking',
      description: 'Manage your accounts securely from anywhere. Check balances, make payments, and more.',
      buttons: [
        { label: 'Check balance', action: { type: 'url', url: 'https://example.com/balance' } },
        { label: 'View activity', action: { type: 'url', url: 'https://example.com/activity' } },
        { label: 'Call support', action: { type: 'dial', phoneNumber: '+44 800 123 456' } }
      ]
    } as RcsRichCard
  },
  'rich-card-tall': {
    type: 'rich_card',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true
    },
    content: {
      media: {
        url: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=400&fit=crop',
        mimeType: 'image/jpeg',
        height: 'tall',
        altText: 'Mountain landscape'
      },
      title: 'Adventure awaits',
      buttons: [
        { label: 'Book now', action: { type: 'url', url: 'https://example.com/book' } }
      ]
    } as RcsRichCard
  },
  'rich-card-no-media': {
    type: 'rich_card',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true
    },
    content: {
      title: 'Your order has shipped!',
      description: 'Your package is on its way. Track your delivery to see when it will arrive.',
      buttons: [
        { label: 'Track package', action: { type: 'url', url: 'https://example.com/track' } }
      ]
    } as RcsRichCard
  },
  'carousel-medium': {
    type: 'carousel',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true,
      tagline: 'Your travel partner'
    },
    content: {
      cardWidth: 'medium',
      cards: [
        {
          media: {
            url: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=300&h=200&fit=crop',
            mimeType: 'image/jpeg',
            height: 'medium'
          },
          title: 'Beach Paradise',
          description: 'Relax on pristine beaches. From $299/night.',
          buttons: [
            { label: 'Book now', action: { type: 'url', url: 'https://example.com/beach' } }
          ]
        },
        {
          media: {
            url: 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=300&h=200&fit=crop',
            mimeType: 'image/jpeg',
            height: 'medium'
          },
          title: 'Mountain Retreat',
          description: 'Escape to the mountains. From $199/night.',
          buttons: [
            { label: 'Book now', action: { type: 'url', url: 'https://example.com/mountain' } }
          ]
        },
        {
          media: {
            url: 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=300&h=200&fit=crop',
            mimeType: 'image/jpeg',
            height: 'medium'
          },
          title: 'City Explorer',
          description: 'Discover urban adventures. From $149/night.',
          buttons: [
            { label: 'Book now', action: { type: 'url', url: 'https://example.com/city' } }
          ]
        }
      ]
    } as RcsCarousel
  },
  'carousel-small': {
    type: 'carousel',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true
    },
    content: {
      cardWidth: 'small',
      cards: [
        {
          media: {
            url: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&h=150&fit=crop',
            mimeType: 'image/jpeg',
            height: 'short'
          },
          title: 'Running Shoes',
          description: '$129',
          buttons: [
            { label: 'Buy', action: { type: 'url', url: 'https://example.com/shoes1' } }
          ]
        },
        {
          media: {
            url: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=200&h=150&fit=crop',
            mimeType: 'image/jpeg',
            height: 'short'
          },
          title: 'Casual Sneakers',
          description: '$89',
          buttons: [
            { label: 'Buy', action: { type: 'url', url: 'https://example.com/shoes2' } }
          ]
        },
        {
          media: {
            url: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=200&h=150&fit=crop',
            mimeType: 'image/jpeg',
            height: 'short'
          },
          title: 'Sport Trainers',
          description: '$159',
          buttons: [
            { label: 'Buy', action: { type: 'url', url: 'https://example.com/shoes3' } }
          ]
        }
      ]
    } as RcsCarousel
  }
};

export function createRcsPreviewController() {
  return {
    currentPayload: null as RcsMessage | null,
    selectedExample: 'rich-card-short',
    validationResult: null as ValidationResult | null,
    isLoading: false,
    examples: Object.keys(samplePayloads),

    init() {
      this.loadExample(this.selectedExample);
    },

    loadExample(exampleId: string) {
      this.selectedExample = exampleId;
      const payload = samplePayloads[exampleId];
      if (payload) {
        this.currentPayload = payload;
        this.validate();
        this.render();
      }
    },

    validate() {
      if (this.currentPayload) {
        this.validationResult = validateRcsMessage(this.currentPayload);
      }
    },

    render() {
      const container = document.getElementById('rcs-preview-container');
      if (!container || !this.currentPayload) return;

      const messageHtml = renderMessage(this.currentPayload);
      const frameHtml = renderPhoneFrame(this.currentPayload.agent, messageHtml);
      container.innerHTML = frameHtml;

      this.initCarouselBehavior();
    },

    initCarouselBehavior() {
      const carousel = document.querySelector('.rcs-carousel-track') as HTMLElement;
      if (!carousel) return;

      const dots = document.querySelectorAll('.rcs-carousel-dot');
      let currentIndex = 0;

      carousel.addEventListener('scroll', () => {
        const scrollLeft = carousel.scrollLeft;
        const itemWidth = carousel.firstElementChild?.clientWidth || 256;
        const gap = 8;
        currentIndex = Math.round(scrollLeft / (itemWidth + gap));

        dots.forEach((dot, i) => {
          dot.classList.toggle('active', i === currentIndex);
        });
      });

      dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
          const itemWidth = carousel.firstElementChild?.clientWidth || 256;
          const gap = 8;
          carousel.scrollTo({
            left: i * (itemWidth + gap),
            behavior: 'smooth'
          });
        });
      });
    },

    getExampleLabel(id: string): string {
      const labels: Record<string, string> = {
        'rich-card-short': 'Rich Card - Short Media',
        'rich-card-medium': 'Rich Card - Medium Media',
        'rich-card-tall': 'Rich Card - Tall Media',
        'rich-card-no-media': 'Rich Card - No Media',
        'carousel-medium': 'Carousel - Medium Cards',
        'carousel-small': 'Carousel - Small Cards'
      };
      return labels[id] || id;
    },

    hasErrors(): boolean {
      return this.validationResult?.errors?.length > 0;
    },

    hasWarnings(): boolean {
      return this.validationResult?.warnings?.length > 0;
    }
  };
}

declare global {
  interface Window {
    createRcsPreviewController: typeof createRcsPreviewController;
  }
}

window.createRcsPreviewController = createRcsPreviewController;
