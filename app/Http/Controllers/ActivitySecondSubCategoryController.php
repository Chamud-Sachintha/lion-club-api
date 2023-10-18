<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ActivityFirstSubCategory;
use App\Models\ActivitySecondSubCategory;
use App\Models\Governer;
use Illuminate\Http\Request;

class ActivitySecondSubCategoryController extends Controller
{
    private $Apphelper;
    private $Governer;
    private $SecondSubCategory;
    private $FirstSubcategory;

    public function __construct()
    {   
        $this->Apphelper = new AppHelper();
        $this->Governer = new Governer();
        $this->SecondSubCategory = new ActivitySecondSubCategory();
        $this->FirstSubcategory = new ActivityFirstSubCategory();
    }

    public function addSecondSubCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $firstSubCategoryCode = (is_null($request->firstSubCategoryCode) || empty($request->firstSubCategoryCode)) ? "" : $request->firstSubCategoryCode;
        $secondSubcategoryCode = (is_null($request->secondSubCategoryCode) || empty($request->secondSubCategoryCode)) ? "" : $request->secondSubCategoryCode;
        $categoryName = (is_null($request->categoryName) || empty($request->categoryName)) ? "" : $request->categoryName;

        if ($request_token == "") {
            return $this->Apphelper->responseMessageHandle(0, "token is requied.");
        } else if ($flag == "") {
            return $this->Apphelper->responseMessageHandle(0, "Flag is required.");
        } else if ($firstSubCategoryCode == "") {
            return $this->Apphelper->responseMessageHandle(0, "First Sub Category is required.");
        } else if ($secondSubcategoryCode == "") {
            return $this->Apphelper->responseMessageHandle(0, "category Code is required.");
        } else if ($categoryName == "") {
            return $this->Apphelper->responseMessageHandle(0, "category Name is required.");
        } else {
            try {
                $catInfo = array();
                $firstSubCategory = $this->FirstSubcategory->find_by_code($firstSubCategoryCode);
                $secondSubCategory = $this->SecondSubCategory->find_by_code($secondSubcategoryCode);

                if (!empty($secondSubCategory)) {
                    return $this->Apphelper->responseMessageHandle(0, "Category Already Exist.");
                }

                if (empty($firstSubCategory)) {
                    return $this->Apphelper->responseMessageHandle(0, "Invalid First Sub category");
                }

                $catInfo['secondCategoryCode'] = $secondSubcategoryCode;
                $catInfo['firstCategoryCode'] = $firstSubCategoryCode;
                $catInfo['categoryName'] = $categoryName;
                $catInfo['createTime'] = $this->Apphelper->get_date_and_time();

                $newSecondSubCategory = $this->SecondSubCategory->add_log($catInfo);

                if ($newSecondSubCategory) {
                    return $this->Apphelper->responseEntityHandle(1, "Operation Complete", $newSecondSubCategory);
                } else {
                    return $this->Apphelper->responseMessageHandle(0, "Error Occured.");
                }

            } catch (\Exception $e) {
                return $this->Apphelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getSecondSubCategoryList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->Apphelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->Apphelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $allFirstCategoryList = $this->SecondSubCategory->query_all();

                $firstCategoryList = array();
                foreach ($allFirstCategoryList as $key => $value) {
                    $firstCategoryList[$key]['secondSubCategoryCode'] = $value['code'];
                }

                return $this->Apphelper->responseEntityHandle(1, "Operation Complete", $firstCategoryList);
            } catch (\Exception $e) {
                return $this->Apphelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getFirstCategoryBymainCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag= (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $firstCategoryCode = (is_null($request->firstCategoryCode) || empty($request->firstCategoryCode)) ? "" : $request->firstCategoryCode;

        if($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($firstCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
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
