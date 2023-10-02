<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private $Governer;
    private $AppHelper;

    public function __construct()
    {
        $this->Governer = new Governer();
        $this->AppHelper = new AppHelper();
    }

    public function checkMenuPermission(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {   
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $user = null;

                if ($flag == "G") {
                    $user = $this->Governer->check_permission($request_token, $flag);
                } else if ($flag == "RC") {

                } else if ($flag == "ZC") {

                } else if ($flag == "CNTU") {

                } else if ($flag == "CU") {

                } else if ($flag == "E") {

                } else {

                }

                if (!empty($user)) {
                    return $this->AppHelper->responseMessageHandle(1, "Permission Granted.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Permission Not Granted.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function authenticateUser(Request $request) {

        $username = (is_null($request->username) || empty($request->username)) ? "" : $request->username;
        $password = (is_null($request->password) || empty($request->password)) ? "" : $request->password;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($username == "") {
            return $this->AppHelper->responseMessageHandle(0, "Username is required.");
        } else if ($password == "") {
            return $this->AppHelper->responseMessageHandle(0, "Password is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is Required.");
        } else {
            try {
                $authInfo = array();
                $authenticateUser = null;

                $authInfo['userName'] = $username;
                $authInfo['password'] = $password;

                if ($flag == "G") {
                    $authenticateUser = $this->authenticateGoverner($authInfo);
                } else if ($flag == "RC") {

                } else if ($flag == "ZC") {

                } else if ($flag == "CNTU") {

                } else if ($flag == "CU") {

                } else if ($flag == "E") {

                } else {

                }

                return $authenticateUser;
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function authenticateGoverner($authInfo) {

        $loginInfo = array();
        $verify_username = $this->Governer->verify_email($authInfo['userName']);

        if (!empty($verify_username)) {
            if (Hash::check($authInfo['password'], $verify_username['password'])) {
                $loginInfo['id'] = $verify_username['id'];
                $loginInfo['firstName'] = $verify_username['first_name'];
                $loginInfo['lastName'] = $verify_username['last_name'];
                $loginInfo['email'] = $verify_username['email'];

                $token = $this->AppHelper->generateAuthToken($verify_username);

                $loginInfo['userRole'] = $verify_username['flag'];

                $tokenInfo = array();
                $tokenInfo['token'] = $token;
                $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                $token_time = $this->Governer->update_login_token($verify_username['id'], $tokenInfo);

                return $this->AppHelper->responseEntityHandle(1, "Operation Successfully.", $loginInfo, $token);
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
            }
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
        }
    }

    private function authenticateRegionChairperson($authInfo) {

    }

    private function authenticateZoneChairPerson($authInfo) {

    }

    private function authenticateContextUser($authInfo) {

    }

    private function authenticateClubUser($authInfo) {

    }
}
