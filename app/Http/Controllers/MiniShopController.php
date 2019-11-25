<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\I18nService;

class MiniShopController extends Controller
{
     /**
      * Index page
      * @param Request $request
      * @param ProductService $productService
      * @return type
      */
     public function index(Request $request, ProductService $productService)
     {
         $loadedPhrases = (new I18nService())->loadPhrases('minishop_page');
         $products = ProductService::getDomainProducts();         
         return view('mini_shop.index', compact('loadedPhrases', 'products'));
     }    
}