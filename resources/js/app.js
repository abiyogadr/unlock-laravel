import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

new Swiper('.heroSwiper', {
    loop: true,
    autoplay: { delay: 62000 },
    pagination: { el: '.hero-pagination', clickable: true },
    spaceBetween: 30,
});
