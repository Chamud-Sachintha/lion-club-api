<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Activity;
use App\Models\Club;
use Illuminate\Http\Request;

class GovernerController extends Controller
{

    private $Activity;
    private $AppHelper;
    private $Club;

    public function __construct()
    {
        $this->Activity = new Activity();
        $this->AppHelper = new AppHelper();
        $this->Club = new Club();
    }

    public function getDashboardCounts(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $activityCount = $this->Activity->get_activity_count();
                $clubCount = $this->Club->get_club_count();

                $dashboardCount = array();
                $dashboardCount['activityCount'] = $activityCount;
                $dashboardCount['clubCount'] = $clubCount;

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dashboardCount);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
