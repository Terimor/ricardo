<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StatisticsService;
use StdClass;


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
    
    /**
     * For fingerpring js
     * @param Request $request
     */
    public function fingerprintClick(Request $request)
    {
        $this->statisticsService->fingerprintClick($request);
    }

    
}
