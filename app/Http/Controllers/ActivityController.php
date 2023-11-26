<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Activity;
use App\Models\ActivityFirstSubCategory;
use App\Models\ActivityMainCategory;
use App\Models\ActivitySecondSubCategory;
use App\Models\Governer;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    private $AppHelper;
    private $Governer;
    private $Activity;
    private $MainActivity;
    private $FirstSubCategory;
    private $SecondSubCategory;
    
    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Governer = new Governer();
        $this->Activity = new Activity();
        $this->MainActivity = new ActivityMainCategory();
        $this->FirstSubCategory = new ActivityFirstSubCategory();
        $this->SecondSubCategory = new ActivitySecondSubCategory();
            }

    public function addNewActivity(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;
        $mainCategoryCode = (is_null($request->mainCatCode) || empty($request->mainCatCode)) ? "" : $request->mainCatCode;
        $firstCategoryCode = (is_null($request->firstCatCode) || empty($request->firstCatCode)) ? "" : $request->firstCatCode;
        $secondCategoryCode = (is_null($request->secondCatCode) || empty($request->secondCatCode)) ? "" : $request->secondCatCode;
        $activityName = (is_null($request->activityName) || empty($request->activityName)) ? "" : $request->activityName;
        $authUserCode = (is_null($request->authUserCode) || empty($request->authUserCode)) ? "" : $request->authUserCode;
        $templateCode = (is_null($request->templateCode) || empty($request->templateCode)) ? "" : $request->templateCode;
        $documentCode = (is_null($request->documentCode) || empty($request->documentCode)) ? "" : $request->documentCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Toekn is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($activityCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Code is required.");
        } else if ($mainCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Main Category is required.");
        } else if ($firstCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "First category is required.");
        } else if ($activityName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Name is required.");
        } else if ($secondCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Second category is required.");
        } else if ($authUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Authorized User is required.");
        } else if ($templateCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Template Code is required.");
        } else if ($documentCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Document Code is required.");
        } else {
            try {
                $userPerm = $this->checkPermission($request_token, $flag);

                $activity = $this->Activity->query_find($activityCode);
                $maincategory = $this->MainActivity->find_by_code($mainCategoryCode);
                $firstSubcategory = $this->FirstSubCategory->find_by_code($firstCategoryCode);
                $secondSubCategory = $this->SecondSubCategory->find_by_code($secondCategoryCode);

                if (!empty($activity)) {
                    return $this->AppHelper->responseMessageHandle(0, "Activity Already Exist.");
                }

                if (empty($maincategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Main Category");
                }

                if (empty($firstSubcategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid First Sub Category.");
                }

                if (empty($secondSubCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid second Sub Category.");
                }

                $activityInfo = array();
                if ($userPerm == true) {
                    $activityInfo['activityCode'] = $activityCode;
                    $activityInfo['mainCategoryCode'] = $mainCategoryCode;
                    $activityInfo['firstCategoryCode'] = $firstCategoryCode;
                    $activityInfo['secondCategoryCode'] = $secondCategoryCode;
                    $activityInfo['activityName'] = $activityName;
                    $activityInfo['authUser'] = $authUserCode;
                    $activityInfo['templateCode'] = $templateCode;
                    $activityInfo['docCode'] = $documentCode;
                    $activityInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $newActivity = $this->Activity->add_log($activityInfo);
                    
                    if ($newActivity) {
                        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $newActivity);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Permission.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getActivityInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $activityInfo = array();
                $activity = $this->Activity->query_find($activityCode);

                if ($activity) {
                    $activityInfo['activityCode'] = $activity['code'];
                    $activityInfo['mainCatCode'] = $activity['main_cat_code'];
                    $activityInfo['firstCatCode'] = $activity['first_cat_code'];
                    $activityInfo['secondCatCode'] = $activity['second_cat_code'];
                    $activityInfo['authorizedUser'] = $activity['authorized_user'];
                    $activityInfo['activityName'] = $activity['activity_name'];
                    $activityInfo['pointTemplateCode'] = $activity['point_template_code'];
                    $activityInfo['documents'] = json_decode($activity['doc_code']);
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $activityInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getActivityList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $allActivityList = $this->Activity->query_all();

                $activityList = array();
                foreach ($allActivityList as $key => $value) {
                    $activityList[$key]['activityCode'] = $value['code'];
                    $activityList[$key]['activityName'] = $value['activity_name'];
                    $activityList[$key]['mainCatCode'] = $value['main_cat_code'];
                    $activityList[$key]['firstCatCode'] = $value['first_cat_code'];
                    $activityList[$key]['secondCatCode'] = $value['second_cat_code'];
                    $activityList[$key]['templateCode'] = $value['point_template_code'];
                    $activityList[$key]['authUserCode'] = $value['authorized_user'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Opreation Complete", $activityList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function findActivityByCodes(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $mainCategoryCode = (is_null($request->mainCategoryCode) || empty($request->mainCategoryCode)) ? "" : $request->mainCategoryCode;
        $firstCategoryCode = (is_null($request->firstCategoryCode) || empty($request->firstCategoryCode)) ? "" : $request->firstCategoryCode;
        $secondCategoryCode = (is_null($request->secondCategoryCode) || empty($request->secondCategoryCode)) ? "" : $request->secondCategoryCode;
        $authCode = (is_null($request->authCode) || empty($request->authCode)) ? "" : $request->authCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($mainCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Main Category Code is required.");
        } else if ($firstCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "First Category Code is required.");
        } else if ($secondCategoryCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Second Category Code is required.");
        } else {

            try {
                $catInfo = array();
                $catInfo['firstCategoryCode'] = $firstCategoryCode;
                $catInfo['secondCategoryCode'] = $secondCategoryCode;
                $catInfo['mainCategoryCode'] = $mainCategoryCode;

                if ($authCode == 1) {
                    $catInfo['authUsers'] = 1;
                } else if ($authCode == 2) {
                    $catInfo['authUsers'] = 2;
                } else if ($authCode == 99) {
                    $catInfo['authUsers'] = [2,3];
                } else {
                    $catInfo['authUsers'] = [1,3];
                }

                $resp = $this->Activity->find_by_codes($catInfo);

                $activityList = array();
                foreach ($resp as $key => $value) {
                    $activityList[$key]['activityCode'] = $value['code'];
                    $activityList[$key]['activityName'] = $value['activity_name'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $activityList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateActivityByActivityCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;
        $activityName = (is_null($request->activityName) || empty($request->activityName)) ? "" : $request->activityName;
        $mainCatCode = (is_null($request->mainCatCode) || empty($request->mainCatCode)) ? "" : $request->mainCatCode;
        $firstCatCode = (is_null($request->firstCatCode) || empty($request->firstCatCode)) ? "" : $request->firstCatCode;
        $secondCatCode = (is_null($request->secondCatCode) || empty($request->secondCatCode)) ? "" : $request->secondCatCode;
        $authUserCode = (is_null($request->authUserCode) || empty($request->authUserCode)) ? "" : $request->authUserCode;
        $templateCode = (is_null($request->templateCode) || empty($request->templateCode)) ? "" : $request->templateCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {   
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($activityCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Code is required.");
        } else if ($activityName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Name is required.");
        } else if ($mainCatCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Main Catgory Code is required.");
        } else if ($firstCatCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "First Cateory Code is required.");
        } else if ($firstCatCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "First Category Coide is required.");
        } else if ($secondCatCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Second Cateory Coide is required.");
        } else {

            try {
                $newActivityInfo = array();

                $mainCategory = $this->MainActivity->find_by_code($mainCatCode);
                $firstCategory = $this->FirstSubCategory->find_by_code($firstCatCode);
                $secondCategory = $this->SecondSubCategory->find_by_code($secondCatCode);

                if (empty($firstCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid First Category Code.");
                }

                if (empty($mainCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Main Category Code");
                }

                if (empty($secondCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Second Cateory Code");
                }

                $activityInfo = array();
                $activityInfo['activityCode'] = $activityCode;
                $activityInfo['mainCategoryCode'] = $mainCatCode;
                $activityInfo['firstCategoryCode'] = $firstCatCode;
                $activityInfo['secondCategoryCode'] = $secondCatCode;
                $activityInfo['activityName'] = $activityName;
                $activityInfo['authUser'] = $authUserCode;
                $activityInfo['templateCode'] = $templateCode;
                // $activityInfo['docCode'] = $documentCode;
                // $activityInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $resp = $this->Activity->update_by_code($activityInfo);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Ocured.");
                }
                
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deleteActivityByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($activityCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Code is required.");
        } else {

            try {
                $resp = $this->Activity->delete_activity_by_code($activityCode);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "error Occured.");
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
