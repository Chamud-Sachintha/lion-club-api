<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use App\Models\ZonalChairPerson;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZonalChairPersonController extends Controller
{
    private $ZonalChairPerson;
    private $Governer;
    private $Zone;
    private $AppHelper;

    public function __construct()
    {
        $this->ZonalChairPerson = new ZonalChairPerson();
        $this->Governer = new Governer();
        $this->Zone = new Zone();
        $this->AppHelper = new AppHelper();
    }

    public function addNewZonalChairperson(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $zonalChairpersonCode = (is_null($request->zonalChairpersonCode) || empty($request->zonalChairpersonCode)) ? "" : $request->zonalChairpersonCode;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $emailAddress = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;

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
        } else if ($zoneCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Zone Code is required.");
        } else {
            try {
                $chairPersonInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                if ($userPerm == true) {
                    $chairPersonInfo['code'] = $zonalChairpersonCode;
                    $chairPersonInfo['name'] = $fullName;
                    $chairPersonInfo['email'] = $emailAddress;
                    $chairPersonInfo['password'] = 123;
                    $chairPersonInfo['zoneCode'] = $zoneCode;
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

    public function getZonalChairPersonList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is requred.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $allChairPersonList = $this->ZonalChairPerson->query_all();

                $chairPersonList = array();
                foreach ($allChairPersonList as $key => $value) {
                    $chairPersonList[$key]['zonalChairpersonCode'] = $value['code'];
                    $chairPersonList[$key]['fullName'] = $value['name'];
                    $chairPersonList[$key]['email'] = $value['email'];
                    $chairPersonList[$key]['zoneCode'] = $value['zone_code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $chairPersonList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getZonalChairPersonInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $chiarPersonCode = (is_null($request->zonalChairpersonCode) || empty($request->zonalChairpersonCode)) ? "" : $request->zonalChairpersonCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($chiarPersonCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Chair Person Code is required.");
        } else {

            try {
                $resp = $this->ZonalChairPerson->find_by_code($chiarPersonCode);

                $userInfo = array();
                $userInfo['code'] = $resp['code'];
                $userInfo['name'] = $resp['name'];
                $userInfo['email'] = $resp['email'];
                $userInfo['zoneCode'] = $resp['zone_code'];

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $userInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateZonalChairpersonByCode(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;
        $name = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $zoneUserCode = (is_null($request->zonalChairpersonCode) || empty($request->zonalChairpersonCode)) ? "" : $request->zonalChairpersonCode;

        if ($request_token == "") { 
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($zoneCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Zone Code is required.");
        } else if ($name == "") {
            return $this->AppHelper->responseMessageHandle(0, "Name is required.");
        } else {

            try {
                $newChairpersonInfo = array();
                $region = $this->Zone->find_by_zone_code($zoneCode);

                if (empty($region)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Zone Code");
                }

                $newChairpersonInfo['zoneCode'] = $zoneCode;
                $newChairpersonInfo['name'] = $name;
                $newChairpersonInfo['email'] = $email;
                $newChairpersonInfo['code'] = $zoneUserCode;

                $updateUser = $this->ZonalChairPerson->update_user_by_code($newChairpersonInfo);

                if ($updateUser) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Copmplete");
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
                
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deleteUserByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $zoneUserCode = (is_null($request->zonalChairpersonCode) || empty($request->zonalChairpersonCode)) ? "" : $request->zonalChairpersonCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($zoneUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "User Code is required.");
        } else {

            try {
                $resp = $this->ZonalChairPerson->delete_by_code($zoneUserCode);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Error Occured.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
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
                $resp = $this->ZonalChairPerson->check_permission($request_token, $flag);

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
