import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                 'resources/js/app.js',
                // 'resources/css/certificate_template1.css',
                'resources/js/certificate.js',
                'resources/js/registration.js',
            ],
        }),
        tailwindcss(),
    ],
    base: '/unlock/',
});
