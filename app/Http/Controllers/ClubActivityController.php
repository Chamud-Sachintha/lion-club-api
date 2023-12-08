<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Mail\AddActivity;
use App\Models\Activity;
use App\Models\ClubActivity;
use App\Models\ClubActivityDocument;
use App\Models\ClubActivityImage;
use App\Models\ClubActivtyPointReserve;
use App\Models\ClubUser;
use App\Models\ContextUser;
use App\Models\Evaluator;
use App\Models\PointTemplate;
use App\Models\ProofDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClubActivityController extends Controller
{
    private $ClubUser;
    private $AppHelper;
    private $Activity;
    private $ClubActivity;
    private $ClubActivityDocument;
    private $ClubActivityImage;
    private $ProofDocument;
    private $ClubActivityPointsReserved;
    private $PointTemplate;
    private $ContextUser;
    private $Eveluvator;

    public function __construct()
    {   
        $this->ClubUser = new ClubUser();
        $this->AppHelper = new AppHelper();
        $this->ClubActivity = new ClubActivity();
        $this->Activity = new Activity();
        $this->ClubActivityDocument = new ClubActivityDocument();
        $this->ClubActivityImage = new ClubActivityImage();
        $this->ProofDocument = new ProofDocument();
        $this->ContextUser = new ContextUser();
        $this->PointTemplate = new PointTemplate();
        $this->ClubActivityPointsReserved = new ClubActivtyPointReserve();
        $this->Eveluvator = new Evaluator();
    }

    public function addnewClubActivityRecord(request $request) {

        $request_token  = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;
        //$conditionValue =  (is_null($request->value) || empty($request->value)) ? "" : $request->value;
        $conditiontype = (is_null($request->type) || empty($request->type)) ? "" : $request->type;
        // $benificiaries = (is_null($request->beneficiaries) || empty($request->beneficiaries)) ? "" : $request->beneficiaries;
        // $memberCount = (is_null($request->memberCount) || empty($request->memberCount)) ? "" : $request->memberCount;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;
        $creator = (is_null($request->creator) || empty($request->creator)) ? "" : $request->creator;
        $extValue = (is_null($request->extValue) || empty($request->extValue)) ? "" : $request->extValue;
        $dateOfActivity = (is_null($request->dateOfActivity) || empty($request->dateOfActivity)) ? "" : $request->dateOfActivity;
        $aditionalInfo  = (is_null($request->aditionalInfo) || empty($request->aditionalInfo)) ? "" : $request->aditionalInfo;

        $documentList = $request->files;
        $imageList = $request->files;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($activityCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Code is required.");
        } else if ($creator == "") {
            return $this->AppHelper->responseMessageHandle(0, "Creator is required.");
        // } else if ($extValue == "") {
        //     return $this->AppHelper->responseMessageHandle(0, "Exact Value is required.");
        } else {
            try {
                $clubActivityInfo = array();
                $clubActivityInfo['activityCode'] = $activityCode;
                $clubActivityInfo['clubCode'] = $clubCode;
                $clubActivityInfo['type'] = $conditiontype;
                // $clubActivityInfo['value'] = $conditionValue;
                // $clubActivityInfo['benificiaries'] = $benificiaries;
                // $clubActivityInfo['memberCount'] = $memberCount;
                $clubActivityInfo['createTime'] = $this->AppHelper->get_date_and_time();
                $clubActivityInfo['creator'] = $creator;
                $clubActivityInfo['extValue'] = $extValue;
                $clubActivityInfo['dateOfActivity'] = strtotime($dateOfActivity);
                $clubActivityInfo['aditionalInfo'] = $aditionalInfo;

                $insertClubActivity = $this->ClubActivity->add_log($clubActivityInfo);

                if ($insertClubActivity) {

                    $docInfo = array();
                    foreach ($documentList as $key => $value) {
                         
                        $docInfo['activityCode'] = $insertClubActivity->id;
                        $docInfo['createTime'] = $this->AppHelper->get_date_and_time();

                        $uniqueId = uniqid();
                        $ext = $value->getClientOriginalExtension();

                        if ($ext == "pdf") {
                            $value->move(public_path('/modo/docs'), $uniqueId . '.' . $ext);
                            $docInfo['document'] = $uniqueId . '.' . $ext;

                            $this->ClubActivityDocument->add_log($docInfo);
                        } else {
                            $value->move(public_path('/modo/images'), $uniqueId . '.' . $ext);
                            $docInfo['image'] = $uniqueId . '.' . $ext;

                            $this->ClubActivityImage->add_log($docInfo);
                        }
                    }

                    $activity = $this->Activity->query_find($activityCode);
                    $creatorInfo = $this->checkUser($creator);

                    $details = [
                        'activityCode' => $activity->code,
                        'activityName' => $activity->activity_name,
                        'submitBy' => $creatorInfo->name,
                        'value' => $extValue,
                        'dateOfActivity' => $dateOfActivity
                    ];

                    $eveluvatorMails = $this->Eveluvator->get_mails();

                    Mail::to($eveluvatorMails)->send(new AddActivity($details));

                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubActivityList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $regionCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;
        $zoneCode = (is_null($request->zoneCode) || empty($request->zoneCode)) ? "" : $request->zoneCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $contextUser = $this->ContextUser->query_find_by_token($request_token);

                if (empty($contextUser)) {
                    return $this->AppHelper->responseMessageHandle(0, 'Invalid Context User Code');
                }

                $allActivityList = DB::table('club_activities')->select('club_activities.*')
                                                                ->join('clubs', 'clubs.club_code', '=', 'club_activities.club_code')
                                                                ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                                                ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                                                ->where('regions.context_user_code', '=', $contextUser->code)
                                                                ->get();

                $activityList = array();
                foreach ($allActivityList as $key => $value) {
                    $activityList[$key]['activityCode'] = $value['activity_code'];
                    $activityList[$key]['clubCode'] = $value['club_code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $activityList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAllClubActivityList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = $this->ClubActivity->query_all();

                $clubActivityList = array();
                foreach ($resp as $key => $value) {

                    $checkUser = $this->checkUser($value['creator']);
                    
                    $clubActivityList[$key]['id'] = $value['id'];
                    $clubActivityList[$key]['activityCode'] = $value['activity_code'];
                    $clubActivityList[$key]['clubCode'] = $value['club_code'];
                    $clubActivityList[$key]['status'] = $value['status'];
                    $clubActivityList[$key]['createUser'] = ["designation" => $checkUser['flag'], "name" => $checkUser['name']];
                    $clubActivityList[$key]['createTime'] = $value['create_time'];
                    $clubActivityList[$key]['dateOfActivity'] = $value['date_of_activity'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $clubActivityList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubActivityDocumentsByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $cbActivity = $this->ClubActivity->find_by_id($activityCode);
                $resp = $this->ClubActivityDocument->query_find_docs($activityCode);
                $resp2 = $this->ClubActivityImage->find_images_by_activity_code($activityCode);

                $activityDocList = array();
                foreach ($resp as $key => $value) {
                    $activityDocList[$key]['documentName'] = $value['document_name'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operatyion Complete", $activityDocList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubActivityImageListByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $resp = $this->ClubActivityImage->find_images_by_activity_code($activityCode);

                $activityImageList = array();
                foreach ($resp as $key => $value) {
                    $activityImageList[$key]['imageName'] = $value['image'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operatyion Complete", $activityImageList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubActivityInfoByActivityCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = DB::table('activities')->select('activities.*', 'club_activities.id as clubActivityId', 'club_activities.club_code', 'club_activities.create_time', 'club_activities.status', 'club_activities.type', 'club_activities.aditional_info')
                                                ->join('club_activities', 'club_activities.activity_code', '=', 'activities.code')
                                                ->where('club_activities.id', $activityCode)
                                                ->get();

                                                // print_r($resp);
                $activityInfo = array();
                $activityInfo['clubActivityId'] = $resp[0]->clubActivityId;
                $activityInfo['activityCode'] = $resp[0]->code;
                $activityInfo['clubCode'] = $resp[0]->club_code;
                $activityInfo['activityName'] = $resp[0]->activity_name;
                $activityInfo['type'] = $resp[0]->type;
                $activityInfo['status'] = $resp[0]->status;
                $activityInfo['aditionInfo'] = $resp[0]->aditional_info;

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $activityInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getClubActivityListByClubCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($clubCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Club Code is required.");
        } else {

            try {
                $resp = $this->ClubActivity->find_by_club_code($clubCode);
                
                $clubActivityList = array();
                foreach ($resp as $key => $value) {
                    $activityInfo = $this->Activity->query_find($value["activity_code"]);
                    $valueObj = $this->PointTemplate->find_by_code($activityInfo->point_template_code);

                    $f = json_decode($valueObj->value);

                    $rangeValue = null;
                    foreach ($f as $k => $v) {
                        if ($v->name == $value['type']) {
                            $rangeValue = $v->value;
                        }
                    }

                    if ($value['status'] == 1 || $value['status'] == 4) {
                        $ponits = $this->ClubActivityPointsReserved->get_points_by_activity_and_club($value['id'], $value['club_code']);
                    } else if ($value['status'] == 2) {
                        $ponits['points'] = "N/A";
                    } else {
                        $ponits['points'] = "Pending";
                    }

                    $clubActivityList[$key]['activityCode'] = $value['activity_code'];
                    $clubActivityList[$key]['clubCode'] = $value['club_code'];
                    $clubActivityList[$key]['activityName'] = $activityInfo['activity_name'];
                    $clubActivityList[$key]['status'] = $value['status'];
                    $clubActivityList[$key]['type'] = $value['type'];
                    $clubActivityList[$key]['extValue'] = $value['ext_value'];
                    $clubActivityList[$key]['createTime'] = $value['create_time'];
                    $clubActivityList[$key]['activityTime'] = $value['date_of_activity'];
                    $clubActivityList[$key]['requestedRangeValue'] = $rangeValue;
                    $clubActivityList[$key]['approvedPoints'] = $ponits['points'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $clubActivityList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getDocInfoByActivityCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $activity = $this->Activity->query_find($activityCode);

                if ($activity) {
                    $docCodeList = json_decode($activity->doc_code);

                    $docInfoArray = array();
                    foreach($docCodeList as $key => $value) {
                        
                        $document = $this->ProofDocument->find_by_code($value->value);

                        $docInfoArray[$key]['documentCode'] = $document->code;
                        $docInfoArray[$key]['documentName'] = $document->name;
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $docInfoArray);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Activity Code");
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
}
