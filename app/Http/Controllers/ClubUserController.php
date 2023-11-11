<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Mail\AddUserMail;
use App\Models\Activity;
use App\Models\ClubActivity;
use App\Models\ClubActivtyPointReserve;
use App\Models\ClubUser;
use App\Models\Governer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClubUserController extends Controller
{
    private $ClubUser;
    private $Governer;
    private $ClubActivity;
    private $ClubActivityPointsReserved;
    private $Activity;
    private $AppHelper;

    public function __construct()
    {
        $this->ClubUser = new ClubUser();
        $this->Governer = new Governer();
        $this->ClubActivity = new ClubActivity();
        $this->ClubActivityPointsReserved = new ClubActivtyPointReserve();
        $this->Activity = new Activity();
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
                    $clubUserInfo['code'] = $clubUserCode;
                    $clubUserInfo['clubCode'] = $clubCode;
                    $clubUserInfo['name'] = $fullName;
                    $clubUserInfo['email'] = $emailAddress;
                    $clubUserInfo['password'] = 123;
                    $clubUserInfo['createTime'] = $this->AppHelper->day_time();

                    $clubUser = $this->ClubUser->add_log($clubUserInfo);

                    if ($clubUser) {

                        $govInfo = $this->Governer->check_permission($request_token, $flag);

                        $details = [
                            'title' => 'Dear Club User l',
                            'para1' => 'Welcome to the Lion Club in Sri Lanka! We are thrilled to have you join our community.',
                            'para2' => 'As a new member, we have created an account for you to access our website. Your initial login credentials are as follows:',
                            'para3' => 'Default Password: [123]',
                            'para4' => 'Please use these credentials to log in to your account for the first time. For security purposes, we strongly recommend that you change your password after your initial login. Your new password should be something unique and known only to you to safeguard your account.',
                        ];

                        $details2 = [
                            'title' => 'Dear Club User',
                            'para1' => "",
                            "para2" => "",
                            "para3" => "",
                            "para4" => "",
                        ];
    
                        Mail::to($govInfo->email)->send(new AddUserMail($details2));
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

                $dashboardInfo = array();
                $dashboardInfo['activityCount'] = $activityCount;
                $dashboardInfo['pointsTotal'] = $ponitsTotal;

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
                    $ponits = $this->ClubActivityPointsReserved->get_points_by_activity_and_club($value['id'], $value['club_code']);

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

    public function routePermission(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = $this->ClubUser->check_permission($request_token, $flag);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Permission Granted.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Permission");
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
