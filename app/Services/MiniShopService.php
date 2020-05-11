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
      // disable Call Us
      /*[
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
      ],*/
    ];

}
