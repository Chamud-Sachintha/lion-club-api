<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ContextUser;
use App\Models\Governer;
use App\Models\Region;
use App\Models\RegionChairperson;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    private $Region;
    private $Governer;
    private $RegionChairPerson;
    private $ContextUser;
    private $AppHelper;

    public function __construct()
    {
        $this->Region = new Region();
        $this->Governer = new Governer();
        $this->RegionChairPerson = new RegionChairperson();
        $this->ContextUser = new ContextUser();
        $this->AppHelper = new AppHelper();
    }

    public function addNewRegionDetail(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $regionCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;
        $contextUserCode = (is_null($request->contextUserCode) || empty($request->contextUserCode)) ? "" : $request->contextUserCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is reuired.");
        } else if ($regionCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Region Code is required.");
        } else if ($contextUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Chair Person Code is required.");
        } else {
            try {
                $regionInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                // $chairPerson = $this->RegionChairPerson->find_by_code($chairPersonCode);
                $checkRegion = $this->Region->find_by_code($regionCode);

                if (!empty($checkRegion)) {
                    return $this->AppHelper->responseMessageHandle(0, "Region Already Exist.");
                }

                // if (empty($chairPerson)) {
                //     return $this->AppHelper->responseMessageHandle(0, "Invalid Chair Person Code.");
                // }

                if ($userPerm == true) {
                    $regionInfo['reCode'] = $regionCode;
                    $regionInfo['contextUserCode'] = $contextUserCode;
                    $regionInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $region = $this->Region->add_log($regionInfo);

                    if (!empty($region)) {
                        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $region);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Operation Not Complete");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Permissions.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getRegionList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $regionList = $this->Region->query_all();

                $allregionList = array();
                foreach ($regionList as $key => $value) {
                    $allregionList[$key]['regionCode'] = $value['region_code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $allregionList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getRegionListByContextUserCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        // $contextUserCode = (is_null($request->contextUserCode) || empty($request->contextUserCode)) ? "" : $request->contextUserCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $contextUser = $this->ContextUser->query_find_by_token($request_token);
                // dd($contextUser);
                if (empty($contextUser)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid User Code.");
                }

                $contextUserInfo = array();
                $contextUserInfo['contextUserCode'] = $contextUser->code;

                $allregionList = $this->Region->get_region_list($contextUserInfo);
                // dd($allregionList);
                $regionList = array();
                foreach ($allregionList as $key => $value) {
                    $regionList[$key]['regionCode'] = $value['region_code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $regionList);
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
