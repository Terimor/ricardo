const mix = require('laravel-mix');


mix.options({
  extractVueStyles: 'public/css/vue-styles/[name].css',
});

mix.webpackConfig({
  externals: {
    vue: 'Vue',
  }
});

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
  .js('resources/js/app.js', 'public/js')
  .js('resources/js/views/promo.js', 'public/js/views')
  .js('resources/js/views/thank-you.js', 'public/js/views')
  .js('resources/js/views/order-tracking.js', 'public/js/views')
  .sass('resources/sass/app.scss', 'public/css')
  .sass('resources/sass/views/promo.scss', 'public/css')
  .sass('resources/sass/views/contact-us.scss', 'public/css')
  .sass('resources/sass/views/static.scss', 'public/css')
  .sass('resources/sass/views/splash.scss', 'public/css')
  .sass('resources/sass/views/order-tracking.scss', 'public/css')
  .sass('resources/sass/views/index.scss', 'public/css')
  .version();
