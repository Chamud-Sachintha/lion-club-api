<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ClubActivity;
use App\Models\ClubActivityDocument;
use App\Models\ClubActivityImage;
use App\Models\ClubUser;
use App\Models\ContextUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubActivityController extends Controller
{
    private $ClubUser;
    private $AppHelper;
    private $ClubActivity;
    private $ClubActivityDocument;
    private $ClubActivityImage;
    private $ContextUser;

    public function __construct()
    {   
        $this->ClubUser = new ClubUser();
        $this->AppHelper = new AppHelper();
        $this->ClubActivity = new ClubActivity();
        $this->ClubActivityDocument = new ClubActivityDocument();
        $this->ClubActivityImage = new ClubActivityImage();
        $this->ContextUser = new ContextUser();
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
 
        $documentList = $request->files;
        $imageList = $request->files;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($activityCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Code is required.");
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

                $insertClubActivity = $this->ClubActivity->add_log($clubActivityInfo);

                if ($insertClubActivity) {

                    $docInfo = array();
                    foreach ($documentList as $key => $value) {
                         
                        $docInfo['activityCode'] = $activityCode;
                        $docInfo['createTime'] = $this->AppHelper->get_date_and_time();

                        $uniqueId = uniqid();
                        $ext = $value->getClientOriginalExtension();

                        if ($ext == "pdf") {
                            $value->move(public_path('\modo\docs'), $uniqueId . '.' . $ext);
                            $docInfo['document'] = $uniqueId . '.' . $ext;

                            $this->ClubActivityDocument->add_log($docInfo);
                        } else {
                            $value->move(public_path('\modo\images'), $uniqueId . '.' . $ext);
                            $docInfo['image'] = $uniqueId . '.' . $ext;

                            $this->ClubActivityImage->add_log($docInfo);
                        }
                    }

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
                    $clubActivityList[$key]['activityCode'] = $value['activity_code'];
                    $clubActivityList[$key]['clubCode'] = $value['club_code'];
                    $clubActivityList[$key]['createTime'] = $value['create_time'];
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
                $resp = DB::table('activities')->select('activities.*', 'club_activities.club_code', 'club_activities.create_time')
                                                ->join('club_activities', 'club_activities.activity_code', '=', 'activities.code')
                                                ->where('activities.code', $activityCode)
                                                ->get();

                                                // print_r($resp);
                $activityInfo = array();
                $activityInfo['activityCode'] = $resp[0]->code;
                $activityInfo['clubCode'] = $resp[0]->club_code;
                $activityInfo['activityName'] = $resp[0]->activity_name;

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $activityInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
