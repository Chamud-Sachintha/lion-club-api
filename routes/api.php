<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityFirstSubCategoryController;
use App\Http\Controllers\ActivityMainCategoryController;
use App\Http\Controllers\ActivitySecondSubCategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubActivityController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ClubUserController;
use App\Http\Controllers\ContextUserController;
use App\Http\Controllers\EvaluatorController;
use App\Http\Controllers\PointTemplateController;
use App\Http\Controllers\ProofDocumentController;
use App\Http\Controllers\RegionChairpersonController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ZonalChairPersonController;
use App\Http\Controllers\ZoneController;
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
Route::middleware('authToken')->post('addZonalChairperson', [ZonalChairPersonController::class, 'addNewZonalChairperson']);
Route::middleware('authToken')->post('addContextUser', [ContextUserController::class, 'addNewContextUser']);
Route::middleware('authToken')->post('addEvaluvator', [EvaluatorController::class, 'addNewEvaluvator']);
Route::middleware('authToken')->post('addClubUser', [ClubUserController::class, 'addNewClubUser']);

Route::middleware('authToken')->post('region-chair-person-list', [RegionChairpersonController::class, 'getAllRegionChairPersonsList']);
Route::middleware('authToken')->post('get-region-list', [RegionController::class, 'getRegionList']);
Route::middleware('authToken')->post('get_zonal-chair-person-list', [ZonalChairPersonController::class, 'getZonalChairPersonList']);
Route::middleware('authToken')->post('get_zone-list', [ZoneController::class, 'getZoneList']);
Route::middleware('authToken')->post('get-club-list', [ClubController::class, 'getClubList']);
Route::middleware('authToken')->post('get-context-user-list', [ContextUserController::class, 'getContextUserList']);
Route::middleware('authToken')->post('get-region-list-by-usercode', [RegionController::class, 'getRegionListByContextUserCode']);
Route::middleware('authToken')->post('get-zone-list-by-re-code', [ZoneController::class, 'getZoneListByRegionCode']);
Route::middleware('authToken')->post('get-club-activity-list', [ClubActivityController::class, 'getClubActivityList']);
Route::middleware('authToken')->post('get-evaluvators-list', [EvaluatorController::class, 'getEvaluvatorUserList']);

Route::middleware('authToken')->post('addRegion', [RegionController::class, 'addNewRegionDetail']);
Route::middleware('authToken')->post('addZone', [ZoneController::class, 'addNewZone']);
Route::middleware('authToken')->post('addClub', [ClubController::class, 'addNewClub']);

Route::middleware('authToken')->post('add-main-category', [ActivityMainCategoryController::class, 'addNewMainActivityCategory']);
Route::middleware('authToken')->post('main-category-list', [ActivityMainCategoryController::class, 'getAllMainCategoryList']);
Route::middleware('authToken')->post('add-first-sub-category', [ActivityFirstSubCategoryController::class, 'addFirstSubCategory']);
Route::middleware('authToken')->post('get-first-sub-category-list', [ActivityFirstSubCategoryController::class, 'getAllFirstSubCategoryList']);
Route::middleware('authToken')->post('add-second-sub-category', [ActivitySecondSubCategoryController::class, 'addSecondSubCategory']);
Route::middleware('authToken')->post('get-second-category-list', [ActivitySecondSubCategoryController::class, 'getSecondSubCategoryList']);
Route::middleware('authToken')->post('add-proof-doc', [ProofDocumentController::class, 'addNewProofDocument']);
Route::middleware('authToken')->post('get-proof-doc-list', [ProofDocumentController::class, 'getProofDocList']);
Route::middleware('authToken')->post('get-firstCatList-by-mainCatCode', [ActivityFirstSubCategoryController::class, 'getFirstCategoryBymainCategory']);
Route::middleware('authToken')->post('get-secondCatList-by-firstCatCode', [ActivitySecondSubCategoryController::class, 'getSecondCategoryByFirstCategory']);
Route::middleware('authToken')->post('get-activities-by-codes', [ActivityController::class, 'findActivityByCodes']);
Route::middleware('authToken')->post('club-list-by-context-user', [ContextUserController::class, 'getAvailableClubList']);

Route::middleware('authToken')->post('add-point-template', [PointTemplateController::class, 'addNewPointTemplate']);
Route::middleware('authToken')->post('get-template-list', [PointTemplateController::class, 'getAllPointTemplateList']);
Route::middleware('authToken')->post('add-activity', [ActivityController::class, 'addNewActivity']);
Route::middleware('authToken')->post('get-activity-list', [ActivityController::class, 'getActivityList']);
Route::middleware('authToken')->post('get-activity-info', [ActivityController::class, 'getActivityInfoByCode']);
Route::middleware('authToken')->post('get-club-user-info', [ClubUserController::class, 'getClubUserInfoByUserCode']);
Route::middleware('authToken')->post('get-club-user-list', [ClubUserController::class, 'getClubUserList']);

Route::middleware('authToken')->post('submit-club-activity', [ClubActivityController::class, 'addnewClubActivityRecord']);
Route::middleware('authToken')->post('get-template-obj-by-code', [PointTemplateController::class, 'getPointTemplateObjectByCode']);
Route::middleware('authToken')->post('club-activity-list', [ClubActivityController::class, 'getAllClubActivityList']);

Route::middleware('authToken')->post('get-activity-doc-list-by-code', [ClubActivityController::class, 'getClubActivityDocumentsByCode']);
Route::middleware('authToken')->post('get-activity-image-list-by-code', [ClubActivityController::class, 'getClubActivityImageListByCode']);
Route::middleware('authToken')->post('get-activity-info-by-code', [ClubActivityController::class, 'getClubActivityInfoByActivityCode']);

Route::middleware('authToken')->post('load-re-chairperson-data', [RegionChairpersonController::class, 'loadUserData']);