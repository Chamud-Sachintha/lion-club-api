<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ActivityFirstSubCategory;
use App\Models\ActivityMainCategory;
use App\Models\Governer;
use Illuminate\Http\Request;

class ActivityFirstSubCategoryController extends Controller
{
    private $AppHelper;
    private $Governer;
    private $FirstCategory;
    private $MainCategory;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Governer = new Governer();
        $this->FirstCategory = new ActivityFirstSubCategory();
        $this->MainCategory = new ActivityMainCategory();
    }

    public function addFirstSubCategory(Request $request) {
        
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag)|| empty($request->flag)) ? "" : $request->flag;
        $mainCategoryCode = (is_null($request->mainCategoryCode) || empty($request->mainCategoryCode)) ? "" : $request->mainCategoryCode;
        $firstSubCategoryCode = (is_null($request->firstSubCategoryCode) || empty($request->firstSubCategoryCode)) ? "" : $request->firstSubCategoryCode;
        $categoryName = (is_null($request->categoryName) || empty($request->categoryName)) ? "" : $request->categoryName;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($firstSubCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Category Code is required.");
        } else if ($categoryName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Category Name is required.");
        } else if ($mainCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Main category is required.");
        } else {

            try {
                $catInfo = array();
                $firstSubCategory = $this->FirstCategory->find_by_code($firstSubCategoryCode);
                $mainCategory = $this->MainCategory->find_by_code($mainCategoryCode);

                if (empty($mainCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Main category Code.");
                }

                if (!empty($firstSubCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Sub Category Already Exist");
                }

                $catInfo['firstSubCategoryCode'] = $firstSubCategoryCode;
                $catInfo['maincategoryCode'] = $mainCategoryCode;
                $catInfo['categoryName'] = $categoryName;
                $catInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $newFirstCategory = $this->FirstCategory->add_log($catInfo);

                if ($newFirstCategory) {
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $newFirstCategory);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAllFirstSubCategoryList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $allFirstSubCategoryList = $this->FirstCategory->query_all();

                $firstSubCategoryList = array();
                foreach ($allFirstSubCategoryList as $key => $value) {
                    $firstSubCategoryList[$key]['firstSubCategoryCode'] = $value['code'];
                    $firstSubCategoryList[$key]['mainCategoryCode'] = $value['main_cat_code'];
                    $firstSubCategoryList[$key]['categoryName'] = $value['category_name'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $firstSubCategoryList);
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
