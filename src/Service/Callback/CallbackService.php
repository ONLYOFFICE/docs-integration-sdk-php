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

/**
 * Callback service Interface.
 *
 * @package Onlyoffice\DocsIntegrationSdk\Service\Callback
 */

use Onlyoffice\DocsIntegrationSdk\Manager\Settings\SettingsManager;
use Onlyoffice\DocsIntegrationSdk\Manager\Security\JwtManager;
use Onlyoffice\DocsIntegrationSdk\Manager\Document\DocumentManager;
use Onlyoffice\DocsIntegrationSdk\Models\Callback;
use Radebatz\ObjectMapper\ObjectMapper;

abstract class CallbackService implements CallbackServiceInterface
{

    private const TRACKERSTATUS_EDITING = 1;
    private const TRACKERSTATUS_MUSTSAVE = 2;
    private const TRACKERSTATUS_CORRUPTED = 3;
    private const TRACKERSTATUS_CLOSED = 4;
    private const TRACKERSTATUS_FORCESAVE = 6;
    private const TRACKERSTATUS_CORRUPTEDFORCESAVE = 7;

    protected $settingsManager;
    protected $jwtManager;

    abstract function processTrackerStatusEditing(Callback $callback, string $fileid);
    abstract function processTrackerStatusMustsave(Callback $callback, string $fileid);
    abstract function processTrackerStatusCorrupted(Callback $callback, string $fileid);
    abstract function processTrackerStatusClosed(Callback $callback, string $fileid);
    abstract function processTrackerStatusForcesave(Callback $callback, string $fileid);


    public function __construct (SettingsManager $settingsManager, JwtManager $jwtManager)
    {
        $this->settingsManager = $settingsManager;
        $this->jwtManager;
    }

    public function verifyCallback(Callback $callback, string $authorizationHeader = "")
    {
        if ($this->jwtManager->isJwtEnabled())
        {
            $token = $callback->getToken();
            $payload = null;
            $fromHeader = false;
 
            if (!empty($authorizationHeader) )
            {
                $compareHeaders = substr($authorizationHeader, 0, strlen($this->settingsManager->getJwtPrefix()));
                if ($compareHeaders === $this->settingsManager->getJwtPrefix())
                {
                    $token = $compareHeaders;
                    $fromHeader = true;
                }
            }

            if (empty($token)) 
            {
                throw new \Exception(CommonError::message(CommonError::CALLBACK_NO_AUTH_TOKEN));
            }

            $payload = $this->jwtManager->jwtDecode($token);
            $callbackFromToken = json_decode($token, true);
            if ($fromHeader)
            {
                $callbackFromToken = $callbackFromToken["payload"];
            }

            $objectMapper = new ObjectMapper();
            return $objectMapper->map($callbackFromToken, \Callback::class);
        }
        return $callback;
    }

    public function processCallback(Callback $callback, string $fileId)
    {
        switch ($callback->getStatus()) {
            case CallbackDocStatus::EDITING :
                $this->processTrackerStatusEditing($callback, $fileId);
            break;
            case CallbackDocStatus::SAVE :
                $this->processTrackerStatusMustsave($callback, $fileId);
            break;
            case CallbackDocStatus::SAVE_CORRUPTED :
                $this->processTrackerStatusCorrupted($callback, $fileId);
            break;
            case CallbackDocStatus::CLOSED :
                $this->processTrackerStatusClosed($callback, $fileId);
            break;
            case CallbackDocStatus::FORCESAVE :
                $this->processTrackerStatusForcesave($callback, $fileId);
            break;
            case CallbackDocStatus::FORCESAVE_CORRUPTED :
                $this->processTrackerStatusCorruptedForcesave($callback, $fileId);
            break;
            default:
                throw new \Exception(CommonError::message(CommonError::CALLBACK_NO_STATUS));
        }
    }

    public function processTrackerStatusCorruptedForcesave(Callback $callback, string $fileid) {
        $this->processTrackerStatusForcesave($callback, $fileid);
    }
}