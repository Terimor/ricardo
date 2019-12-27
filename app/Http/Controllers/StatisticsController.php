<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StatisticsService;
use StdClass;


/*use com\checkout;
use com\checkout\ApiServices;*/

class StatisticsController extends Controller
{
    
    protected $statisticsService;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }
    
    public function fingerprintClick(Request $request)
    {
        $this->statisticsService->fingerprintClick($request);
    }

    
}
