<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;


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
    $products = ProductService::getDomainProducts();
    return view('minishop/pages/products', compact('products'));
  }
}
