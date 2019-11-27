<?php

namespace App\Services;
use App\Helpers\I18n;
use Illuminate\Support\Facades\Route;


/**
 * Mini shop service class
 */
class MiniShopService
{
    public static $headerLogoDefaultPath = '/assets/images/minishop/domain_logo.png';
    
    public static $headerMenu = [
      [
        'url' => '/',
        'phrase' => 'minishop.menu.home',
        'active' => 'home',
      ],
      [
        'url' => '/about',
        'phrase' => 'minishop.menu.about',
        'active' => 'minishop.about',
      ],
      [
        'url' => '/contact-us',
        'phrase' => 'minishop.menu.contact_us',
        'active' => 'minishop.contact',
        'icon' => 'contact.png',
      ],
      [
        'class' => 'call-us',
        'phrase' => 'minishop.menu.call_us',
        'icon' => 'call.png',
        'submenu' => [
          [
            'url' => 'tel:8887438103',
            'label' => '(&#127482;&#127480;/&#127464;&#127462;) (888) 743-8103',
          ],
          [
            'url' => 'tel:+441782454716',
            'label' => '(&#127758;) +441782454716',
          ],
        ],
      ],
    ];
    
    public static $footerMenu = [
      [
        'url' => '/contact-us',
        'phrase' => 'minishop.menu.contact_us',
      ],
      [
        'url' => '/terms',
        'phrase' => 'minishop.menu.terms',
      ],
      [
        'url' => '/privacy',
        'phrase' => 'minishop.menu.privacy',
      ],
      [
        'url' => 'https://www.h8m8.com',
        'phrase' => 'minishop.menu.affiliate',
        'visibility' => [
          'is_signup_hidden' => false,
        ],
      ],
    ];

    public static function getFooterMenu(array $visibility_options = []): array {
      return array_filter(self::$footerMenu, function($item) use ($visibility_options) {
        if (isset($item['visibility'])) {
          foreach ($item['visibility'] as $name => $value) {
            if (isset($visibility_options[$name]) && $value !== $visibility_options[$name]) {
              return false;
            }
          }
        }

        return true;
      });
    }
}
