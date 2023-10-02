<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use App\Models\ZonalChairPerson;
use Illuminate\Http\Request;

class ZonalChairPersonController extends Controller
{
    private $ZonalChairPerson;
    private $Governer;
    private $AppHelper;

    public function __construct()
    {
        $this->ZonalChairPerson = new ZonalChairPerson();
        $this->Governer = new Governer();
        $this->AppHelper = new AppHelper();
    }

    public function addNewZonalChairperson(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $zonalChairpersonCode = (is_null($request->zonalChairpersonCode) || empty($request->zonalChairpersonCode)) ? "" : $request->zonalChairpersonCode;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $emailAddress = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($zonalChairpersonCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Chairperson Code is required.");
        } else if ($fullName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        } else if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else {
            try {
                $chairPersonInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                if ($userPerm == true) {
                    $chairPersonInfo['code'] = $zonalChairpersonCode;
                    $chairPersonInfo['name'] = $fullName;
                    $chairPersonInfo['email'] = $emailAddress;
                    $chairPersonInfo['password'] = 123;
                    $chairPersonInfo['createTime'] = $this->AppHelper->day_time();

                    $chairPerson = $this->ZonalChairPerson->add_log($chairPersonInfo);

                    if ($chairPerson) {
                        return $this->AppHelper->responseEntityHandle(1, "Chair Person Created.", $chairPerson);
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
