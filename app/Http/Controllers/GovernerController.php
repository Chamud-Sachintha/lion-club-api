<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Activity;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivtyPointReserve;
use App\Models\ClubUser;
use App\Models\ContextUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GovernerController extends Controller
{

    private $Activity;
    private $AppHelper;
    private $ClubPoint;
    private $ClubActivity;
    private $ClubUser;
    private $ContextUser;
    private $Club;

    public function __construct()
    {
        $this->Activity = new Activity();
        $this->AppHelper = new AppHelper();
        $this->ClubPoint = new ClubActivtyPointReserve();
        $this->ClubActivity = new ClubActivity();
        $this->ClubUser = new ClubUser();
        $this->ContextUser = new ContextUser();
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

    public function getGovReportTableData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = DB::table('club_activities')->select('club_activities.*', 'activities.activity_name', 'activities.create_time as activity_date', 'clubs.zone_code')
                                                    ->join('activities', 'club_activities.activity_code', '=', 'activities.code')
                                                    ->join('clubs', 'clubs.club_code', '=', 'club_activities.club_code')
                                                    ->get();

                $reportDataList = array();
                foreach($resp as $key => $value) {

                    $user = $this->checkUser($value->creator);

                    $reportDataList[$key]['activityCode'] = $value->activity_code;
                    $reportDataList[$key]['activityName'] = $value->activity_name;
                    $reportDataList[$key]['activityDate'] = $value->activity_date;
                    $reportDataList[$key]['submitDate'] = $value->create_time;
                    $reportDataList[$key]['extValue'] = $value->ext_value;
                    $reportDataList[$key]['submitBy'] = $user['name'];
                    $reportDataList[$key]['zoneCode'] = $value->zone_code;
                    $reportDataList[$key]['clubCode'] = $value->club_code;
                }

                return $this->AppHelper->responseEntityHandle(1, "Opereation Complete", $reportDataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        } 
    }

    private function checkUser($userCode) {

        $role = null;

        $clubUser = $this->ClubUser->find_by_code($userCode);
        $contextUser = $this->ContextUser->find_by_code($userCode);

        if ($clubUser) {
            $role = $clubUser;
        } else {
            $role = $contextUser;
        }

        return $role;
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
