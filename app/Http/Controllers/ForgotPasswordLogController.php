<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Mail\ForgotPasswordMail;
use App\Models\ClubUser;
use App\Models\ContextUser;
use App\Models\Evaluator;
use App\Models\ForgotPasswordLog;
use App\Models\Governer;
use App\Models\RegionChairperson;
use App\Models\ZonalChairPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordLogController extends Controller
{
    private $AppHelper;
    private $ForgotPw;
    private $Governer;
    private $RegionChairPerson;
    private $ZonalChairPerson;
    private $ClubUser;
    private $ContextUser;
    private $Evaluvator;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->ForgotPw = new ForgotPasswordLog();
        $this->Governer = new Governer();
        $this->RegionChairPerson = new RegionChairperson();
        $this->ZonalChairPerson = new ZonalChairPerson();
        $this->ClubUser = new ClubUser();
        $this->ContextUser = new ContextUser();
        $this->Evaluvator = new Evaluator();
    }

    public function addForgotPwLog(Request $request) {

        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($email == "") { 
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $isValidEmail = $this->validateEmail($email, $flag);

                if (!$isValidEmail) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Email");
                }

                $checkRecord = $this->ForgotPw->find_by_email($email);

                $forgotLogDetails = array();
                $code = Str::random(5);

                $forgotLogDetails['email'] = $email;
                $forgotLogDetails['role'] = $flag;
                $forgotLogDetails['code'] = $code;
                $forgotLogDetails['createTime'] = $this->AppHelper->get_date_and_time();
 
                $resp = null;

                if ($checkRecord) {
                    $yesterday = $this->AppHelper->day_time() - 86400;

                    if ($checkRecord['create_time'] < $yesterday) {
                        $this->ForgotPw->delete_by_email($email);

                        $resp = $this->createForgotPwLog($forgotLogDetails, $code);
                    }
                } else {
                    $resp = $this->createForgotPwLog($forgotLogDetails, $code);
                }

                $details = [
                    'code' => $code,
                ];

                Mail::to($email)->send(new ForgotPasswordMail($details));

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updatePassword(Request $request) {

        $authCode = (is_null($request->authCode) || empty($request->authCode)) ? "" : $request->authCode;
        $password = (is_null($request->password) || empty($request->password)) ? "" : $request->password;
        $emailAddress = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($authCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Auth Code is required.");
        } else if ($password == "") {
            return $this->AppHelper->responseMessageHandle(0, "Password is required.");
        } else if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email is required.");
        } else {

            try {
                $verifyEmail = $this->ForgotPw->find_by_code($authCode);

                $passwordInfo = array();
                $passwordInfo['email'] = $emailAddress;
                $passwordInfo['password'] = $password;

                $res = null;

                if ($verifyEmail) {
                    if ($verifyEmail['role'] == "G") {
                        $res = $this->Governer->update_pw_by_email($passwordInfo);
                    } else if ($verifyEmail['role'] == "RC") {
                        $res = $this->RegionChairPerson->update_pw_by_email($passwordInfo);
                    } else if ($verifyEmail['role'] == "ZC") {
                        $res = $this->ZonalChairPerson->update_pw_by_email($passwordInfo);
                    } else if ($verifyEmail['role'] == "E") {
                        $res = $this->Evaluvator->update_pw_by_email($passwordInfo);
                    } else if ($verifyEmail['role'] == "CU") {
                        $res = $this->ClubUser->update_pw_by_email($passwordInfo);
                    } else if ($verifyEmail['role'] == "CNTU") {
                        $res = $this->ContextUser->update_pw_by_email($passwordInfo);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Invalid Flag");
                    }

                    if ($res) {
                        $this->ForgotPw->delete_by_email($emailAddress);
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Auth Code");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function createForgotPwLog($info, $code) {
        $forgotLogDetails['email'] = $info['email'];
        $forgotLogDetails['role'] = $info['role'];
        $forgotLogDetails['code'] = $code;
        $forgotLogDetails['createTime'] = $this->AppHelper->get_date_and_time();

        return $this->ForgotPw->add_log($forgotLogDetails);
    }

    private function validateEmail($email, $role) {

        $isValidEmail = false;
        $userInfo = null;

        try {
            if ($role == "G") {
                $userInfo = $this->Governer->verify_email($email);
            } else if ($role == "RC") {
                $userInfo = $this->RegionChairPerson->verify_email($email);
            } else if ($role == "ZC") {
                $userInfo = $this->ZonalChairPerson->verify_email($email);
            } else if ($role == "CU") {
                $userInfo = $this->ClubUser->verify_email($email);
            } else if ($role == "CNTU") {
                $userInfo = $this->ContextUser->verify_email($email);
            } else if ($role == "EU") {
                $userInfo = $this->Evaluvator->verify_email($email);
            } else {
                $userInfo = null;
            }

            if ($userInfo != null) {
                $isValidEmail = true;
            }
        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
        }

        return $isValidEmail;
    }
}
