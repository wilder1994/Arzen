import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './app/**/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    safelist: [
        'bg-red-200',
        'text-red-800',
        'bg-orange-200',
        'text-orange-800',
        'bg-amber-200',
        'text-amber-900',
        'bg-green-200',
        'text-green-800',
        'bg-slate-200',
        'text-slate-700',
        'bg-yellow-200',
        'text-amber-900',
        'text-green-700',
        'border-amber-200',
        'bg-amber-50',
        'text-amber-950',
        'text-amber-900',
        'border-[#fcd34d]',
        'bg-[#fde68a]',
        'hover:bg-[#fcd34d]',
        'text-[#78350f]',
        'focus:ring-[#fbbf24]',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Mirrors the --sj-fs-* scale in resources/css/app.css.
            fontSize: {
                xs: ['0.75rem', { lineHeight: '1.1rem' }],
                sm: ['0.875rem', { lineHeight: '1.3rem' }],
                base: ['0.9375rem', { lineHeight: '1.4rem' }],
                lg: ['0.9375rem', { lineHeight: '1.4rem' }],
                xl: ['1.125rem', { lineHeight: '1.6rem' }],
                '2xl': ['1.375rem', { lineHeight: '1.75rem' }],
                '3xl': ['1.5rem', { lineHeight: '1.9rem' }],
            },
        },
    },

    plugins: [forms],
};
