<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use App\Models\ZonalChairPerson;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    private $Governer;
    private $Zone;
    private $ZonalChairPerson;
    private $AppHelper;

    public function __construct()
    {
        $this->Governer = new Governer();
        $this->Zone = new Zone();
        $this->ZonalChairPerson = new ZonalChairPerson();
        $this->AppHelper = new AppHelper();
    }

    public function addNewZone(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;
        // $chairPersonCode = (is_null($request->chairPersonCode) || empty($request->chairPersonCode)) ? "" : $request->chairPersonCode;
        $regionCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else  if ($zoneCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Zone Code is requirted.");
        // } else if ($chairPersonCode == "") {
        //     return $this->AppHelper->responseMessageHandle(0, "Chair Person Code is required.");
        } else if ($regionCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Region Code is required.");
        } else {
            try {
                $zonalInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                $zone = $this->Zone->find_by_zone_code($zoneCode);
                // $chairPerson = $this->ZonalChairPerson->find_by_code($chairPersonCode);
                
                if (!empty($zone)) {
                    return $this->AppHelper->responseMessageHandle(0, "Zone Already Exists.");
                }

                // if (empty($chairPerson)) {
                //     return $this->AppHelper->responseMessageHandle(0, "Invalid Chair Person Code.");
                // }

                if ($userPerm == true) {
                    $zonalInfo['zoneCode'] = $zoneCode;
                    // $zonalInfo['chairPersonCode'] = $chairPersonCode;
                    $zonalInfo['regionCode'] = $regionCode;
                    $zonalInfo['createTime'] = $this->AppHelper->get_date_and_time();
                    
                    $newZone = $this->Zone->add_log($zonalInfo);

                    if ($newZone) {
                        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $newZone);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Permissions");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getZoneList(Request $request) {
        
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $allZones = $this->Zone->query_all();

                $zoneList = array();
                foreach ($allZones as $key => $value) {
                    $zoneList[$key]['zoneCode'] = $value['zone_code'];
                    $zoneList[$key]['regionCode'] = $value['re_code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $zoneList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getZoneListByRegionCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $regionCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($regionCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Region Code is required.");
        } else {

            try {
                $allZoneList = $this->Zone->find_by_re_code($regionCode);

                $zoneList = array();
                foreach ($allZoneList as $key => $value) {
                    $zoneList[$key]['zoneCode'] = $value['zone_code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $zoneList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getZoneInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($zoneCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Zone Code is required.");
        } else {    

            try {
                $zoneInfo = array();
                $zone = $this->Zone->find_by_zone_code($zoneCode);

                $zoneInfo['code'] = $zone['zone_code'];
                $zoneInfo['reCode'] = $zone['re_code'];
                return $this->AppHelper->responseEntityHandle(1, "operation complete", $zoneInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateZoneByZoneCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;
        $reCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($zoneCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Zone Code is required.");
        } else if ($reCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Region Code is required.");
        } else {

            try {
                $newZoneInfo = array();
                $newZoneInfo['zoneCode'] = $zoneCode;
                $newZoneInfo['reCode'] = $reCode;

                $updateZone = $this->Zone->update_zone_by_code($newZoneInfo);

                if ($updateZone) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deleteRegionByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;

        if ($request_token == "") { 
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($zoneCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Region Code is required.");
        } else {

            try {
                $resp = $this->Zone->delete_zone_by_code($zoneCode);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operatyion Complete");
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
