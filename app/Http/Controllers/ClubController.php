<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Club;
use App\Models\Governer;
use App\Models\Zone;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    private $Governer;
    private $Club;
    private $Zone;
    private $AppHelper;

    public function __construct()
    {
        $this->Governer = new Governer();
        $this->Club = new Club();
        $this->Zone = new Zone();
        $this->AppHelper = new AppHelper();
    }

    public function addNewClub(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;
        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($clubCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Club Code is required.");
        } else if ($zoneCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Zone code is required.");
        } else {
            try {
                $clubInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                $club = $this->Club->find_by_club_code($clubCode);
                $zone = $this->Zone->find_by_zone_code($zoneCode);

                if (!empty($club)) {
                    return $this->AppHelper->responseMessageHandle(0, "Club Already Exists.");
                }

                if (empty($zone)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Zone Code.");
                }

                if ($userPerm == true) {
                    $clubInfo['clubCode'] = $clubCode;
                    $clubInfo['zoneCode'] = $zoneCode;
                    $clubInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $newClub = $this->Club->add_log($clubInfo);

                    if ($newClub) {
                        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $clubInfo);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Permissions.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }

    }

    public function getClubList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $allClubList = $this->Club->query_all();

                $clubList = array();
                foreach ($allClubList as $key => $value) {
                    $clubList[$key]['clubCode'] = $value['club_code'];
                    $clubList[$key]['zoneCode'] = $value['zone_code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $clubList);
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