<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Mail\EvaluvateActivity;
use App\Models\Activity;
use App\Models\ChangePassword;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivtyPointReserve;
use App\Models\ClubUser;
use App\Models\ContextUser;
use App\Models\Evaluator;
use App\Models\Governer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EvaluatorController extends Controller
{
    private $Evaluator;
    private $Governer;
    private $ClubActivity;
    private $Activity;
    private $ClubActivityPointReserve;
    private $ClubUser;
    private $ContextUser;
    private $ChangePasswordLog;
    private $Club;
    private $AppHelper;

    public function __construct()
    {
        $this->Evaluator = new Evaluator();
        $this->Governer = new Governer();
        $this->ClubActivity = new ClubActivity();
        $this->Activity = new Activity();
        $this->ClubActivityPointReserve = new ClubActivtyPointReserve();
        $this->ClubUser = new ClubUser();
        $this->ContextUser = new ContextUser();
        $this->ChangePasswordLog = new ChangePassword();
        $this->Club = new Club();
        $this->AppHelper = new AppHelper();
    }

    public function addNewEvaluvator(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $evaluatorCode = (is_null($request->evaluatorCode) || empty($request->evaluatorCode)) ? "" : $request->evaluatorCode;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $emailAddress = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($evaluatorCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Chairperson Code is required.");
        } else if ($fullName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        } else if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else {
            try {
                $evaluatorInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                $validateUser = $this->Evaluator->verify_email($emailAddress);

                if (!empty($validateUser)) {
                    return $this->AppHelper->responseMessageHandle(0, "User Already Exist.");
                }

                if ($userPerm == true) {
                    $evaluatorInfo['code'] = $evaluatorCode;
                    $evaluatorInfo['name'] = $fullName;
                    $evaluatorInfo['email'] = $emailAddress;
                    $evaluatorInfo['password'] = 123;
                    $evaluatorInfo['createTime'] = $this->AppHelper->day_time();

                    $evaluator = $this->Evaluator->add_log($evaluatorInfo);

                    if ($evaluator) {

                        $passwordLogInfo = array();
                        $passwordLogInfo['userEmail'] = $emailAddress;
                        $passwordLogInfo['password'] = 123;
                        $passwordLogInfo['secret'] = sha1(time());
                        $passwordLogInfo['flag'] = "E";
                        $passwordLogInfo['createTime'] = $this->AppHelper->get_date_and_time();

                        $this->ChangePasswordLog->add_log($passwordLogInfo);


                        return $this->AppHelper->responseEntityHandle(1, "Chair Person Created.", $evaluator);
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

    public function getEvaluvatorUserList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = $this->Evaluator->query_all();

                $evaluvatorList = array();
                foreach ($resp as $key => $value) {
                    $evaluvatorList[$key]['evaluatorCode'] = $value['code'];
                    $evaluvatorList[$key]['fullName'] = $value['name'];
                    $evaluvatorList[$key]['email'] = $value['email'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $evaluvatorList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateClubactivityConditionValue(Request $request) {
        
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;
        $activityStatus = (is_null($request->status) || empty($request->status)) ? "" : $request->status;
        $conditionType = (is_null($request->conditionType) || empty($request->conditionType)) ? "" : $request->conditionType;
        $comment = (is_null($request->comment) || empty($request->comment)) ? "" : $request->comment;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($activityCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($activityStatus == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {

                $info = array();
                $info['clubActivityCode'] = $activityCode;
                $info['status'] = $activityStatus;
                $info['comment'] = $comment;

                $cbActivity = $this->ClubActivity->find_by_id($activityCode);

                if ($cbActivity->status == "1" || $cbActivity->status == "4") {
                    return $this->AppHelper->responseMessageHandle(0, "Already Approved.");
                }

                $updateStatus = $this->ClubActivity->update_status_by_id($info);

                if ($updateStatus ) {

                    if ($activityStatus != "1" && $activityStatus != "4") {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    }
                    
                    $activity = $this->Activity->query_find($cbActivity->activity_code);

                    $templateValueList = DB::table('club_activities')->select('point_templates.value as valueList')
                                                                    ->join('activities', 'club_activities.activity_code', '=', 'activities.code')
                                                                    ->join('point_templates', 'activities.point_template_code', '=', 'point_templates.code')
                                                                    ->where('point_templates.code', '=', $activity->point_template_code)
                                                                    ->get();

                    $decodeValueList = json_decode($templateValueList[0]->valueList);

                    $pointInfo = array();
                    foreach ($decodeValueList as $key => $value) {
                         
                        if ($value->name == $conditionType) {

                            $club = $this->Club->find_by_club_code($cbActivity->club_code);

                            $pointInfo['clubActivityCode'] = $activityCode;
                            $pointInfo['clubCode'] = $cbActivity->club_code;
                            $pointInfo['points'] = $value->value;
                            $pointInfo['createTime'] = $this->AppHelper->get_date_and_time();

                            $this->ClubActivityPointReserve->add_log($pointInfo);

                            // add points of activity to current value of points of the club
                            // print_r($club);
                            $updatedPoints = $club->total_points + $value->value;

                            $clubPointsInfo = array();
                            $clubPointsInfo['clubCode'] = $cbActivity->club_code;
                            $clubPointsInfo['updatedPoints'] = $updatedPoints;

                            $resp1 = $this->Club->update_club_points($clubPointsInfo);

                            break;
                        }
                    }

                    $creatorInfo = $this->checkUser($cbActivity->creator);

                    $details = array();
                    $details['activityCode'] = $activityCode;
                    $details['activityName'] = $activity->activity_name;
                    $details['submitBy'] = $creatorInfo->name;
                    $details['points'] = $pointInfo['points'];
                    $details['value'] = $cbActivity->ext_value;
                    $details['comment'] = $comment;

                    if ($activityStatus == 1) {
                        $details['status'] = "Approved";
                    } else {
                        $details['status'] = "Rejected";
                    }

                    Mail::to($creatorInfo->email)->send(new EvaluvateActivity($details));

                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occurecd.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getEvaluvatorInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $userCode = (is_null($request->evaluvatorCode) || empty($request->evaluvatorCode)) ? "" : $request->evaluvatorCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($userCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "User Code is required.");
        } else {

            try {
                $userInfo = array();
                $evaluvatorUser = $this->Evaluator->finc_by_code($userCode);

                $userInfo['code'] = $evaluvatorUser['code'];
                $userInfo['name'] = $evaluvatorUser['name'];
                $userInfo['email'] = $evaluvatorUser['email'];

                return $this->AppHelper->responseEntityHandle(1, "Opretaion complete", $userInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateEvaluvatorUserByCode(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $userCode = (is_null($request->evaluatorCode) || empty($request->evaluatorCode)) ? "" : $request->evaluatorCode;
        $name = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is requiiored");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is requiiored");
        } else if ($userCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "User Code is requiiored");
        } else if ($name == "") {
            return $this->AppHelper->responseMessageHandle(0, "Name is requiiored");
        } else if ($email == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is requiiored");
        } else {

            try {
                $newEvaluvatorInfo  = array();
                $newEvaluvatorInfo['code'] = $userCode;
                $newEvaluvatorInfo['name'] = $name;
                $newEvaluvatorInfo['email'] = $email;

                $updateUser = $this->Evaluator->update_evaluvator_by_code($newEvaluvatorInfo);

                if ($updateUser) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle("0", "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getEvaluvatorDashboardData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required");
        } else {

            try {
                $totalActivites = $this->ClubActivity->get_activity_count();
                $approvedCount = $this->ClubActivity->get_approved_acivity_count();
                $pendingCount = $this->ClubActivity->get_pending_acivity_count();
                $rejectedActivites = $this->ClubActivity->get_rejected_activity_count();
                $holdCount = $this->ClubActivity->get_hold_acivity_count();
                $approvedWithCorrections = $this->ClubActivity->get_approved_aith_corrections_acivity_count();

                $dashboardData = array();
                $dashboardData['totalActivities'] = $totalActivites;
                $dashboardData['approvedCount'] = $approvedCount + $approvedWithCorrections;
                $dashboardData['pendingCount'] = $pendingCount + $holdCount;
                $dashboardData['rejectedCount'] = $rejectedActivites;

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dashboardData);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getEveluvatorDashboardDataTableData(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = $this->ClubActivity->query_all();

                $dataList = array();
                foreach($resp as $key => $value) {

                    $totalCount = $this->ClubActivity->get_total_count_by_club_code($value['club_code']);
                    $approvedCount = $this->ClubActivity->get_approved_count_by_club_code($value['club_code']);
                    $rejectedCount = $this->ClubActivity->get_rejected_count_by_club_code($value['club_code']);
                    $pendingCount = $this->ClubActivity->get_pending_count_by_club_code($value['club_code']);
                    $holdCount = $this->ClubActivity->get_hold_count_by_club_code($value['club_code']);
                    $aprovedWithCorrections = $this->ClubActivity->get_approved_with_corrections_count_by_club_code($value['club_code']);

                    $dataList[$key]['clubCode'] = $value['club_code'];
                    $dataList[$key]['totalCount'] = $totalCount;
                    $dataList[$key]['pendingCount'] = $pendingCount + $holdCount;
                    $dataList[$key]['approvedCount'] = $approvedCount + $aprovedWithCorrections;
                    $dataList[$key]['rejectedCount'] = $rejectedCount;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function filterClubActivities(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $reCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;
        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = null;

                if ($reCode != "" && $zoneCode == "") {
                    $resp = DB::table('club_activities')->select('club_activities.*','regions.region_code')
                                                        ->join('clubs', 'clubs.club_code', '=', 'club_activities.club_code')
                                                        ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                        ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                        ->where('regions.region_code', '=', $reCode)
                                                        ->get();
                } else if ($reCode != "" && $zoneCode != "") {
                    $resp = DB::table('club_activities')->select('club_activities.*')
                                                        ->join('clubs', 'clubs.club_code', '=', 'club_activities.club_code')
                                                        ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                        ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                        ->where('regions.region_code', '=', $reCode)
                                                        ->where('zones.zone_code', '=', $zoneCode)
                                                        ->get();
                } else {
                    $resp = DB::table('club_activities')->select('club_activities.*')
                                                        ->get();
                }

                $clubActivityList = array();
                foreach ($resp as $key => $value) {
                    $clubActivityList[$key]['activityCode'] = $value->activity_code;
                    $clubActivityList[$key]['clubCode'] = $value->club_code;
                    $clubActivityList[$key]['status'] = $value->status;
                    $clubActivityList[$key]['createTime'] = $value->create_time;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $clubActivityList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deleteUserByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $evaluvatorCode = (is_null($request->evaluvatorCode) || empty($request->evaluvatorCode)) ? "" : $request->evaluvatorCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($evaluvatorCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "User Code is required.");
        } else {

            try {
                $resp = $this->Evaluator->delete_by_code($evaluvatorCode);

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
