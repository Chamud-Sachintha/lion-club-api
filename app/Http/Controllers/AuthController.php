<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ChangePassword;
use App\Models\ClubUser;
use App\Models\ContextUser;
use App\Models\Evaluator;
use App\Models\Governer;
use App\Models\RegionChairperson;
use App\Models\ZonalChairPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private $Governer;
    private $RegionChairPerson;
    private $ZonalChairPerson;
    private $ChangePasswordLog;
    private $ContextUser;
    private $ClubUser;
    private $Evaluator;
    private $AppHelper;

    public function __construct()
    {
        $this->Governer = new Governer();
        $this->RegionChairPerson = new RegionChairperson();
        $this->ZonalChairPerson = new ZonalChairPerson();
        $this->ContextUser = new ContextUser();
        $this->ChangePasswordLog = new ChangePassword();
        $this->ClubUser = new ClubUser();
        $this->Evaluator = new Evaluator();
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
                    $user = $this->RegionChairPerson->check_permission($request_token, $flag);
                } else if ($flag == "ZC") {
                    $user = $this->ZonalChairPerson->check_permission($request_token, $flag);
                } else if ($flag == "CNTU") {
                    $user = $this->ContextUser->check_permission($request_token, $flag);
                } else if ($flag == "CU") {
                    $user = $this->ClubUser->check_permission($request_token, $flag);
                } else if ($flag == "E") {
                    $user = $this->Evaluator->check_permission($request_token, $flag);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid User Code.");
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
                    $authenticateUser = $this->authenticateRegionChairperson($authInfo);
                } else if ($flag == "ZC") {
                    $authenticateUser = $this->authenticateZoneChairPerson($authInfo);
                } else if ($flag == "CNTU") {
                    $authenticateUser = $this->authenticateContextUser($authInfo);
                } else if ($flag == "CU") {
                    $authenticateUser = $this->authenticateClubUser($authInfo);
                } else if ($flag == "E") {
                    $authenticateUser = $this->authenticateEvaluator($authInfo);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid User Role.");
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

        $loginInfo = array();
        $verify_user = $this->RegionChairPerson->verify_email($authInfo['userName']);

        $resetPwInfo = array();
        $resetPwInfo['email'] = $authInfo['userName'];

        $checkIsReset = $this->ChangePasswordLog->query_find($resetPwInfo);

        if ($checkIsReset) {
            return $this->AppHelper->responseEntityHandle(2, "Operation Complete", $checkIsReset);
        }

        if (!empty($verify_user)) {
            if (Hash::check($authInfo['password'], $verify_user['password'])) {
                $loginInfo['id'] = $verify_user['id'];
                $loginInfo['code'] = $verify_user['code'];
                $loginInfo['fullName'] = $verify_user['name'];
                $loginInfo['email'] = $verify_user['email'];
                $loginInfo['userCode'] = $verify_user['code'];

                $token = $this->AppHelper->generateAuthToken($verify_user);

                $loginInfo['userRole'] = $verify_user['flag'];

                $tokenInfo = array();
                $tokenInfo['token'] = $token;
                $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                $this->RegionChairPerson->update_login_token($verify_user['id'], $tokenInfo);

                return $this->AppHelper->responseEntityHandle(1, "Operation Successfully.", $loginInfo, $token);
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
            }
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
        }
    }

    private function authenticateZoneChairPerson($authInfo) {

        $loginInfo = array();
        $verify_user = $this->ZonalChairPerson->verify_email($authInfo['userName']);

        if (!empty($verify_user)) {
            if (Hash::check($authInfo['password'], $verify_user['password'])) {
                $loginInfo['id'] = $verify_user['id'];
                $loginInfo['code'] = $verify_user['code'];
                $loginInfo['fullName'] = $verify_user['name'];
                $loginInfo['email'] = $verify_user['email'];
                $loginInfo['userCode'] = $verify_user['code'];

                $token = $this->AppHelper->generateAuthToken($verify_user);

                $loginInfo['userRole'] = $verify_user['flag'];

                $tokenInfo = array();
                $tokenInfo['token'] = $token;
                $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                $this->ZonalChairPerson->update_login_token($verify_user['id'], $tokenInfo);

                return $this->AppHelper->responseEntityHandle(1, "Operation Successfully.", $loginInfo, $token);
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
            }
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
        }
    }

    private function authenticateContextUser($authInfo) {

        $loginInfo = array();
        $verify_user = $this->ContextUser->verify_email($authInfo['userName']);

        if (!empty($verify_user)) {
            if (Hash::check($authInfo['password'], $verify_user['password'])) {
                $loginInfo['id'] = $verify_user['id'];
                $loginInfo['code'] = $verify_user['code'];
                $loginInfo['fullName'] = $verify_user['name'];
                $loginInfo['email'] = $verify_user['email'];
                $loginInfo['userCode'] = $verify_user['code'];

                $token = $this->AppHelper->generateAuthToken($verify_user);

                $loginInfo['userRole'] = $verify_user['flag'];

                $tokenInfo = array();
                $tokenInfo['token'] = $token;
                $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                $this->ContextUser->update_login_token($verify_user['id'], $tokenInfo);

                return $this->AppHelper->responseEntityHandle(1, "Operation Successfully.", $loginInfo, $token);
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
            }
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
        }
    }

    private function authenticateClubUser($authInfo) {
        $loginInfo = array();
        $verify_user = $this->ClubUser->verify_email($authInfo['userName']);

        if (!empty($verify_user)) {
            if (Hash::check($authInfo['password'], $verify_user['password'])) {
                $loginInfo['id'] = $verify_user['id'];
                $loginInfo['code'] = $verify_user['code'];
                $loginInfo['fullName'] = $verify_user['name'];
                $loginInfo['email'] = $verify_user['email'];
                $loginInfo['userCode'] = $verify_user['code'];

                $token = $this->AppHelper->generateAuthToken($verify_user);

                $loginInfo['userRole'] = $verify_user['flag'];

                $tokenInfo = array();
                $tokenInfo['token'] = $token;
                $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                $this->ClubUser->update_login_token($verify_user['id'], $tokenInfo);

                return $this->AppHelper->responseEntityHandle(1, "Operation Successfully.", $loginInfo, $token);
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
            }
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
        }
    }

    private function authenticateEvaluator($authInfo) {
        $loginInfo = array();
        $verify_user = $this->Evaluator->verify_email($authInfo['userName']);

        if (!empty($verify_user)) {
            if (Hash::check($authInfo['password'], $verify_user['password'])) {
                $loginInfo['id'] = $verify_user['id'];
                $loginInfo['code'] = $verify_user['code'];
                $loginInfo['fullName'] = $verify_user['name'];
                $loginInfo['email'] = $verify_user['email'];
                $loginInfo['userCode'] = $verify_user['code'];

                $token = $this->AppHelper->generateAuthToken($verify_user);

                $loginInfo['userRole'] = $verify_user['flag'];

                $tokenInfo = array();
                $tokenInfo['token'] = $token;
                $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                $this->Evaluator->update_login_token($verify_user['id'], $tokenInfo);

                return $this->AppHelper->responseEntityHandle(1, "Operation Successfully.", $loginInfo, $token);
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
            }
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Username or Password");
        }
    }

    public function changePassword(Request $request) {

        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $password = (is_null($request->password) || empty($request->password)) ? "" : $request->password;
        $secret = (is_null($request->secret) || empty($request->secret)) ? "" : $request->secret;

        if ($email == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else if ($password == "") {
            return $this->AppHelper->responseMessageHandle(0, "Password is required.");
        } else if ($secret == "") {
            return $this->AppHelper->responseMessageHandle(0, "Secret is required.");
        } else {

            try {
                $resp = $this->ChangePasswordLog->find_by_secret($secret);

                if ($resp) {
                    $passwordInfo = array();
                    $passwordInfo['email'] = $email;
                    $passwordInfo['password'] = $password;

                    $res = null;

                    if ($resp['flag'] == "RC") {
                        $res = $this->RegionChairPerson->update_pw_by_email($passwordInfo);
                    } else if ($resp['flag'] == "ZC") {
                        $res = $this->ZonalChairPerson->update_pw_by_email($passwordInfo);
                    } else if ($resp['flag'] == "E") {
                        $res = $this->Evaluator->update_pw_by_email($passwordInfo);
                    } else if ($resp['flag'] == "CU") {
                        $res = $this->ClubUser->update_pw_by_email($passwordInfo);
                    } else if ($resp['flag'] == "CNTU") {
                        $res = $this->ContextUser->update_pw_by_email($passwordInfo);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Invalid Flag");
                    }

                    if ($res) {

                        $this->ChangePasswordLog->delete_log_by_email($email);

                        return $this->AppHelper->responseMessageHandle(1, "Operaion Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Operaion Not Complete");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Secret");
                }
            } catch(\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function loadUserInfo(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $userInfo = null;

                if ($flag == "G") {
                    $userInfo = $this->Governer->check_permission($request_token, $flag);
                } else if ($flag == "CU") {
                    $userInfo = $this->ClubUser->check_permission($request_token, $flag);
                } else if ($flag == "E") {
                    $userInfo = $this->Evaluator->check_permission($request_token, $flag);
                } else if ($flag == "CNTU") {
                    $userInfo = $this->ContextUser->check_permission($request_token, $flag);
                } else if ($flag == "RC") {
                    $userInfo = $this->RegionChairPerson->check_permission($request_token, $flag);
                } else if ($flag == "ZC") {
                    $userInfo = $this->ZonalChairPerson->check_permission($request_token, $flag);
                } else {
                    $userInfo = null;
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $userInfo);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
