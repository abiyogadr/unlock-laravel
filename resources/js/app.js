import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

const PROMO_STORAGE_KEY = 'unlock-promo-closed-at';
const PROMO_INTERVAL_MS = 3 * 60 * 60 * 1000;
const PROMO_IMAGE_URL = '/assets/images/p/lkasd65asdui298dy.jpeg';

function shouldShowPromo() {
    try {
        const saved = localStorage.getItem(PROMO_STORAGE_KEY);

        if (!saved) {
            return true;
        }

        return Date.now() - Number(saved) >= PROMO_INTERVAL_MS;
    } catch (error) {
        return true;
    }
}

function setPromoClosedAt() {
    try {
        localStorage.setItem(PROMO_STORAGE_KEY, String(Date.now()));
    } catch (error) {
        // noop
    }
}

function createPromoPopup() {
    const overlay = document.createElement('div');
    overlay.id = 'promo-popup';
    overlay.className = 'fixed inset-0 z-[9999] hidden items-center justify-center bg-black/60 px-4 py-6';

    overlay.innerHTML = `
        <div class="relative w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="relative">
                <button type="button"
                        data-promo-close
                        class="absolute right-3 top-3 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full bg-black/20 text-white transition hover:bg-black/50 focus:outline-none focus:ring-2 focus:ring-white cursor-pointer"
                        aria-label="Tutup promo">
                    <i class="fas fa-times text-sm"></i>
                </button>
                <a href="/ecourse" class="block">
                    <img src="${PROMO_IMAGE_URL}" alt="Promo" class="h-auto w-full object-cover">
                </a>
            </div>
        </div>
    `;

    const openPopup = () => {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    };

    const closePopup = () => {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        setPromoClosedAt();
    };

    overlay.querySelectorAll('[data-promo-close]').forEach((button) => {
        button.addEventListener('click', closePopup);
    });

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            closePopup();
        }
    });

    document.body.appendChild(overlay);

    if (shouldShowPromo()) {
        window.setTimeout(openPopup, 500);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', createPromoPopup, { once: true });
} else {
    createPromoPopup();
}

new Swiper('.heroSwiper', {
    loop: true,
    autoplay: { delay: 8000 },
    pagination: { el: '.hero-pagination', clickable: true },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    spaceBetween: 30,
});
