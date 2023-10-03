<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    private $Region;
    private $Governer;
    private $AppHelper;

    public function __construct()
    {
        $this->Region = new Region();
        $this->Governer = new Governer();
        $this->AppHelper = new AppHelper();
    }

    public function addNewRegionDetail(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $regionCode = (is_null($request->regionCode) || empty($request->regionCode)) ? "" : $request->regionCode;
        $chairPersonCode = (is_null($request->chairPersonCode) || empty($request->chairPersonCode)) ? "" : $request->chairPersonCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is reuired.");
        } else if ($regionCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Region Code is required.");
        } else if ($chairPersonCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Chair Person Code is required.");
        } else {
            try {
                
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
