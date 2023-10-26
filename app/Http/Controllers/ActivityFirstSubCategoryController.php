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

    public function getFirstCategoryBymainCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag= (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $mainCategoryCode = (is_null($request->mainCategoryCode) || empty($request->mainCategoryCode)) ? "" : $request->mainCategoryCode;

        if($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($mainCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->FirstCategory->get_info_by_main_cat_code($mainCategoryCode);

                $firstCategoryList = array();
                foreach ($resp as $key => $value) {
                    $firstCategoryList[$key]['firstSubCategoryCode'] = $value['code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $firstCategoryList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getFirstCategoryInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $firstCatCode = (is_null($request->firstCategoryCode) || empty($request->firstCategoryCode)) ? "" : $request->firstCategoryCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($firstCatCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->FirstCategory->find_by_code($firstCatCode);

                $firstCatInfo = array();
                $firstCatInfo['firstCategoryCode'] = $resp['code'];
                $firstCatInfo['mainCategoryCode'] = $resp['main_cat_code'];
                $firstCatInfo['categoryName'] = $resp['category_name'];

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $firstCatInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateFirstCategoryInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $firstCatCode = (is_null($request->firstSubCategoryCode) || empty($request->firstSubCategoryCode)) ? "" : $request->firstSubCategoryCode;
        $mainCatCode = (is_null($request->mainCategoryCode) || empty($request->mainCategoryCode)) ? "" : $request->mainCategoryCode;
        $firstCatName = (is_null($request->categoryName) || empty($request->categoryName)) ? "" : $request->categoryName;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($firstCatCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "First Category Code is required.");
        } else if ($mainCatCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Main Category Code is required.");
        } else if ($firstCatName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Category Name is required.");
        } else {

            try {
                $newFirstCategoryInfo = array();
                $newFirstCategoryInfo['firstCategoryCode'] = $firstCatCode;
                $newFirstCategoryInfo['mainCatCode'] = $mainCatCode;
                $newFirstCategoryInfo['categoryNmae'] = $firstCatName;

                $mainCategory = $this->MainCategory->find_by_code($mainCatCode);
                $firstCategory = $this->FirstCategory->find_by_code($firstCatCode);

                if (empty($mainCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Main Category Code");
                }

                if (empty($firstCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid First Category");
                }

                $resp = $this->FirstCategory->update_first_category_by_code($newFirstCategoryInfo);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }

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
