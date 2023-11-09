<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Activity;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivityDocument;
use App\Models\ClubActivtyPointReserve;
use App\Models\ClubUser;
use App\Models\ContextUser;
use App\Models\Governer;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContextUserController extends Controller
{
    private $ContextUser;
    private $Governer;
    private $ClubActivity;
    private $ClubActivityDocument;
    private $ClubPoints;
    private $Zone;
    private $Activity;
    private $ClubUser;
    private $Club;
    private $Region;
    private $AppHelper;

    public function __construct()
    {
        $this->ContextUser = new ContextUser();
        $this->Governer = new Governer();
        $this->ClubActivity = new ClubActivity();
        $this->ClubActivityDocument = new ClubActivityDocument();
        $this->ClubPoints = new ClubActivtyPointReserve();
        $this->Activity = new Activity();
        $this->ClubUser = new ClubUser();
        $this->Region = new Region();
        $this->Club = new Club();
        $this->Zone = new Zone();
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

                $clubCount = DB::table('clubs')->select('clubs.*')
                                                ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                ->where('regions.context_user_code', '=', $contextUser->code)
                                                ->count();

                $activityList = $this->ClubActivity->get_list_by_creator($contextUser->code);

                $dashboardData = array();
                $dashboardData['regionCount'] = $regions;
                $dashboardData['zoneCount'] = $zones;
                $dashboardData['activityCount'] = count($activityList);
                $dashboardData['clubsCount'] = $clubCount;

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

                        $clubInfo = $this->Club->find_by_club_code($value['club_code']);
                        $zone = $this->Zone->find_by_zone_code($clubInfo->zone_code);

                        $activityList[$key]['activityCode'] = $value['activity_code'];
                        $activityList[$key]['clubCode'] = $value['club_code'];
                        $activityList[$key]['status'] = $value['status'];
                        $activityList[$key]['reCode'] = $zone->re_code;
                        $activityList[$key]['zoneCode'] = $zone->zone_code;
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

                    $clubActivity = $this->ClubActivity->find_by_id($value->club_activity_code);
                    $activity = $this->Activity->query_find($clubActivity->activity_code);
                    $createdUser = $this->checkUser($clubActivity->creator);

                    $dataList[$key]['clubCode'] = $value->club_code;
                    $dataList[$key]['points'] = $value->points;
                    $dataList[$key]['activityName'] = $activity->activity_name;
                    $dataList[$key]['creator'] = $createdUser;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getDashboardTableData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Toekn is requirec.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is requirec.");
        } else {

            try {
                $contectUser = $this->ContextUser->query_find_by_token($request_token);

                $resp = DB::table('clubs')->select('clubs.club_code as clubCode', 'regions.region_code as reCode', 'zones.zone_code as zoneCode')
                                            ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                            ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                            ->join('club_activities', 'club_activities.club_code', '=', 'clubs.club_code')
                                            ->where('regions.context_user_code', '=', $contectUser->code)
                                            ->get();

                $dataList = array();
                foreach ($resp as $key => $value) {

                    $totalActivities = $this->ClubActivity->get_activity_count_by_club_code($value->clubCode);
                    $totalPoints = $this->ClubPoints->get_points__by_club_code($value->clubCode);
                    $clubRank = $this->getClubRank($value->clubCode);

                    $dataList[$key]['regionCode'] = $value->reCode;
                    $dataList[$key]['zoneCode'] = $value->zoneCode;
                    $dataList[$key]['clubCode'] = $value->clubCode;
                    $dataList[$key]['activityCount'] = $totalActivities;
                    $dataList[$key]['totalPoints'] = $totalPoints;
                    $dataList[$key]['rank'] = $clubRank;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function checkUser($userCode) {

        $role = null;

        $clubUser = $this->ClubUser->find_by_code($userCode);
        $contextUser = $this->ContextUser->find_by_code($userCode);

        if ($clubUser) {
            $role = $clubUser->flag;
        } else {
            $role = $contextUser->flag;
        }

        return $role;
    }

    private function getClubRank($clubCode) {

        try {
            $resp = $this->ClubPoints->get_ordered_list();

            $clubRank = 1;
            foreach ($resp as $key => $value) {
                if ($value['club_code'] == $clubCode) {
                    break;
                }

                $clubRank += 1;
            }

            return $clubRank;

        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
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
