import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                'forest': '#1F3D2E',
                'forest-dark': '#152A20',
                'amber-warm': '#C17817',
                'amber-light': '#D89A40',
                'cream': '#FAFAF8',
                'ink': '#24352D',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                'fraunces': ['Fraunces', 'serif'],
                'inter': ['Inter', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};
