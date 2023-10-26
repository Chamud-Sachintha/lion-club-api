<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Evaluator;
use App\Models\Governer;
use Illuminate\Http\Request;

class EvaluatorController extends Controller
{
    private $Evaluator;
    private $Governer;
    private $AppHelper;

    public function __construct()
    {
        $this->Evaluator = new Evaluator();
        $this->Governer = new Governer();
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

                if ($userPerm == true) {
                    $evaluatorInfo['code'] = $evaluatorCode;
                    $evaluatorInfo['name'] = $fullName;
                    $evaluatorInfo['email'] = $emailAddress;
                    $evaluatorInfo['password'] = 123;
                    $evaluatorInfo['createTime'] = $this->AppHelper->day_time();

                    $evaluator = $this->Evaluator->add_log($evaluatorInfo);

                    if ($evaluator) {
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

    public function getClubActivitylist(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {

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
        $userCode = (is_null($request->evaluvatorCode) || empty($request->evaluvatorCode)) ? "" : $request->evaluvatorCode;
        $name = (is_null($request->name) || empty($request->name)) ? "" : $request->name;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokenm is requiiored");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokenm is requiiored");
        } else if ($userCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokenm is requiiored");
        } else if ($name == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokenm is requiiored");
        } else if ($email == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tokenm is requiiored");
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
