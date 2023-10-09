<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    private $AppHelper;
    private $Governer;
    private $Activity;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Governer = new Governer();
    }

    public function addNewActivity(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Toekn is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $userPerm = $this->checkPermission($request_token, $flag);

                if ($userPerm == true) {
                    
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Permission.");
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
