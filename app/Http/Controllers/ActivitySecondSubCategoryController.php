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
                $allSecondCategoryList = $this->SecondSubCategory->query_all();

                $secondCategoryList = array();
                foreach ($allSecondCategoryList as $key => $value) {
                    $secondCategoryList[$key]['secondSubCategoryCode'] = $value['code'];
                    $secondCategoryList[$key]['categoryName'] = $value['category_name'];
                    $secondCategoryList[$key]['firstSubCategoryCode'] = $value['first_cat_code'];
                }

                return $this->Apphelper->responseEntityHandle(1, "Operation Complete", $secondCategoryList);
            } catch (\Exception $e) {
                return $this->Apphelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getSecondCategoryByFirstCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag= (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $firstCategoryCode = (is_null($request->firstCategoryCode) || empty($request->firstCategoryCode)) ? "" : $request->firstCategoryCode;

        if($request_token == "") {
            return $this->Apphelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->Apphelper->responseMessageHandle(0, "Token is required.");
        } else if ($firstCategoryCode == "") {
            return $this->Apphelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->SecondSubCategory->find_by_first_cat_code($firstCategoryCode);

                $secondCategoryList = array();
                foreach ($resp as $key => $value) {
                    $secondCategoryList[$key]['secondSubCategoryCode'] = $value['code'];
                }

                return $this->Apphelper->responseEntityHandle(1, "Operation Complete", $secondCategoryList);
            } catch (\Exception $e) {
                return $this->Apphelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getSecondCategoryInfoByCode(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $secondCatCode = (is_null($request->secondCategoryCode) || empty($request->secondCategoryCode)) ? "" : $request->secondCategoryCode;

        if ($request_token == "") {
            return $this->Apphelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->Apphelper->responseMessageHandle(0, "Token is required.");
        } else if ($secondCatCode == "") {
            return $this->Apphelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->SecondSubCategory->find_by_code($secondCatCode);

                $secondCatInfo = array();
                $secondCatInfo['secondCategoryCode'] = $resp['code'];
                $secondCatInfo['firstCategoryCode'] = $resp['first_cat_code'];
                $secondCatInfo['categoryName'] = $resp['category_name'];

                return $this->Apphelper->responseEntityHandle(1, "Operation Complete", $secondCatInfo);
            } catch (\Exception $e) {
                return $this->Apphelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateSecondCategoryByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $secondCatCode = (is_null($request->secondSubCategoryCode) || empty($request->secondSubCategoryCode)) ? "" : $request->secondSubCategoryCode;
        $firstCatCode = (is_null($request->firstSubCategoryCode) || empty($request->firstSubCategoryCode)) ? "" : $request->firstSubCategoryCode;
        $secondCatName = (is_null($request->categoryName) || empty($request->categoryName)) ? "" : $request->categoryName;

        if ($request_token == "") {
            return $this->Apphelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->Apphelper->responseMessageHandle(0, "Flag is required.");
        } else if ($secondCatCode == "") {
            return $this->Apphelper->responseMessageHandle(0, "Second Cateory Code is required.");
        } else if ($firstCatCode == "") {
            return $this->Apphelper->responseMessageHandle(0, "First Category Code is required.");
        } else if ($secondCatName == "") {
            return $this->Apphelper->responseMessageHandle(0, "Category Name is required.");
        } else {

            try {
                $newCategoryInfo = array();
                $newCategoryInfo['secondCategoryCode'] = $secondCatCode;
                $newCategoryInfo['categoryName'] = $secondCatName;
                $newCategoryInfo['firstCatCode'] = $firstCatCode;

                $firstCategory = $this->FirstSubcategory->find_by_code($firstCatCode);

                if (empty($firstCategory)) {
                    return $this->Apphelper->responseMessageHandle(0, "Invalid Category Code.");
                }

                $resp = $this->SecondSubCategory->update_second_category_by_code($newCategoryInfo);

                if ($resp) {
                    return $this->Apphelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->Apphelper->responseMessageHandle(0, "Error Occured.");
                }
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
