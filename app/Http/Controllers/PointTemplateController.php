<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use App\Models\PointTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointTemplateController extends Controller
{
    private $AppHelper;
    private $Governer;
    private $PointTemplate;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Governer = new Governer();
        $this->PointTemplate = new PointTemplate();
    }

    public function addNewPointTemplate(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $templateCode = (is_null($request->templateName) || empty($request->templateName)) ? "" : $request->templateName;
        $valueList = (is_null($request->valueList) || empty($request->valueList)) ? "" : $request->valueList;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {   
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($templateCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Template Code is reuiqred");
        } else if ($valueList == "") {
            return $this->AppHelper->responseMessageHandle(0, "Value List is required.");
        } else {
            try {
                $templateInfo = array();
                $template = $this->PointTemplate->find_by_code($templateCode);

                if (!empty($template)) {
                    return $this->AppHelper->responseMessageHandle(0, "Template Already Exists.");
                }

                $templateInfo['templateCode'] = $templateCode;
                $templateInfo['templateValue'] = json_encode($valueList);
                $templateInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $newTemplate = $this->PointTemplate->add_log($templateInfo);

                if ($newTemplate) {
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $newTemplate);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAllPointTemplateList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $allPointTemplateList = $this->PointTemplate->query_all();

                $templateList = array();
                foreach ($allPointTemplateList as $key => $value) {
                    $templateList[$key]['templateName'] = $value['code'];
                    $templateList[$key]['valueList'] = json_decode($value['value']);
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $templateList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getPointTemplateObjectByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $pointTemplateCode = (is_null($request->pointTemplateCode) || empty($request->pointTemplateCode)) ? "" : $request->pointTemplateCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($pointTemplateCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Template Code is required.");
        } else {

            try {
                $resp = $this->PointTemplate->find_by_code($pointTemplateCode);

                $rangeList = json_decode($resp->value);
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $rangeList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getPointTemplateObjByTemplateName(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $pointTemplateCode = (is_null($request->pointTemplateCode) || empty($request->pointTemplateCode)) ? "" : $request->pointTemplateCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($pointTemplateCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Template Code is required.");
        } else {

            try {
                $resp = $this->PointTemplate->find_by_code($pointTemplateCode);
                $rangeList = json_decode($resp->value);

                $info = array();
                $info['templateName'] = $resp->code;
                $info['valueList'] = $rangeList;
                
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $info);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updatePointTemplateObjByCode(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $templateCode = (is_null($request->templateName) || empty($request->templateName)) ? "" : $request->templateName;
        $valueList = (is_null($request->valueList) || empty($request->valueList)) ? "" : $request->valueList;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {   
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($templateCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Template Code is reuiqred");
        } else if ($valueList == "") {
            return $this->AppHelper->responseMessageHandle(0, "Value List is required.");
        } else {
            try {
                $templateInfo = array();
                $templateInfo['templateCode'] = $templateCode;
                $templateInfo['templateValue'] = json_encode($valueList);
                $templateInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $newTemplate = $this->PointTemplate->update_point_template_by_code($templateInfo);

                if ($newTemplate) {
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $newTemplate);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getTemplateObjByActivityCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $activityCode = (is_null($request->activityCode) || empty($request->activityCode)) ? "" : $request->activityCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($activityCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Activity Code is required.");
        } else {

            try {
                $resp = DB::table('point_templates')->select('point_templates.*')
                                                    ->join('activities', 'activities.point_template_code', '=', 'point_templates.code')
                                                    ->where('activities.code', '=', $activityCode)
                                                    ->get();

                $rangeList = json_decode($resp[0]->value);

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $rangeList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deletePointTemplateByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $templateCode = (is_null($request->pointTemplateCode) || empty($request->pointTemplateCode)) ? "" : $request->pointTemplateCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($templateCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Template Code is required.");
        } else {

            try {
                
                $resp = $this->PointTemplate->delete_by_code($templateCode);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete Successfullt.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
