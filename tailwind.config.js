import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    important : true
    ,
    darkMode: 'class'
    ,
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            screens: {
                 // => @media (max-width: 900px) { ... }
                'mobile': {'max': '900px'},
                // => @media (max-width: 900px) { ... }
                'tab': {'min': '700px'},
                // => @media (min-width: 800px) { ... }
                'larg': {'min': '1000px'},
                // => @media (min-width: 1010px) { ... }
            }
            ,    
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        require('tailwind-scrollbar'),
        forms,
        typography,
    ],
};
