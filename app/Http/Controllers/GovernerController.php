<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Activity;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivtyPointReserve;
use App\Models\ClubUser;
use App\Models\ContextUser;
use App\Models\PointTemplate;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
    private $PointTemplate;
    private $ClubActivityPointsReserved;

    public function __construct()
    {
        $this->Activity = new Activity();
        $this->AppHelper = new AppHelper();
        $this->ClubPoint = new ClubActivtyPointReserve();
        $this->ClubActivity = new ClubActivity();
        $this->ClubUser = new ClubUser();
        $this->ContextUser = new ContextUser();
        $this->Club = new Club();
        $this->PointTemplate = new PointTemplate();
        $this->ClubActivityPointsReserved = new ClubActivtyPointReserve();
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
                $totalExtValueQuery = DB::table('club_activities')->select('club_activities.ext_value as extValue', 'activities.point_template_code as templateCode')
                                                        ->join('activities', 'activities.code', '=', 'club_activities.activity_code')
                                                        ->get();

                $totalApprovedFunds = 0;
                $totalPeopleServed = 0;
                foreach($totalExtValueQuery as $key => $value) {
                    $templateNamePrefix = explode("-" ,$value->templateCode);

                    if (trim($templateNamePrefix[1]) == "C") {
                        $totalApprovedFunds += $value->extValue;
                    } else if (trim($templateNamePrefix[1]) == "P") {
                        $totalPeopleServed += $value->extValue;
                    } else {
                        
                    }
                }

                $dashboardCount = array();
                $dashboardCount['activityCount'] = $activityCount;
                $dashboardCount['clubCount'] = $clubCount;
                $dashboardCount['totalFunds'] = $totalApprovedFunds;
                $dashboardCount['totalPeopleServed'] = $totalPeopleServed;

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
                                                    ->distinct('clubs.club_code')
                                                    ->get();

                $dataList = array();
                foreach ($clubRankInfo as $key => $value) {

                    $clubRank = $this->getClubRank($value->club_code);
                    $activityCount = $this->ClubActivity->get_activity_count_by_club_code($value->club_code);
                    $totalPoints = $this->ClubPoint->get_points__by_club_code($value->club_code);
                    $activityCountEvaluvated = DB::table('club_activities')->select('*')
                                                                            ->where('club_activities.club_code' ,'=', $value->club_code)
                                                                            ->where(function($query) {
                                                                                $query->where('club_activities.status', 'like', '%' . 0 . '%')
                                                                                ->orWhere('club_activities.status', 'like', '%' . 3 . '%');
                                                                            })
                                                                            ->count();
                    $dataList[$key]['clubCode'] = $value->club_code;
                    $dataList[$key]['regionCode'] = $value->region_code;
                    $dataList[$key]['zoneCode'] = $value->zone_code;
                    $dataList[$key]['rank'] = $clubRank;
                    $dataList[$key]['activityCount'] = $activityCount;
                    $dataList[$key]['totalPoints'] = $totalPoints;
                    $dataList[$key]['activitiesToBeEvaluvated'] = $activityCountEvaluvated;
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

    public function getClubReportData(Request $request) {

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
                                                    ->distinct('clubs.club_code')
                                                    ->get();

                $dataList = array();
                foreach ($clubRankInfo as $key => $value) {

                    $clubRank = $this->getClubRank($value->club_code);
                    $activityCount = $this->ClubActivity->get_activity_count_by_club_code($value->club_code);
                    $totalPoints = $this->ClubPoint->get_points__by_club_code($value->club_code);
                    $activityCountEvaluvated = DB::table('club_activities')->select('*')
                                                                            ->where('club_activities.club_code' ,'=', $value->club_code)
                                                                            ->where(function($query) {
                                                                                $query->where('club_activities.status', 'like', '%' . 0 . '%')
                                                                                ->orWhere('club_activities.status', 'like', '%' . 3 . '%');
                                                                            })
                                                                            ->count();
                    $dataList[$key]['clubCode'] = $value->club_code;
                    $dataList[$key]['regionCode'] = $value->region_code;
                    $dataList[$key]['zoneCode'] = $value->zone_code;
                    $dataList[$key]['rank'] = $clubRank;
                    $dataList[$key]['activityCount'] = $activityCount;
                    $dataList[$key]['totalPoints'] = $totalPoints;
                    $dataList[$key]['activitiesToBeEvaluvated'] = $activityCountEvaluvated;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function exportActivityReportDataSheet(Request $request) {
        print("ssad");
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is erquired.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is erquired.");
        } else {

            try {
                $csvFileName = 'activity_report.csv';
                $filePath = public_path('exports/excel/' . $csvFileName);

                $file = fopen($filePath, 'w');

                $headers = [
                                'Region Code', 'Zone Code', 'Main Category Code', 'First Category Code', 'Second Category Code', 'Activity Code', 'Template Code', 'Authorized User',
                                'Activity Date', 'Submited Date', 'Club Code', 'Submited By', 'Exact Value', 'Type', 'Points Clamied', 'Points Approved',
                            ];
                
                fputcsv($file, $headers);

                $resp = DB::table('club_activities')->select('zones.re_code', 'zones.zone_code', 'activities.main_cat_code', 'activities.first_cat_code', 'activities.second_cat_code', 'activities.code'
                                                                , 'activities.point_template_code', 'activities.authorized_user', 'activities.create_time', 'club_activities.create_time as submited_date'
                                                                , 'club_activities.club_code', 'club_activities.creator', 'club_activities.ext_value', 'club_activities.id as clubActivityCode', 'club_activities.type')
                            ->join('activities', 'club_activities.activity_code', '=', 'activities.code')
                            ->join('clubs', 'clubs.club_code', '=', 'club_activities.club_code')
                            ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                            ->get();
                
                foreach ($resp as $row) {
                    $arrays[] =  (array) $row;
                    $valueObj = $this->PointTemplate->find_by_code($arrays[0]['point_template_code']);

                    $f = json_decode($valueObj->value);

                    $rangeValue = null;
                    foreach ($f as $k => $v) {
                        if ($v->name == $arrays[0]['type']) {
                            $rangeValue = $v->value;
                        }
                    }

                    $ponits = $this->ClubActivityPointsReserved->get_points_by_activity_and_club($arrays[0]["clubActivityCode"], $arrays[0]['club_code']);

                    $arrays[0]["points_claimed"] = $rangeValue;
                    $arrays[0]["points_approved"] = $ponits['points'];

                    $filtered = Arr::except($arrays[0], ['clubActivityCode']);
                    fputcsv($file, $filtered);
                }

                fclose($file);

                return response()->download($filePath, $csvFileName)->deleteFileAfterSend(true);

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function exportClubReportDataSheet(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $csvFileName = 'club_report.csv';
                $filePath = public_path('exports/excel/' . $csvFileName);

                $file = fopen($filePath, 'w');

                $headers = [
                                'Rank', 'Region Code', 'Zone Code', 'Club Code', 'Region Chair Person', 'Zone Chair Person', 'Club Users', 'Total Activities',
                                'No Of Activities to be Evaluated', 'Total Claimed Marks', 'Total Marks Approved'
                            ];
                
                fputcsv($file, $headers);

                $clubRankInfo = DB::table('clubs')->select('clubs.*', 'zones.zone_code', 'regions.region_code')
                                                    ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                    ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                    ->distinct('clubs.club_code')
                                                    ->get();

                $dataList = array();
                foreach ($clubRankInfo as $key => $value) {

                    $clubRank = $this->getClubRank($value->club_code);
                    $activityCount = $this->ClubActivity->get_activity_count_by_club_code($value->club_code);
                    $totalPoints = $this->ClubPoint->get_points__by_club_code($value->club_code);
                    $activityCountEvaluvated = DB::table('club_activities')->select('*')
                                                                            ->where('club_activities.club_code' ,'=', $value->club_code)
                                                                            ->where(function($query) {
                                                                                $query->where('club_activities.status', 'like', '%' . 0 . '%')
                                                                                ->orWhere('club_activities.status', 'like', '%' . 3 . '%');
                                                                            })
                                                                            ->count();
                    
                    $arrays[] =  (array) $clubRankInfo[$key];
                    fputcsv($file, $arrays[0]);
                }

                fclose($file);

                return response()->download($filePath, $csvFileName)->deleteFileAfterSend(true);

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
            // $resp = $this->ClubPoint->get_ordered_list();

            $resp = $this->Club->get_club_list_by_points_order();

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
