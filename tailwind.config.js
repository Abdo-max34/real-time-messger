import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
        screens:{
            xs:"420px",
            sm:"680px",
            md:"780px",
            lg:"1024px",
            xl:"1280px",
            "2xl":"1536px",
        }
    },

    plugins: [require('daisyui'), forms],
    // daisyUI config (optional - here are the default values)
    daisyui: {
        themes: ['dark'],
    },
};
