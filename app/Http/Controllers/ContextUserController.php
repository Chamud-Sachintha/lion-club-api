<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ClubActivity;
use App\Models\ClubActivityDocument;
use App\Models\ContextUser;
use App\Models\Governer;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContextUserController extends Controller
{
    private $ContextUser;
    private $Governer;
    private $ClubActivity;
    private $ClubActivityDocument;
    private $Region;
    private $AppHelper;

    public function __construct()
    {
        $this->ContextUser = new ContextUser();
        $this->Governer = new Governer();
        $this->ClubActivity = new ClubActivity();
        $this->ClubActivityDocument = new ClubActivityDocument();
        $this->Region = new Region();
        $this->AppHelper = new AppHelper();
    }

    public function addNewContextUser(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $contextUserCode = (is_null($request->contextUserCode) || empty($request->contextUserCode)) ? "" : $request->contextUserCode;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $emailAddress = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($contextUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Context User Code is required.");
        } else if ($fullName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        } else if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else {
            try {
                $contextUserInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                if ($userPerm == true) {
                    $contextUserInfo['code'] = $contextUserCode;
                    $contextUserInfo['name'] = $fullName;
                    $contextUserInfo['email'] = $emailAddress;
                    $contextUserInfo['password'] = 123;
                    $contextUserInfo['createTime'] = $this->AppHelper->day_time();

                    $contextUser = $this->ContextUser->add_log($contextUserInfo);

                    if ($contextUser) {
                        return $this->AppHelper->responseEntityHandle(1, "Chair Person Created.", $contextUser);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Chair Person Not Created."); 
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Permissions.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getContextUserList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $allContextUserList = $this->ContextUser->query_all();

                $contextUserList = array();
                foreach ($allContextUserList as $key => $value) {
                    $contextUserList[$key]['contextUserCode'] = $value['code'];
                    $contextUserList[$key]['fullName'] = $value['name'];
                    $contextUserList[$key]['email'] = $value['email'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $contextUserList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAvailableClubList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $contextuserCode = $this->ContextUser->query_find_by_token($request_token);
                
                if (empty($contextuserCode)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Context UserCode.");
                }

                // dd($contextuserCode);

                $clubList = DB::table('clubs')->select('clubs.*')
                                    ->join('zones', 'clubs.zone_code', '=', 'zones.zone_code')
                                    ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                    ->where('regions.context_user_code', '=', $contextuserCode->code)
                                    ->get();

                $availableClubList = array();
                foreach ($clubList as $key => $value) {
                    $availableClubList[$key]['clubCode'] = $value->club_code;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $availableClubList);
            } catch(\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function feedClubActivity(Request $request) {

        $request_token= (is_null($request->token)|| empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;
        $conditionValue =  (is_null($request->value) || empty($request->value)) ? "" : $request->value;
        $conditiontype = (is_null($request->type) || empty($request->type)) ? "" : $request->type;
        $documentList = $request->files;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {   
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }else if ($clubCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $clubActivityInfo = array();
                $clubActivityInfo['activityCode'] = $activityCode;
                $clubActivityInfo['clubCode'] = $clubCode;
                $clubActivityInfo['type'] = $conditiontype;
                $clubActivityInfo['value'] = $conditionValue;
                // $clubActivityInfo['benificiaries'] = $benificiaries;
                // $clubActivityInfo['memberCount'] = $memberCount;
                $clubActivityInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $insertClubActivity = $this->ClubActivity->add_log($clubActivityInfo);
 
                if ($insertClubActivity) {

                    $docInfo = array();
                    foreach ($documentList as $key => $value) {
                         
                        $docInfo['activityCode'] = $activityCode;
                        $docInfo['createTime'] = $this->AppHelper->get_date_and_time();

                        $uniqueId = uniqid();
                        $ext = $value->getClientOriginalExtension();
                        $value->move(public_path('\modo\images'), $uniqueId . '.' . $ext);

                        $docInfo['document'] = $uniqueId . '.' . $ext;

                        $this->ClubActivityDocument->add_log($docInfo);
                    }

                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getContextUserInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $contextUserCode = (is_null($request->contextUserCode) || empty($request->contextUserCode)) ? "" : $request->contextUserCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($contextUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Context User Code is required.");
        } else {

            try {
                $userInfo = array();
                $contextUser = $this->ContextUser->find_by_code($contextUserCode);

                $userInfo['code'] = $contextUser['code'];
                $userInfo['name'] = $contextUser['name'];
                $userInfo['email'] = $contextUser['email'];

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $userInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateContextUserByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag =- (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $contextUserCode = (is_null($request->contextUserCode) || empty($request->contextUserCode)) ? "" : $request->contextUserCode;
        $name = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is requiored.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is requiored.");
        } else if ($contextUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Context User Code is requiored.");
        } else if ($name == "") {
            return $this->AppHelper->responseMessageHandle(0, "Name is requiored.");
        } else if ($email == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is requiored.");
        } else {

            try {
                $newContextUserInfo = array();
                $newContextUserInfo['code'] = $contextUserCode;
                $newContextUserInfo['name'] = $name;
                $newContextUserInfo['email'] = $email;

                $updateUser = $this->ContextUser->update_user_by_code($newContextUserInfo);

                if ($newContextUserInfo) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");;
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getDashboardData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $contextUser = $this->ContextUser->query_find_by_token($request_token);
                $regions = $this->Region->et_regions_count_by_context_user_code($contextUser->code);

                $zones = DB::table('zones')->select('zones.*')
                                            ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                            ->where('regions.context_user_code', '=', $contextUser->code)
                                            ->count();

                $activityList = $this->ClubActivity->get_list_by_creator($contextUser->code);

                $dashboardData = array();
                $dashboardData['regionCount'] = $regions;
                $dashboardData['zoneCount'] = $zones;
                $dashboardData['activityCount'] = count($activityList);

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dashboardData);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getContextUserFeedActivityList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $contextUser = $this->ContextUser->query_find_by_token($request_token);

                if ($contextUser) {
                    $clubActivityList = $this->ClubActivity->get_list_by_creator($contextUser->code);

                    $activityList = array();
                    foreach ($clubActivityList as $key => $value) {
                        $activityList[$key]['activityCode'] = $value['activity_code'];
                        $activityList[$key]['clubCode'] = $value['club_code'];
                        $activityList[$key]['status'] = $value['status'];
                        $activityList[$key]['createTime'] = $value['create_time'];
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $activityList);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid User");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deleteContextUserByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $userCode = (is_null($request->contextUserCode) || empty($request->contextUserCode)) ? "" : $request->contextUserCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($userCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "User Code is required.");
        } else {

            try {
                $resp = $this->ContextUser->delete_user_by_code($userCode);

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

    public function getContextUserViewDataList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $contextUser = $this->ContextUser->query_find_by_token(($request_token));

                $viewDataList = DB::table('clubs')->select('clubs.*', 'club_activty_point_reserves.*')
                                                    ->join('club_activty_point_reserves', 'clubs.club_code', '=', 'club_activty_point_reserves.club_code')
                                                    ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                    ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                    ->where('regions.context_user_code', '=', $contextUser->code)
                                                    ->get();

                $dataList = array();
                foreach ($viewDataList as $key => $value) {
                    $dataList[$key]['clubCode'] = $value->club_code;
                    $dataList[$key]['points'] = $value->points;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
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
