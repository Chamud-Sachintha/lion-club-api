<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Mail\AddUserMail;
use App\Models\Activity;
use App\Models\ChangePassword;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivtyPointReserve;
use App\Models\Governer;
use App\Models\Region;
use App\Models\RegionChairperson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegionChairpersonController extends Controller
{
    
    private $RegionChairperson;
    private $Region;
    private $Governer;
    private $ChangePasswordLog;
    private $Club;
    private $ClubActivity;
    private $PointsReserved;
    private $AppHelper;

    public function __construct()
    {
        $this->RegionChairperson = new RegionChairperson();
        $this->Governer = new Governer();
        $this->Region = new Region();
        $this->ChangePasswordLog = new ChangePassword();
        $this->Club = new Club();
        $this->ClubActivity = new ClubActivity();
        $this->PointsReserved = new ClubActivtyPointReserve();
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
                        
                        $passwordLogInfo = array();
                        $passwordLogInfo['userEmail'] = $emailAddress;
                        $passwordLogInfo['password'] = 123;
                        $passwordLogInfo['secret'] = sha1(time());
                        $passwordLogInfo['flag'] = "RC";
                        $passwordLogInfo['createTime'] = $this->AppHelper->get_date_and_time();

                        $this->ChangePasswordLog->add_log($passwordLogInfo);

                        $details = [
                            'userRole' => 'Region Chair Person',
                            'userName' => $chairPerson->name,
                            'tempPass' => 123,
                        ];
    
                        Mail::to($emailAddress)->send(new AddUserMail($details));

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
        $reCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;
        $name = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $reUserCode = (is_null($request->reChairPersonCode) || empty($request->reChairPersonCode)) ? "" : $request->reChairPersonCode;

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
                $newChairpersonInfo['code'] = $reUserCode;
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

    public function getDashboardData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $chairPerson = $this->RegionChairperson->query_find_by_token($request_token);

                if ($chairPerson) {
                
                    $totalActivities = $this->getActivityResultDataSet($chairPerson->region_code, 99);
                    $totalRejectedActivities = $this->getActivityResultDataSet($chairPerson->region_code, 2);
                    $totalApprovedActivities = $this->getActivityResultDataSet($chairPerson->region_code, 1) + $this->getActivityResultDataSet($chairPerson->region_code, 4);
                    $totalPendingActivities = $this->getActivityResultDataSet($chairPerson->region_code, 0) + $this->getActivityResultDataSet($chairPerson->region_code, 3);

                    $dashboardData = array();
                    $dashboardData['totalActivities'] = $totalActivities;
                    $dashboardData['rejectedActivities'] = $totalRejectedActivities;
                    $dashboardData['approvedActivities'] = $totalApprovedActivities;
                    $dashboardData['pendingActivities'] = $totalPendingActivities;

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dashboardData);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invaid Token");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deleteRegionChairpersonByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $reUserCode = (is_null($request->reChairPersonCode) || empty($request->reChairPersonCode)) ? "" : $request->reChairPersonCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($reUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "User Code is required.");
        } else {

            try {

                $resp = $this->RegionChairperson->delete_by_code($reUserCode);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getRCUserCheckInfoPageTableData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $rePerson = $this->RegionChairperson->query_find_by_token($request_token);

                $totalClubList = DB::table('clubs')->select('clubs.club_code', 'clubs.zone_code')
                                                    ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                    ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                    ->where('regions.region_code', '=', $rePerson->region_code)
                                                    ->distinct('clubs.club_code')
                                                    ->get();
                
                $checkInfoPageData = array();
                foreach ($totalClubList as $key => $value) {
                    $clubRank = $this->getClubRank($value->club_code);
                    $totalActivityReported = $this->ClubActivity->find_by_club_code($value->club_code);
                    $totalActivitiesApproved = $this->ClubActivity->get_approved_count_by_club_code($value->club_code);
                    $rejectedActivityCount = $this->ClubActivity->get_rejected_count_by_club_code($value->club_code);
                    $pendingActivityCount = $this->ClubActivity->get_pending_count_by_club_code($value->club_code);
                    $holdCount = $this->ClubActivity->get_hold_count_by_club_code($value->club_code);
                    $approvedWithCorrections = $this->ClubActivity->get_approved_with_corrections_count_by_club_code($value->club_code);
                    $pointsClamed = $this->PointsReserved->get_points__by_club_code($value->club_code);

                    $checkInfoPageData[$key]['clubRank'] = $clubRank;
                    $checkInfoPageData[$key]['zoneCode'] = $value->zone_code;
                    $checkInfoPageData[$key]['clubCode'] = $value->club_code;
                    $checkInfoPageData[$key]['totalActivitiesReported'] = count($totalActivityReported);
                    $checkInfoPageData[$key]['totalActivitiesApproved'] = $totalActivitiesApproved + $approvedWithCorrections;
                    $checkInfoPageData[$key]['totalActivitiesRejected'] = $rejectedActivityCount;
                    $checkInfoPageData[$key]['pendingActivityCount'] = $pendingActivityCount + $holdCount;
                    $checkInfoPageData[$key]['pointsClamed'] = $pointsClamed;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $checkInfoPageData);

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function getActivityResultDataSet($reCode, $status) {

        $activityResultSet = null;

        try {

            if ($status == 99) {
                $activityResultSet = DB::table('club_activities')->select('*')
                                                        ->join('clubs', 'clubs.club_code', '=', 'club_activities.club_code')
                                                        ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                        ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                        ->where('regions.region_code', '=', $reCode)
                                                        ->distinct('club_activities.id')
                                                        ->count();
            } else {
                $activityResultSet = DB::table('club_activities')->select('*')
                                                        ->join('clubs', 'clubs.club_code', '=', 'club_activities.club_code')
                                                        ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                        ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                        ->where('regions.region_code', '=', $reCode)
                                                        ->where('club_activities.status', '=', $status)
                                                        ->distinct('club_activities.id')
                                                        ->count();
            }

            return $activityResultSet;
        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
        }
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
