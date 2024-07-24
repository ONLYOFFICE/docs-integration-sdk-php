<?php

namespace Onlyoffice\DocsIntegrationSdk\Service\Callback;

/**
 *
 * (c) Copyright Ascensio System SIA 2024
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
use Onlyoffice\DocsIntegrationSdk\Manager\Settings\SettingsManager;
use Onlyoffice\DocsIntegrationSdk\Manager\Security\JwtManager;
use Onlyoffice\DocsIntegrationSdk\Manager\Document\DocumentManager;

abstract class CallbackService implements CallbackServiceInterface
{

    private const TRACKERSTATUS_EDITING = 1;
    private const TRACKERSTATUS_MUSTSAVE = 2;
    private const TRACKERSTATUS_CORRUPTED = 3;
    private const TRACKERSTATUS_CLOSED = 4;
    private const TRACKERSTATUS_FORCESAVE = 6;
    private const TRACKERSTATUS_CORRUPTEDFORCESAVE = 7;

    protected $hashData;
    private $callbackResponse;
    private $callbackResponseJSON;
    protected $settingsManager;
    protected $jwtManager;
    protected $docManager;

    abstract function getSecurityKey();
    abstract function processHashData();
    abstract function processTrackerStatusEditing($trackResult, $data) : array;
    abstract function processTrackerStatusMustsave($trackResult, $data) : array;
    abstract function processTrackerStatusCorrupted($trackResult, $data) : array;
    abstract function processTrackerStatusClosed($trackResult, $data) : array;
    abstract function processTrackerStatusForcesave($trackResult, $data) : array;
    abstract function processTrackerStatusCorruptedForcesave($trackResult, $data) : array;

    public function __construct ($request, SettingsManager $settingsManager, DocumentManager $docManager, JwtManager $jwtManager) {
        $this->callbackResponse = [];
        $this->settingsManager = $settingsManager;
        $this->docManager = $docManager;
        $this->jwtManager = $jwtManager;
        if (isset($request["hash"]) && !empty($request["hash"])) {
            @header( 'Content-Type: application/json; charset==utf-8');
            @header( 'X-Robots-Tag: noindex' );
            @header( 'X-Content-Type-Options: nosniff' );

            list ($hashData, $error) = $this->jwtManager->readHash($_GET["hash"], $this->getSecurityKey());
            if (empty($hashData)) {
                $this->callbackResponse["status"] = "error";
                $this->callbackResponse["error"] = $error;
                $this->callbackResponseJSON = json_encode($this->callbackResponse);
                return;
            }
            $this->hashData = $hashData;
            $error = $this->processHashData();
            if (!empty($error)) {
                $this->callbackResponse["status"] = "error";
                $this->callbackResponse["error"] = $error;
                $this->callbackResponseJSON = json_encode($this->callbackResponse);
                return;
            }

            if (isset($this->hashData->type) && !empty($this->hashData->type)) {
                switch($this->hashData->type) {
                    case "track":
                        $this->callbackResponse = $this->track();
                        break;
                    case "download":
                        $this->callbackResponse = $this->download();
                        break;
                    default:
                        $this->callbackResponse["status"] = "error";
                        $this->callbackResponse["error"] = "404 Method not found";
                }
            }
        }
        $this->callbackResponseJSON = json_encode($this->callbackResponse);
    }

    public function getCallbackResponse() {
        return $this->callbackResponse;
    }

    public function getCallbackResponseJSON() {
        return $this->callbackResponseJSON;
    }

    public function track() {
        $result = [];
        if (($body_stream = file_get_contents("php://input")) === false) {
            $result["error"] = "Bad Request";
            return $result;
        }

        $data = json_decode($body_stream, true);
        if ($data === null) {
            $result["error"] = "Bad Response";
            return $result;
        }

        if (!empty($this->settingsManager->getJwtKey())) {
            if (!empty($data["token"])) {
                try {
                    $payload = $this->jwtManager->jwtDecode($data["token"]);
                } catch (\UnexpectedValueException $e) {
                    $result["status"] = "error";
                    $result["error"] = "403 Access denied";
                    return $result;
                }
            } else {
                $token = substr(getallheaders()[$this->settingsManager->getJwtHeader()], strlen("Bearer "));
                try {
                    $decodedToken = $this->jwtManager->jwtDecode($token);
                    $payload = $decodedToken->payload;
                } catch (\UnexpectedValueException $e) {
                    $result["status"] = "error";
                    $result["error"] = "403 Access denied";
                    return $result;
                }
            }
            $data["url"] = isset($payload->url) ? $payload->url : null;
            $data["status"] = $payload->status;
        }
        $status = $data["status"];
        $trackResult = 1;
        $error = null;

        switch ($status) {
            case self::TRACKERSTATUS_EDITING:
                list ($trackResult, $error) = $this->processTrackerStatusEditing($trackResult, $data);
                break;
            case self::TRACKERSTATUS_MUSTSAVE:
                list ($trackResult, $error) = $this->processTrackerStatusMustsave($trackResult, $data);
                break;
            case self::TRACKERSTATUS_CORRUPTED:
                list ($trackResult, $error) = $this->processTrackerStatusCorrupted($trackResult, $data);
                break;
            case self::TRACKERSTATUS_CLOSED:
                list ($trackResult, $error) = $this->processTrackerStatusClosed($trackResult, $data);
                break;
            case self::TRACKERSTATUS_FORCESAVE:
                list ($trackResult, $error) = $this->processTrackerStatusForcesave($trackResult, $data);
                break;
            case self::TRACKERSTATUS_CORRUPTEDFORCESAVE:
                list ($trackResult, $error) = $this->processTrackerStatusCorruptedForcesave($trackResult, $data);
                break;
        }
        if (!empty($error)) {
            $result["error"] = $error;
        } else {
            $result["error"] = $trackResult;
        }
        return $result;
    }

    public function download() {
        if (!empty($this->settingsManager->getJwtKey())) {
            $token = substr(getallheaders()[$this->settingsManager->getJwtHeader()], strlen("Bearer "));
            try {
                $payload = $this->jwtManager->jwtDecode($token);
            } catch (\UnexpectedValueException $e) {
                $result["status"] = "error";
                $result["error"] = "403 Access denied";
                return $result;
            }
        }

        $filePath = $this->getFilePath();
        if (empty($filePath)) {
            $result["status"] = "error";
            $result["error"] = "File not found";
            return $result;
        }
        $fileName = $this->docManager->getFileName($filePath);
        @header("Content-Type: application/octet-stream");
        @header("Content-Disposition: attachment; filename=" . $fileName);

        $this->readFile($filePath);
    }

    public function readfile($filePath) {
        readfile($filePath);
        exit();
    }
    
}