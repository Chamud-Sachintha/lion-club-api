<?php

namespace App\Http\Middleware;

use App\Helpers\AppHelper;
use App\Models\ClubUser;
use App\Models\ContextUser;
use App\Models\Evaluator;
use App\Models\Governer;
use App\Models\RegionChairperson;
use App\Models\ZonalChairPerson;
use Closure;
use Illuminate\Http\Request;

class AuthTokenMiddleware
{

    private $Governer;
    private $ClubUser;
    private $ContextUser;
    private $Evaluvator;
    private $RegionChairPerson;
    private $ZonalChairPerson;
    private $AppHelper;

    public function __construct()
    {
        $this->Governer = new Governer();
        $this->ClubUser = new ClubUser();
        $this->ContextUser = new ContextUser();
        $this->Evaluvator = new Evaluator();
        $this->RegionChairPerson = new RegionChairperson();
        $this->ZonalChairPerson = new ZonalChairPerson();
        $this->AppHelper = new AppHelper();
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (is_null($request->token) || empty($request->token) && (is_null($request->flag) || empty($request->flag))) {
                return response()->json(['error' => 'Unauthorized'], 401);
            } else {

                $user = null;

                if ($request->flag == "G") {
                    $user = $this->Governer->query_find_by_token($request->token);
                } else if ($request->flag == "CU") {
                    $user = $this->ClubUser->query_find_by_token($request->token);
                } else if ($request->flag == "CNTU") {
                    $user = $this->ContextUser->query_find_by_token($request->token);
                } else if ($request->flag == "E") {
                    $user = $this->Evaluvator->query_find_by_token($request->token);
                } else if ($request->flag == "RC") {
                    $user = $this->RegionChairPerson->query_find_by_token($request->token);
                } else if ($request->flag == "ZC") {
                    $user = $this->ZonalChairPerson->query_find_by_token($request->token);
                } else {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                $yesterday = $this->AppHelper->day_time() - 86400;

                if (empty($user) || ($user['login_time'] < $yesterday)) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return $next($request);
    }
}
