<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ActivityMainCategory;
use App\Models\Governer;
use Illuminate\Http\Request;

class ActivityMainCategoryController extends Controller
{
    private $AppHelper;
    private $Governer;
    private $ActivityMainCategory;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Governer = new Governer();
        $this->ActivityMainCategory = new ActivityMainCategory();
    }

    public function addNewMainActivityCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag)|| empty($request->flag)) ? "" : $request->flag;

        $mainCategoryCode = (is_null($request->mainCategoryCode) || empty($request->mainCategoryCode)) ? "" : $request->mainCategoryCode;
        $categoryName = (is_null($request->categoryName) || empty($request->categoryName)) ? "" : $request->categoryName;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($mainCategoryCode == "") { 
            return $this->AppHelper->responseMessageHandle(0, "category Code is required.");
        } else if ($categoryName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Category Name is required.");
        } else {
            try {
                $catInfo = array();
                $mainCategory = $this->ActivityMainCategory->find_by_code($mainCategoryCode);

                if (!empty($mainCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Category Already Exists.");
                }

                $catInfo['mainCategoryCode'] = $mainCategoryCode;
                $catInfo['categoryName'] = $categoryName;
                $catInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $newCategory = $this->ActivityMainCategory->add_log($catInfo);

                if ($newCategory) {
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $catInfo);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAllMainCategoryList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {   
                $allMainCategoryList = $this->ActivityMainCategory->query_all();

                $maincategoryList = array();
                foreach ($allMainCategoryList as $key => $value) {
                    $maincategoryList[$key]['mainCategoryCode'] = $value['code'];
                    $maincategoryList[$key]['categoryName'] = $value['name'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $maincategoryList);
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
