<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegionChairpersonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('authenticateUser', [AuthController::class, 'authenticateUser']);
Route::middleware('authToken')->post('menu-perm', [AuthController::class, 'checkMenuPermission']);
Route::middleware('authToken')->post('addRegionChairperson', [RegionChairpersonController::class, 'addNewRegionChairperson']);