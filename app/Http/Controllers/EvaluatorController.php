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
