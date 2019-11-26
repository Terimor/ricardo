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
   * Mini-Shop - Products
   * @param Request $request
   * @param ProductService $productService
   * @return type
   */
  public function products(Request $request, ProductService $productService) {
    $products = ProductService::getDomainProducts() ?? [];
    return view('minishop/pages/products/products', compact('products'));
  }
}
