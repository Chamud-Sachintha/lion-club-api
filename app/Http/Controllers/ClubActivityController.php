<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ClubActivity;
use App\Models\ClubActivityDocument;
use App\Models\ClubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubActivityController extends Controller
{
    private $ClubUser;
    private $AppHelper;
    private $ClubActivity;
    private $ClubActivityDocument;

    public function __construct()
    {   
        $this->ClubUser = new ClubUser();
        $this->AppHelper = new AppHelper();
        $this->ClubActivity = new ClubActivity();
        $this->ClubActivityDocument = new ClubActivityDocument();
    }

    public function addnewClubActivityRecord(request $request) {
        // dd(count($request->files));
        $request_token  = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;
        $activityCost =  (is_null($request->activityCost) || empty($request->activityCost)) ? "" : $request->activityCost;
        $benificiaries = (is_null($request->beneficiaries) || empty($request->beneficiaries)) ? "" : $request->beneficiaries;
        $memberCount = (is_null($request->memberCount) || empty($request->memberCount)) ? "" : $request->memberCount;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;

        $documentList = $request->files;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($activityCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Code is required.");
        } else if ($activityCost == "") {
            return $this->AppHelper->responseMessageHandle(0, "Cost is required.");
        } else if ($benificiaries == "") {
            return $this->AppHelper->responseMessageHandle(0, "Benificiaries is required.");
        } else {
            try {
                $clubActivityInfo = array();
                $clubActivityInfo['activityCode'] = $activityCode;
                $clubActivityInfo['clubCode'] = $clubCode;
                $clubActivityInfo['cost'] = $activityCost;
                $clubActivityInfo['benificiaries'] = $benificiaries;
                $clubActivityInfo['memberCount'] = $memberCount;
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

    private function addDocumentRecordForUser($documentInfo) {

    }
}
