<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Mail\AddUserMail;
use App\Models\Activity;
use App\Models\ChangePassword;
use App\Models\ClubActivity;
use App\Models\ClubActivtyPointReserve;
use App\Models\ClubUser;
use App\Models\Governer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClubUserController extends Controller
{
    private $ClubUser;
    private $Governer;
    private $ClubActivity;
    private $ClubActivityPointsReserved;
    private $Activity;
    private $ChangePasswordLog;
    private $AppHelper;

    public function __construct()
    {
        $this->ClubUser = new ClubUser();
        $this->Governer = new Governer();
        $this->ClubActivity = new ClubActivity();
        $this->ClubActivityPointsReserved = new ClubActivtyPointReserve();
        $this->Activity = new Activity();
        $this->ChangePasswordLog = new ChangePassword();
        $this->AppHelper = new AppHelper();
    }

    public function addNewClubUser(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $clubUserCode = (is_null($request->clubUserCode) || empty($request->clubUserCode)) ? "" : $request->clubUserCode;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $emailAddress = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($clubCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Club Code is required.");
        } else if ($clubUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Chairperson Code is required.");
        } else if ($fullName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        } else if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else {
            try {
                $clubUserInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                if ($userPerm == true) {

                    $validate_user = $this->ClubUser->verify_email($emailAddress);

                    if ($validate_user) {
                        return $this->AppHelper->responseMessageHandle(0, "User Already Exist");
                    }

                    $pass = Str::random(8);

                    $clubUserInfo['code'] = $clubUserCode;
                    $clubUserInfo['clubCode'] = $clubCode;
                    $clubUserInfo['name'] = $fullName;
                    $clubUserInfo['email'] = $emailAddress;
                    $clubUserInfo['password'] =  $pass;
                    $clubUserInfo['createTime'] = $this->AppHelper->day_time();

                    $clubUser = $this->ClubUser->add_log($clubUserInfo);

                    if ($clubUser) {

                        $govInfo = $this->Governer->check_permission($request_token, $flag);

                        $passwordLogInfo = array();
                        $passwordLogInfo['userEmail'] = $emailAddress;
                        $passwordLogInfo['password'] = $pass;
                        $passwordLogInfo['secret'] = sha1(time());
                        $passwordLogInfo['flag'] = "CU";
                        $passwordLogInfo['createTime'] = $this->AppHelper->get_date_and_time();

                        $this->ChangePasswordLog->add_log($passwordLogInfo);

                        $details = [
                            'userRole' => 'Club User',
                            'userName' => $clubUser->name,
                            'tempPass' => $pass,
                        ];
    
                        Mail::to($emailAddress)->send(new AddUserMail($details));

                        return $this->AppHelper->responseEntityHandle(1, "Chair Person Created.", $clubUser);
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

    public function getClubUserInfoByUserCode(request $request) {
        
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $clubUser = $this->ClubUser->query_find_by_token($request_token);

                if ($clubUser) {
                    $ClubUser["password"] = "hidden";
                    return $this->AppHelper->responseEntityHandle(1, "Operation Successfully", $clubUser);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubUserList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $allClubUserList = $this->ClubUser->query_all();

                $clubUserList = array();
                foreach ($allClubUserList as $key => $value) {
                    $clubUserList[$key]['clubUserCode'] = $value['code'];
                    $clubUserList[$key]['fullName'] = $value['name'];
                    $clubUserList[$key]['email'] = $value['email'];
                    $clubUserList[$key]['clubCode'] = $value['club_code'];
                    $clubUserList[$key]['status'] = $value['status'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $clubUserList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubUserInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubUserCode = (is_null($request->clubUserCode) || empty($request->clubUserCode)) ? "" : $request->clubUserCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($clubUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "User Code is required.");
        } else {

            try {
                $clubUserInfo = array();
                $clubUser = $this->ClubUser->find_by_code($clubUserCode);

                $clubUserInfo['code'] = $clubUser['code'];
                $clubUserInfo['name'] = $clubUser['name'];
                $clubUserInfo['email'] = $clubUser['email'];
                $clubUserInfo['clubCode'] = $clubUser['club_code'];

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $clubUserInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateClubUserByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubUserCode = (is_null($request->clubUserCode) || empty($request->clubUserCode)) ? "" : $request->clubUserCode;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokemn is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokemn is required.");
        } else if ($clubUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokemn is required.");
        } else if ($fullName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokemn is required.");
        } else if ($email == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokemn is required.");
        } else if ($clubCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokemn is required.");
        } else {

            try {
                $newClubUserInfo = array();
                $newClubUserInfo['name'] = $fullName;
                $newClubUserInfo['email'] = $email;
                $newClubUserInfo['clubCode'] = $clubCode;
                $newClubUserInfo['code'] = $clubUserCode;

                $updateClubUser = $this->ClubUser->update_club_user_by_code($newClubUserInfo);

                if ($updateClubUser) {
                    return $this->AppHelper->responseMessageHandle(1, "Operayion Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubUserDashboardData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $activityCount = $this->ClubActivity->get_activity_count_by_club_code($clubCode);
                $ponitsTotal = $this->ClubActivityPointsReserved->get_points__by_club_code($clubCode);
                $totalExtValueQuery = DB::table('club_activities')->select('club_activities.ext_value as extValue', 'activities.point_template_code as templateCode')
                                                        ->join('activities', 'activities.code', '=', 'club_activities.activity_code')
                                                        ->where('club_activities.club_code', '=', $clubCode)
                                                        ->get();

                $totalApprovedFunds = 0;
                $totalPeopleServed = 0;
                foreach($totalExtValueQuery as $key => $value) {
                    $templateNamePrefix = explode("-" ,$value->templateCode);

                    if (trim($templateNamePrefix[1]) == "C") {
                        $totalApprovedFunds += $value->extValue;
                    } else if (trim($templateNamePrefix[1]) == "P") {
                        $totalPeopleServed += $value->extValue;
                    } else {
                        
                    }
                }

                $dashboardInfo = array();
                $dashboardInfo['activityCount'] = $activityCount;
                $dashboardInfo['pointsTotal'] = $ponitsTotal;
                $dashboardInfo['totalFunds'] = $totalApprovedFunds;
                $dashboardInfo['totalPeopleServed'] = $totalPeopleServed;

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dashboardInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubUserDashboardTableData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($clubCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Club Code is required.");
        } else {

            try {

                $clubActivityInfo = $this->ClubActivity->find_by_club_code($clubCode);

                $cbList = array();
                foreach($clubActivityInfo as $key => $value) {

                    $activityInfo = $this->Activity->query_find($value['activity_code']);

                    if ($value['status'] == 1 || $value['status'] == 4) {
                        $ponits = $this->ClubActivityPointsReserved->get_points_by_activity_and_club($value['id'], $value['club_code']);
                    } else if ($value['status'] == 2) {
                        $ponits['points'] = "N/A";
                    } else {
                        $ponits['points'] = "Pending";
                    }

                    $cbList[$key]['activityName'] = $activityInfo['activity_name'];
                    $cbList[$key]['createTime'] = $value['create_time'];
                    $cbList[$key]['extValue'] = $value['ext_value'];
                    $cbList[$key]['status'] = $value['status'];
                    $cbList[$key]['points'] = $ponits['points'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $cbList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deleteClubUserByCode(Request $request) {

        // return $this->AppHelper->responseMessageHandle(0, "Cannot Delete Club User.");

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubUserCode = (is_null($request->clubUserCode) || empty($request->clubUserCode)) ? "" : $request->clubUserCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($clubUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Club Code is required.");
        } else {

            try {

                $resp = $this->ClubUser->delete_by_code($clubUserCode);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Operation Failed.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function enableClubUserByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubUserCode = (is_null($request->clubUserCode) || empty($request->clubUserCode)) ? "" : $request->clubUserCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {   
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($clubUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "User Code is required.");
        } else {

            try {
                $resp = $this->ClubUser->activate_user($clubUserCode);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Operation Failed.");
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
