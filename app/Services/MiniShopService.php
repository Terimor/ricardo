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
        'label' => 'minishop.menu.home',
        'active' => 'home',
      ],
      [
        'url' => '/about',
        'label' => 'minishop.menu.about',
        'active' => 'minishop.about',
      ],
      [
        'url' => '/minishop/products',
        'label' => 'minishop.menu.products',
        'active' => 'minishop.products',
      ],
      [
        'url' => '/contact-us',
        'label' => 'minishop.menu.contact_us',
        'active' => 'minishop.contact',
      ]
    ];
    
    public static $footerMenu = [
        [
          'url' => '/',
          'label' => 'minishop.menu.home',
        ],
        [
          'url' => '/about',
          'label' => 'minishop.menu.about',
        ],
        [
          'url' => '/minishop/products',
          'label' => 'minishop.menu.products',
        ],
        [
          'url' => '/contact-us',
          'label' => 'minishop.menu.contact_us',
        ],
      ];
}
