const mix = require('laravel-mix');
const rimraf = require('rimraf');


if (mix.inProduction()) {
  rimraf.sync('public/assets');
}

mix.options({
  extractVueStyles: 'public[name].vue.css',
  processCssUrls: false,
});

mix.webpackConfig({
  externals: {
    vue: 'Vue',
  },
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
  .js('resources/js/app.js', 'public/assets/js')
  .js('resources/js/static.js', 'public/assets/js')
  .js('resources/js/views/promo.js', 'public/assets/js/views')
  .js('resources/js/views/thank-you.js', 'public/assets/js/views')
  .js('resources/js/views/order-tracking.js', 'public/assets/js/views')
  .js('resources/js/minishop/pages/home.js', 'public/assets/js/minishop')
  .js('resources/js/minishop/pages/product.js', 'public/assets/js/minishop')
  .js('resources/js/new/pages/checkout/templates/fmc5.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/amc8.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/amc81.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/hp01.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/thor-power.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/hydrolinx.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/slimeazy.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/vc1.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/vc2.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/checkout/templates/nmc9.js', 'public/assets/js/new/pages/checkout/templates')
  .js('resources/js/new/pages/vrtl/upsells.js', 'public/assets/js/new/pages/vrtl')
  .js('resources/js/new/pages/vrtl/thankyou.js', 'public/assets/js/new/pages/vrtl')
  .js('resources/js/new/pages/vrtl/download.js', 'public/assets/js/new/pages/vrtl')
  .js('resources/js/new/pages/vrtl/splash.js', 'public/assets/js/new/pages/vrtl')
  .sass('resources/sass/app.scss', 'public/assets/css')
  .sass('resources/sass/views/promo.scss', 'public/assets/css')
  .sass('resources/sass/views/contact-us.scss', 'public/assets/css')
  .sass('resources/sass/views/support.scss', 'public/assets/css')
  .sass('resources/sass/views/static.scss', 'public/assets/css')
  .sass('resources/sass/views/splash.scss', 'public/assets/css')
  .sass('resources/sass/views/uppsells.scss', 'public/assets/css')
  .sass('resources/sass/views/thank-you.scss', 'public/assets/css')
  .sass('resources/sass/views/returns.scss', 'public/assets/css')
  .sass('resources/sass/views/order-tracking.scss', 'public/assets/css')
  .sass('resources/sass/views/index.scss', 'public/assets/css')
  .sass('resources/sass/new/pages/checkout/templates/fmc5.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/amc8.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/amc81.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/hp01.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/thor-power.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/hydrolinx.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/slimeazy.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/vc1.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/vc2.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/checkout/templates/nmc9.scss', 'public/assets/css/new/pages/checkout/templates')
  .sass('resources/sass/new/pages/vrtl/upsells.scss', 'public/assets/css/new/pages/vrtl')
  .sass('resources/sass/new/pages/vrtl/thankyou.scss', 'public/assets/css/new/pages/vrtl')
  .sass('resources/sass/new/pages/vrtl/download.scss', 'public/assets/css/new/pages/vrtl')
  .sass('resources/sass/new/pages/vrtl/splash.scss', 'public/assets/css/new/pages/vrtl')
  .sass('resources/sass/minishop/pages/home.scss', 'public/assets/css/minishop')
  .sass('resources/sass/minishop/pages/product.scss', 'public/assets/css/minishop')
  .copyDirectory('resources/images', 'public/assets/images')
  .copy('resources/scripts/bioep.min.js', 'public/assets/scripts');

if (mix.inProduction()) {
  mix.sourceMaps();
  mix.version();
}
