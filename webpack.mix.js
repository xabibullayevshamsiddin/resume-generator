const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/resume-form.js', 'public/js')
    .js('resources/js/home.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css')
    .postCss('resources/css/home.css', 'public/css')
    .postCss('resources/css/resume-form.css', 'public/css')
    .postCss('resources/css/resume-pdf.css', 'public/css');

if (mix.inProduction()) {
    mix.version();
}
