<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Mail\AddUserMail;
use App\Models\Activity;
use App\Models\ChangePassword;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivityDocument;
use App\Models\ClubActivtyPointReserve;
use App\Models\ClubUser;
use App\Models\ContextUser;
use App\Models\Governer;
use App\Models\PointTemplate;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ContextUserController extends Controller
{
    private $ContextUser;
    private $Governer;
    private $ClubActivity;
    private $ClubActivityDocument;
    private $ChangePasswordLog;
    private $ClubPoints;
    private $Zone;
    private $Activity;
    private $ClubUser;
    private $PointTemplate;
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
        $this->ChangePasswordLog = new ChangePassword();
        $this->PointTemplate = new PointTemplate();
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

                $validateUser = $this->ContextUser->verify_email($emailAddress);

                if (!empty($validateUser)) {
                    return $this->AppHelper->responseMessageHandle(0, "User Already Exist.");
                }

                $pass = Str::random(8);

                if ($userPerm == true) {
                    $contextUserInfo['code'] = $contextUserCode;
                    $contextUserInfo['name'] = $fullName;
                    $contextUserInfo['email'] = $emailAddress;
                    $contextUserInfo['password'] =  $pass;
                    $contextUserInfo['createTime'] = $this->AppHelper->day_time();

                    $contextUser = $this->ContextUser->add_log($contextUserInfo);

                    if ($contextUser) {

                        $passwordLogInfo = array();
                        $passwordLogInfo['userEmail'] = $emailAddress;
                        $passwordLogInfo['password'] = $pass;
                        $passwordLogInfo['secret'] = sha1(time());
                        $passwordLogInfo['flag'] = "CNTU";
                        $passwordLogInfo['createTime'] = $this->AppHelper->get_date_and_time();

                        $this->ChangePasswordLog->add_log($passwordLogInfo);

                        $details = [
                            'userRole' => 'Context User',
                            'userName' => $contextUser->name,
                            'tempPass' => $pass,
                        ];
    
                        Mail::to($emailAddress)->send(new AddUserMail($details));

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
                                    // ->where('regions.context_user_code', '=', $contextuserCode->code)
                                    ->distinct()
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
                    $resp = $this->ClubActivity->get_list_by_creator($contextUser->code);

                    $clubActivityList = array();
                    foreach ($resp as $key => $value) {
                        $activityInfo = $this->Activity->query_find($value["activity_code"]);
                        $valueObj = $this->PointTemplate->find_by_code($activityInfo->point_template_code);

                        $f = json_decode($valueObj->value);

                        $rangeValue = null;
                        foreach ($f as $k => $v) {
                            if ($v->name == $value['type']) {
                                $rangeValue = $v->value;
                            }
                        }

                        if ($value['status'] == 1 || $value['status'] == 4) {
                            $pointResp = $this->ClubPoints->get_points_by_activity_and_club($value['id'], $value['club_code']);
                            $ponits = $pointResp->points;
                        } else if ($value['status'] == 2) {
                            $ponits = "N/A";
                        } else {
                            $ponits = "Pending";
                        }

                        $clubActivityList[$key]['activityCode'] = $value['activity_code'];
                        $clubActivityList[$key]['clubCode'] = $value['club_code'];
                        $clubActivityList[$key]['activityName'] = $activityInfo['activity_name'];
                        $clubActivityList[$key]['status'] = $value['status'];
                        $clubActivityList[$key]['type'] = $value['type'];
                        $clubActivityList[$key]['extValue'] = $value['ext_value'];
                        $clubActivityList[$key]['createTime'] = $value['create_time'];
                        $clubActivityList[$key]['activityTime'] = $value['date_of_activity'];
                        $clubActivityList[$key]['requestedRangeValue'] = $rangeValue;
                        $clubActivityList[$key]['approvedPoints'] = $ponits;
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $clubActivityList);
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
                                                    ->distinct()
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
                                            ->distinct('zones.zone_code')
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
            $resp = $this->Club->get_club_list_by_points_order();

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
