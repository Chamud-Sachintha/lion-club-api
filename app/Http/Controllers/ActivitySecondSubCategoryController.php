<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ActivitySecondSubCategory;
use App\Models\Governer;
use Illuminate\Http\Request;

class ActivitySecondSubCategoryController extends Controller
{
    private $Apphelper;
    private $Governer;
    private $SecondSubCategory;

    public function __construct()
    {   
        $this->Apphelper = new AppHelper();
        $this->Governer = new Governer();
        $this->SecondSubCategory = new ActivitySecondSubCategory();
    }

    public function addSecondSubCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $firstSubCategoryCode = (is_null($request->firstSubCategoryCode) || empty($request->firstSubCategoryCode)) ? "" : $request->firstSubCategoryCode;
        $secondSubcategoryCode = (is_null($request->secondSubCategoryCode) || empty($request->secondSubCategoryCode)) ? "" : $request->secondSubCategirCode;
        $secondSubCategoryName = (is_null($request->secondSubCategoryName) || empty($request->secondSubCategoryName)) ? "" : $request->secondSubCategoryname;

        if ($request_token == "") {

        } else if ($flag == "") {

        } else if ($firstSubCategoryCode == "") {

        } else if ($secondSubcategoryCode == "") {

        } else if ($secondSubCategoryName == "") {

        } else {
            try {

            } catch (\Exception $e) {
                return $this->Apphelper->responseMessageHandle(0, $e->getMessage());
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
