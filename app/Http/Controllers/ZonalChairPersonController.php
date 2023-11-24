<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Mail\AddUserMail;
use App\Models\ChangePassword;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivtyPointReserve;
use App\Models\Governer;
use App\Models\ZonalChairPerson;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ZonalChairPersonController extends Controller
{
    private $ZonalChairPerson;
    private $Governer;
    private $Zone;
    private $ChangePasswordLog;
    private $Club;
    private $ClubActivity;
    private $PointsReserved;
    private $AppHelper;

    public function __construct()
    {
        $this->ZonalChairPerson = new ZonalChairPerson();
        $this->Governer = new Governer();
        $this->Zone = new Zone();
        $this->ChangePasswordLog = new ChangePassword();
        $this->Club = new Club();
        $this->ClubActivity = new ClubActivity();
        $this->PointsReserved = new ClubActivtyPointReserve();
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

                        $passwordLogInfo = array();
                        $passwordLogInfo['userEmail'] = $emailAddress;
                        $passwordLogInfo['password'] = 123;
                        $passwordLogInfo['secret'] = sha1(time());
                        $passwordLogInfo['flag'] = "ZC";
                        $passwordLogInfo['createTime'] = $this->AppHelper->get_date_and_time();

                        $this->ChangePasswordLog->add_log($passwordLogInfo);

                        $details = [
                            'userRole' => 'Zone Chair Person',
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

    public function getZCUserCheckInfoPageTableData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $zcPerson = $this->ZonalChairPerson->query_find_by_token($request_token);

                $totalClubList = DB::table('clubs')->select('clubs.club_code', 'clubs.zone_code')
                                                    ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                    ->where('zones.zone_code', '=', $zcPerson->zone_code)
                                                    ->get();
                
                $checkInfoPageData = array();
                foreach ($totalClubList as $key => $value) {
                    $clubRank = $this->getClubRank($value->club_code);
                    $totalActivityReported = $this->ClubActivity->find_by_club_code($value->club_code);
                    $totalActivitiesApproved = $this->ClubActivity->get_approved_count_by_club_code($value->club_code);
                    $rejectedActivityCount = $this->ClubActivity->get_rejected_count_by_club_code($value->club_code);
                    $pendingActivityCount = $this->ClubActivity->get_pending_count_by_club_code($value->club_code);
                    $pointsClamed = $this->PointsReserved->get_points__by_club_code($value->club_code);

                    $checkInfoPageData[$key]['clubRank'] = $clubRank;
                    // $checkInfoPageData[$key]['zoneCode'] = $value->zone_code;
                    $checkInfoPageData[$key]['clubCode'] = $value->club_code;
                    $checkInfoPageData[$key]['totalActivitiesReported'] = count($totalActivityReported);
                    $checkInfoPageData[$key]['totalActivitiesApproved'] = $totalActivitiesApproved;
                    $checkInfoPageData[$key]['totalActivitiesRejected'] = $rejectedActivityCount;
                    $checkInfoPageData[$key]['pendingActivityCount'] = $pendingActivityCount;
                    $checkInfoPageData[$key]['pointsClamed'] = $pointsClamed;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $checkInfoPageData);

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
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
