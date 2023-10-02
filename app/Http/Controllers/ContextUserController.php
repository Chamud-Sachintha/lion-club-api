<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ContextUser;
use App\Models\Governer;
use Illuminate\Http\Request;

class ContextUserController extends Controller
{
    private $ContextUser;
    private $Governer;
    private $AppHelper;

    public function __construct()
    {
        $this->ContextUser = new ContextUser();
        $this->Governer = new Governer();
        $this->AppHelper = new AppHelper();
    }

    public function addNewZonalChairperson(Request $request) {

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
