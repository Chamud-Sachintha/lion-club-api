<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use App\Models\Region;
use App\Models\RegionChairperson;
use Illuminate\Http\Request;

class RegionChairpersonController extends Controller
{
    
    private $RegionChairperson;
    private $Region;
    private $Governer;
    private $AppHelper;

    public function __construct()
    {
        $this->RegionChairperson = new RegionChairperson();
        $this->Governer = new Governer();
        $this->Region = new Region();
        $this->AppHelper = new AppHelper();
    }

    public function addNewRegionChairperson(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $regonChairpersonCode = (is_null($request->reChairPersonCode) || empty($request->reChairPersonCode)) ? "" : $request->reChairPersonCode;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $emailAddress = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $regionCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($regonChairpersonCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Chairperson Code is required.");
        } else if ($fullName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        } else if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else if ($regionCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Region Code is required.");
        } else {
            try {
                $chairPersonInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                if ($userPerm == true) {
                    $chairPersonInfo['code'] = $regonChairpersonCode;
                    $chairPersonInfo['name'] = $fullName;
                    $chairPersonInfo['email'] = $emailAddress;
                    $chairPersonInfo['password'] = 123;
                    $chairPersonInfo['regionCode'] = $regionCode;
                    $chairPersonInfo['createTime'] = $this->AppHelper->day_time();

                    $chairPerson = $this->RegionChairperson->add_log($chairPersonInfo);

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

    public function getAllRegionChairPersonsList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {    
            try {
                $allReChairPersonList = $this->RegionChairperson->query_all();

                $regionChairPersonList = array();
                foreach ($allReChairPersonList as $key => $value) {
                    $regionChairPersonList[$key]['reChairPersonCode'] = $value['code'];
                    $regionChairPersonList[$key]['fullName'] = $value['name'];
                    $regionChairPersonList[$key]['email'] = $value['email'];
                    $regionChairPersonList[$key]['regionCode'] = $value['region_code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $regionChairPersonList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function loadUserData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $chiarPersonCode = (is_null($request->reChairPersonCode) || empty($request->reChairPersonCode)) ? "" : $request->reChairPersonCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($chiarPersonCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Chair Person Code is required.");
        } else {

            try {
                $resp = $this->RegionChairperson->find_by_code($chiarPersonCode);

                $userInfo = array();
                $userInfo['code'] = $resp['code'];
                $userInfo['name'] = $resp['name'];
                $userInfo['email'] = $resp['email'];
                $userInfo['reCode'] = $resp['region_code'];

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $userInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateRegionChairPersonByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $reCode = (is_null($request->reCode) || empty($request->reCode)) ? "" : $request->reCode;
        $name = (is_null($request->name) || empty($request->name)) ? "" : $request->name;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") { 
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($reCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Region Code is required.");
        } else if ($name == "") {
            return $this->AppHelper->responseMessageHandle(0, "Name is required.");
        } else {

            try {
                $newChairpersonInfo = array();
                $region = $this->Region->find_by_code($reCode);

                if (empty($region)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Region Code");
                }

                $newChairpersonInfo['reCode'] = $reCode;
                $newChairpersonInfo['name'] = $name;
                $newChairpersonInfo['email'] = $email;

                $updateUser = $this->RegionChairperson->update_user_by_code($newChairpersonInfo);

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
