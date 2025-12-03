import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*.php',
        './app/Providers/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Urbanist', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
