<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ActivityFirstSubCategory;
use App\Models\Governer;
use Illuminate\Http\Request;

class ActivityFirstSubCategoryController extends Controller
{
    private $AppHelper;
    private $Governer;
    private $FirstCategory;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Governer = new Governer();
        $this->FirstCategory = new ActivityFirstSubCategory();
    }

    public function addFirstSubCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag)|| empty($request->flag)) ? "" : $request->flag;
        $mainCategoryCode = (is_null($request->mainCategoryCode) || empty($request->mainCategoryCode)) ? "" : $request->mainCategoryCode;
        $firstSubCategoryCode = (is_null($request->firstSubCategoryCode) || empty($request->firstSubCategoryCode)) ? "" : $request->firstSubCategoryCode;
        $firstSubCategoryName = (is_null($request->firstSubCategoryname) || empty($request->firstSubCategoryName)) ? "" : $request->firstSubCategoryName;

        if ($request_token == "") {

        } else if ($flag == "") {

        } else if ($firstSubCategoryCode == "") {

        } else if ($firstSubCategoryName == "") {

        } else if ($mainCategoryCode == "") {

        } else {
            try {

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function checkPermission($token, $flag) {
        
        $perm = null;

        try {
            if ($flag == "G") {
                $perm = $this->Governer->check_permission($token, $flag);
            } else {
                return false;
            }

            if (!empty($perm)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
