<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ContextUser;
use App\Models\Governer;
use Illuminate\Http\Request;

class ContextUserController extends Controller
{
    private $ContextUser;
    private $Governer;
    private $AppHelper;

    public function __construct()
    {
        $this->ContextUser = new ContextUser();
        $this->Governer = new Governer();
        $this->AppHelper = new AppHelper();
    }

    public function addNewContextUser(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $contextUserCode = (is_null($request->contextUserCode) || empty($request->contextUserCode)) ? "" : $request->contextUserCode;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $emailAddress = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($contextUserCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Context User Code is required.");
        } else if ($fullName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        } else if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else {
            try {
                $contextUserInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                if ($userPerm == true) {
                    $contextUserInfo['code'] = $contextUserCode;
                    $contextUserInfo['name'] = $fullName;
                    $contextUserInfo['email'] = $emailAddress;
                    $contextUserInfo['password'] = 123;
                    $contextUserInfo['createTime'] = $this->AppHelper->day_time();

                    $contextUser = $this->ContextUser->add_log($contextUserInfo);

                    if ($contextUser) {
                        return $this->AppHelper->responseEntityHandle(1, "Chair Person Created.", $contextUser);
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

    public function getContextUserList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $allContextUserList = $this->ContextUser->query_all();

                $contextUserList = array();
                foreach ($allContextUserList as $key => $value) {
                    $contextUserList[$key]['contextUserCode'] = $value['code'];
                    $contextUserList[$key]['fullName'] = $value['name'];
                    $contextUserList[$key]['email'] = $value['email'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $contextUserList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAvailableClubList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $contextuserCode = $this->Contextuser->query_find_by_token($request_token);

                if (empty($contextUser)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Context UserCode.");
                }

                $clubList = DB::table('clubs', 'clubs.*')
                                    ->join('zones', 'zones.zone_code', '=', 'clubs.zone_code')
                                    ->join('regions', 'regions.region_code', '=', 'zones.re_code')
                                    ->where('regions.context_user_code', '=', $contextUserCode->code)
                                    ->get();

                $availableClubList = array();
                foreach ($clubList as $key => $value) {
                    $availableClubList[$key]['clubCode'] = $value['code'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $availableClubList);
            } catch(\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function feedClubActivity(Request $request) {

        $request_token= (is_null($request->token)|| empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $clubCode = (is_null($request->clubCode) || empty($request->clubCode)) ? "" : $request->clubCode;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;
        $conditionValue =  (is_null($request->value) || empty($request->value)) ? "" : $request->value;
        $conditiontype = (is_null($request->type) || empty($request->type)) ? "" : $request->type;
        $documentList = $request->files;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {   
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }else if ($clubCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
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
