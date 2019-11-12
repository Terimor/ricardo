<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api-key')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => ['auth.apikey']], function (Router $router) {
    $router->post('order/{orderId}/payment/{hash}/capture', 'ApiController@capturePayment');
    $router->post('order/{orderId}/payment/{hash}/void', 'ApiController@voidPayment');
});
