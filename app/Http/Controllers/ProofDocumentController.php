<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Governer;
use App\Models\ProofDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProofDocumentController extends Controller
{
    private $Governer;
    private $AppHelper;
    private $ProofDoc;

    public function __construct()
    {
        $this->Governer = new Governer();
        $this->AppHelper = new AppHelper();
        $this->ProofDoc = new ProofDocument();
    }

    public function addNewProofDocument(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $documentCode = (is_null($request->documentCode) || empty($request->documentCode)) ? "" : $request->documentCode;
        $documentName = (is_null($request->documentName) || empty($request->documentName)) ? "" : $request->documentName;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($documentCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Document Code is required.");
        } else if ($documentName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Document Name is required.");
        } else {
            try {
                $documentInfo = array();
                $userPerm = $this->checkPermission($request_token, $flag);

                $document = $this->ProofDoc->find_by_code($documentCode);

                if (!empty($document)) {
                    return $this->AppHelper->responseMessageHandle(0, "Document Already Exists.");
                }

                if ($userPerm == true) {
                    $documentInfo['documentCode'] = $documentCode;
                    $documentInfo['documentName'] = $documentName;
                    $documentInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $newDocument = $this->ProofDoc->add_log($documentInfo);

                    if ($newDocument) {
                        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $newDocument);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getProofDocList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $allProffDocList = $this->ProofDoc->query_all();

                $proofDocList = array();
                foreach ($allProffDocList as $key => $value) {
                    $proofDocList[$key]['documentCode'] = $value['code'];
                    $proofDocList[$key]['documentName'] = $value['name'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operayion Complete", $proofDocList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0 ,$e->getMessage());
            }
        }
    }

    public function updateProofDocumentByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $documentCode = (is_null($request->documentCode) || empty($request->documentCode)) ? "" : $request->documentCode;
        $documentName = (is_null($request->documentName) || empty($request->documentName)) ? "" : $request->documentName;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($documentCode == "") {
            return $this->AppHelper->responseMessageHandle(0, "Document Code is required.");
        } else {
            
            try {
                $documentDetails = array();
                $documentDetails['documentCode'] = $documentCode;
                $documentDetails['documentName'] = $documentName;

                $resp = $this->ProofDoc->find_by_code($documentCode);

                if ($resp) {
                    $updateDocument = $this->ProofDoc->update_docuemnt_details_by_code($documentDetails);

                    if ($resp) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Document Code.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getDocumentInfoByCode(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $documentCode = (is_null($request->documentCode) || empty($request->documentCode)) ? "" : $request->documentCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $resp = $this->ProofDoc->find_by_code($documentCode);

                $documentInfo = array();
                $documentInfo['documentCode'] = $resp['code'];
                $documentInfo['documentName'] = $resp['name'];


                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $documentInfo);
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
