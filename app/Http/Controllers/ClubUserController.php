<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ClubUser;
use App\Models\Governer;
use Illuminate\Http\Request;

class ClubUserController extends Controller
{
    private $ClubUser;
    private $Governer;
    private $AppHelper;

    public function __construct()
    {
        $this->ClubUser = new ClubUser();
        $this->Governer = new Governer();
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
                    $clubUserInfo['name'] = $fullName;
                    $clubUserInfo['email'] = $emailAddress;
                    $clubUserInfo['password'] = 123;
                    $clubUserInfo['createTime'] = $this->AppHelper->day_time();

                    $clubUser = $this->ClubUser->add_log($clubUserInfo);

                    if ($clubUser) {
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
