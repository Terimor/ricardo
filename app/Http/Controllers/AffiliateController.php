<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AffiliateService;
use StdClass;


class AffiliateController extends Controller
{
    
    protected $affiliateService;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }
    
    /**
     * For fingerpring js
     * @param Request $request
     */
    public function fingerprintClick(Request $request)
    {
        $this->affiliateService->fingerprintClick($request);
    }

    
}
