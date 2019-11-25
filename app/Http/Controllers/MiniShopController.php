<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Setting;
use App\Models\OdinProduct;
use App\Models\AffiliateSetting;
use App\Services\AffiliateService;
use App\Services\I18nService;
use App\Services\ProductService;
use App\Services\UtilsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;


/**
 * Class MiniShopController
 * @package App\Http\Controllers
 */
class MiniShopController extends Controller
{
  /**
   * Mini-Shop View
   * @return type
   */
  public static function boot() {
    View::composer('minishop.*', function($view) {
      $req = Request();

      $lang = app()->getLocale();
      $cdn_url = UtilsService::getCdnUrl();

      $domain = Domain::getByName();
      optional($domain)->setLocalLogo();

      $aff_id = AffiliateService::getAffIdFromRequest($req);

      if ($aff_id) {                
        $affiliate = AffiliateSetting::getByHasOfferId($aff_id);
      }

      $htmlToApp = AffiliateService::getHtmlToApp($req, $affiliate ?? null);
      $i18n = (new I18nService())->loadPhrases('minishop_page');

      $settings = Setting::getValue(array(
        'sentry_dsn',
      ));

      $view->with('htmlToApp', $htmlToApp);

      // All
      $view->with('i18n', $i18n);
      $view->with('domain', $domain);
      $view->with('cdn_url', $cdn_url);

      // Layout
      View::composer('minishop.layout', function($view) use ($lang) {
        $direction = !in_array($lang, ['he', 'ar'])
          ? 'ltr'
          : 'rtl';

        $view->with('lang_locale', $lang);
        $view->with('lang_direction', $direction);
      });

      // JS Deps
      View::composer('minishop.layout.js_deps', function($view) {
        $view->with('show_deps', [
          'lato.css',
          'awesome.css',
          'bootstrap.css',
        ]);
      });

      // Google Analytics
      View::composer('minishop.scripts.sentry', function($view) use ($domain) {
        $view->with('ga_id', optional($domain)->ga_id);
      });

      // Sentry.io
      View::composer('minishop.scripts.sentry', function($view) use ($settings) {
        $view->with('sentry_dsn', $settings['sentry_dsn']);
      });

      // Header Logo
      View::composer('minishop.regions.header.logo', function($view) use ($cdn_url, $domain) {
        $view->with('domain_logo', optional($domain)->logo ?? $cdn_url . '/assets/images/minishop/domain_logo.png');
      });

      // Header Menu
      View::composer('minishop.regions.header.menu', function($view) use ($i18n) {
        $view->with('header_menu', [
          [
            'url' => '/',
            'label' => $i18n['minishop.menu.home'] ?? '',
            'active' => Route::is('minishop.home'),
          ],
          [
            'url' => '/about',
            'label' => $i18n['minishop.menu.about'] ?? '',
            'active' => Route::is('minishop.about'),
          ],
          [
            'url' => '/minishop/products',
            'label' => $i18n['minishop.menu.products'] ?? '',
            'active' => Route::is('minishop.products'),
          ],
          [
            'url' => '/contact-us',
            'label' => $i18n['minishop.menu.contact_us'] ?? '',
            'active' => Route::is('minishop.contact'),
          ],
        ]);
      });

      // Footer Menu
      View::composer('minishop.regions.footer.menu', function($view) use ($i18n) {
        $view->with('footer_menu', [
          [
            'url' => '/',
            'label' => $i18n['minishop.menu.home'] ?? '',
          ],
          [
            'url' => '/about',
            'label' => $i18n['minishop.menu.about'] ?? '',
          ],
          [
            'url' => '/minishop/products',
            'label' => $i18n['minishop.menu.products'] ?? '',
          ],
          [
            'url' => '/contact-us',
            'label' => $i18n['minishop.menu.contact_us'] ?? '',
          ],
        ]);
      });
    });
  }

  /**
   * Mini-Shop - Products
   * @param Request $request
   * @param ProductService $productService
   * @return type
   */
  public function products(Request $request, ProductService $productService) {
    //$products = ProductService::getDomainProducts() ?? [];
    $products = OdinProduct::all();

    foreach ($products as $product) {
      $product->setLocalImages();
    }

    return view(
      'minishop/pages/products/products',
      compact(
        'products',
      ),
    );
  }
}
