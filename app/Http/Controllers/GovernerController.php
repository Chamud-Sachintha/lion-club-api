<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Activity;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivtyPointReserve;
use App\Models\Governer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GovernerController extends Controller
{

    private $Activity;
    private $AppHelper;
    private $ClubPoint;
    private $ClubActivity;
    private $Governer;
    private $Club;

    public function __construct()
    {
        $this->Activity = new Activity();
        $this->AppHelper = new AppHelper();
        $this->ClubPoint = new ClubActivtyPointReserve();
        $this->ClubActivity = new ClubActivity();
        $this->Governer = new Governer();
        $this->Club = new Club();
    }

    public function getDashboardCounts(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $activityCount = $this->Activity->get_activity_count();
                $clubCount = $this->Club->get_club_count();

                $dashboardCount = array();
                $dashboardCount['activityCount'] = $activityCount;
                $dashboardCount['clubCount'] = $clubCount;

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dashboardCount);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubRankData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $clubRankInfo = DB::table('clubs')->select('clubs.*', 'zones.zone_code', 'regions.region_code')
                                                    ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                    ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                    ->get();

                $dataList = array();
                foreach ($clubRankInfo as $key => $value) {

                    $clubRank = $this->getClubRank($value->club_code);
                    $activityCount = $this->ClubActivity->get_activity_count_by_club_code($value->club_code);
                    $totalPoints = $this->ClubPoint->get_points__by_club_code($value->club_code);

                    $dataList[$key]['clubCode'] = $value->club_code;
                    $dataList[$key]['regionCode'] = $value->region_code;
                    $dataList[$key]['zoneCode'] = $value->zone_code;
                    $dataList[$key]['rank'] = $clubRank;
                    $dataList[$key]['activityCount'] = $activityCount;
                    $dataList[$key]['totalPoints'] = $totalPoints;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);

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
                $resp = $this->Governer->check_permission($request_token, $flag);

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

    private function getClubRank($clubCode) {

        try {
            $resp = $this->ClubPoint->get_ordered_list();

            $clubRank = 1;
            foreach ($resp as $key => $value) {
                if ($value['club_code'] == $clubCode) {
                    break;
                }

                $clubRank += 1;
            }

            return $clubRank;

        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
        }
    }
}
