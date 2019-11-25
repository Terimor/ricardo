<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;

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
        $loadedPhrases = (new I18nService())->loadPhrases('index_page');        
        return view('index', compact('loadedPhrases'));
    }
}