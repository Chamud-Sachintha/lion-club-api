<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ClubActivity;
use App\Models\ClubActivityDocument;
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
    private $ContextUser;

    public function __construct()
    {   
        $this->ClubUser = new ClubUser();
        $this->AppHelper = new AppHelper();
        $this->ClubActivity = new ClubActivity();
        $this->ClubActivityDocument = new ClubActivityDocument();
        $this->ContextUser = new ContextUser();
    }

    public function addnewClubActivityRecord(request $request) {

        $request_token  = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;
        $conditionValue =  (is_null($request->value) || empty($request->value)) ? "" : $request->value;
        $conditiontype = (is_null($request->type) || empty($request->type)) ? "" : $request->type;
        // $benificiaries = (is_null($request->beneficiaries) || empty($request->beneficiaries)) ? "" : $request->beneficiaries;
        // $memberCount = (is_null($request->memberCount) || empty($request->memberCount)) ? "" : $request->memberCount;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;
 
        $documentList = $request->files;

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
                $clubActivityInfo['value'] = $conditionValue;
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
                        $value->move(public_path('\modo\images'), $uniqueId . '.' . $ext);

                        $docInfo['document'] = $uniqueId . '.' . $ext;

                        $this->ClubActivityDocument->add_log($docInfo);
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
}
