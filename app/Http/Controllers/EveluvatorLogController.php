<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\EveluvatorLog;
use Illuminate\Http\Request;

class EveluvatorLogController extends Controller
{
    private $AppHelper;
    private $EveluvationLog;
    
    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->EveluvationLog = new EveluvatorLog();
    }

    public function getEveluvatorLogList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = $this->EveluvationLog->get_all();

                $dataList = array();
                foreach ($resp as $key => $value) {
                    $dataList[$key]['name'] = $value['name'];
                    $dataList[$key]['activityCode'] = $value['activity'];
                    $dataList[$key]['clubCode'] = $value['club_code'];
                    $dataList[$key]['comment'] = $value['comment'];
                    $dataList[$key]['status'] = $value['status'];
                    $dataList[$key]['requestedRange'] = $value['requested_range'];
                    $dataList[$key]['requestedPoints'] = $value['requested_points'];
                    $dataList[$key]['claimedRange'] = $value['claimed_range'];
                    $dataList[$key]['claimedPoints'] = $value['claimed_points'];
                    $dataList[$key]['eveluvatedDate'] = $value['eveluvated_date'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
