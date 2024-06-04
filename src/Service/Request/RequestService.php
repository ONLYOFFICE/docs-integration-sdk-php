<?php

namespace Onlyoffice\DocsIntegrationSdk\Service\Request;

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
use Onlyoffice\DocsIntegrationSdk\Manager\Document\DocumentManager;
use Onlyoffice\DocsIntegrationSdk\Manager\Settings\SettingsManager;
use Onlyoffice\DocsIntegrationSdk\Manager\Security\JwtManager;
use Onlyoffice\DocsIntegrationSdk\Service\Request\RequestServiceInterface;
use Onlyoffice\DocsIntegrationSdk\Util\CommandResponse;
use Onlyoffice\DocsIntegrationSdk\Util\CommonError;
use Onlyoffice\DocsIntegrationSdk\Util\ConvertResponse;
use GuzzleHttp\Client;

/**
 * Default Document service.
 *
 * @package Onlyoffice\DocsIntegrationSdk\Service\Request
 */

 class RequestService implements RequestServiceInterface {

    /**
     * Minimum supported version of editors
     *
     * @var float
     */
    private const MIN_EDITORS_VERSION = 6.0;

    protected SettingsManager $settingsManager;
    protected JwtManager $jwtManager;

    public function __construct(SettingsManager $settingsManager) {
        $this->settingsManager = $settingsManager;
        $this->jwtManager = new JwtManager($this->settingsManager);
    }

     /**
     * Request to Document Server
     *
     * @param string $url - request address
     * @param array $method - request method
     * @param array $opts - request options
     *
     * @return string
     */
    public function request($url, $method = "GET", $opts = []) {
        $client = new Client();
        if ($this->settingsManager->isIgnoreSSL()) {
            $opts["verify"] = false;
        }

        if (!array_key_exists("timeout", $opts)) {
            $opts["timeout"] = 60;
        }

        $response = $client->request($method, $url, $opts);
        if ($response->getStatusCode() === 200) {
            return $response->getBody()->getContents();
        }

        return "";
    }

        /**
     * Generate an error code table of convertion
     *
     * @param int $errorCode - Error code
     *
     * @throws Exception
     */
    private function processConvServResponceError($errorCode) {
        $errorMessage = '';

        switch ($errorCode) {
            case ConvertResponse::UNKNOWN:
                $errorMessage = ConvertResponse::message(ConvertResponse::UNKNOWN);
                break;
            case ConvertResponse::TIMEOUT:
                $errorMessage = ConvertResponse::message(ConvertResponse::TIMEOUT);
                break;
            case ConvertResponse::CONVERSION:
                $errorMessage = ConvertResponse::message(ConvertResponse::CONVERSION);
                break;
            case ConvertResponse::DOWNLOADING:
                $errorMessage = ConvertResponse::message(ConvertResponse::DOWNLOADING);
                break;
            case ConvertResponse::PASSWORD:
                $errorMessage = ConvertResponse::message(ConvertResponse::PASSWORD);
                break;
            case ConvertResponse::DATABASE:
                $errorMessage = ConvertResponse::message(ConvertResponse::DATABASE);
                break;
            case ConvertResponse::INPUT:
                $errorMessage = ConvertResponse::message(ConvertResponse::INPUT);
                break;
            case ConvertResponse::TOKEN:
                $errorMessage = ConvertResponse::message(ConvertResponse::TOKEN);
                break;
            default:
                $errorMessage = "ErrorCode = " . $errorCode;
                break;
        }

        throw new \Exception($errorMessage);
    }

    /**
     * Generate an error code table of command
     *
     * @param string $errorCode - Error code
     *
     * @throws Exception
     */
    protected function processCommandServResponceError($errorCode) {
        $errorMessage = '';

        switch ($errorCode) {
            case CommandResponse::NO:
                return;
            case CommandResponse::KEY:
                $errorMessage = CommandResponse::message(CommandResponse::KEY);
                break;
            case CommandResponse::CALLBACK_URL:
                $errorMessage = CommandResponse::message(CommandResponse::CALLBACK_URL);
                break;
            case CommandResponse::INTERNAL_SERVER:
                $errorMessage = CommandResponse::message(CommandResponse::INTERNAL_SERVER);
                break;
            case CommandResponse::FORCE_SAVE:
                $errorMessage = CommandResponse::message(CommandResponse::FORCE_SAVE);
                break;
            case CommandResponse::COMMAND:
                $errorMessage = CommandResponse::message(CommandResponse::COMMAND);
                break;
            case CommandResponse::TOKEN:
                $errorMessage = CommandResponse::message(CommandResponse::TOKEN);
                break;
            default:
                $errorMessage = "ErrorCode = " . $errorCode;
                break;
        }

        throw new \Exception($errorMessage);
    }

    /**
     * Request health status
     *
     * @throws Exception
     * 
     * @return bool
     */
    public function healthcheckRequest() : bool {
        $healthcheckUrl = $this->settingsManager->getDocumentServerHealthcheckUrl();
        if (empty($healthcheckUrl)) {
            throw new \Exception(CommonError::message(CommonError::NO_HEALTHCHECK_ENDPOINT));
        }

        $response = $this->request($healthcheckUrl);
        return $response === "true";
    }

    /**
     * Request for conversion to a service
     *
     * @param string $documentUri - Uri for the document to convert
     * @param string $fromExtension - Document extension
     * @param string $toExtension - Extension to which to convert
     * @param string $documentRevisionId - Key for caching on service
     * @param bool - $isAsync - Perform conversions asynchronously
     * @param string $region - Region
     * 
     * @throws Exception
     *
     * @return array
     */
    public function sendRequestToConvertService($documentUri, $fromExtension, $toExtension, $documentRevisionId, $isAsync, $region = null) {
        $urlToConverter = $this->settingManager->getConvertServiceUrl(true);
        if (empty($urlToConverter)) {
            throw new \Exception(CommonError::message(CommonError::NO_CONVERT_SERVICE_ENDPOINT));
        }

        if (empty($documentRevisionId)) {
            $documentRevisionId = $documentUri;
        }
        $documentRevisionId = FileUtility::GenerateRevisionId($documentRevisionId);

        if (empty($fromExtension)) {
            // TODO: Use special methods in FileUtility
            $fromExtension = pathinfo($documentUri)['extension'];
        } else {
            $fromExtension = trim($fromExtension, '.');
        }

        $data = [
            'async' => $isAsync,
            'url' => $documentUri,
            'outputtype' => trim($toExtension, '.'),
            'filetype' => $fromExtension,
            'title' => $documentRevisionId . '.' . $fromExtension,
            'key' => $documentRevisionId
        ];

        if (!is_null($region)) {
            $data['region'] = $region;
        }

        //TODO: remove hardcode
        $opts = [
            'timeout' => '120',
            'headers' => [
                'Content-type' => 'application/json'
            ],
            'body' => json_encode($data)
        ];

        if ($this->jwtManager->isJwtEnabled()) {
            $params = [
                'payload' => $data
            ];
            $token = $this->jwtManager->jwtEncode($params);
            $jwtHeader = $this->settingsManager->getJwtHeader();
            $jwtPrefix = $this->settingsManager->getJwtPrefix();

            if (empty($jwtHeader)) {
                throw new \Exception(CommonError::message(CommonError::NO_JWT_HEADER));
            } elseif (empty($jwtPrefix)) {
                throw new \Exception(CommonError::message(CommonError::NO_JWT_PREFIX));
            }

            $opts['headers'][$jwtHeader] = $jwtPrefix . $token;
            $token = $this->jwtManager->jwtEncode($data);
            $data['token'] = $token;
            $opts['body'] = json_encode($data);
        }

        $response_xml_data = $this->request($urlToConverter, 'POST', $opts);
        libxml_use_internal_errors(true);

        //TODO: Use special lib for XML
        if (!function_exists('simplexml_load_file')) {
             throw new \Exception(CommonError::message(CommonError::READ_XML));
        }

        $response_data = simplexml_load_string($response_xml_data);
        
        if (!$response_data) {
            $exc = CommonError::message(CommonError::BAD_RESPONSE_XML);
            foreach(libxml_get_errors() as $error) {
                $exc = $exc . PHP_EOL . $error->message;
            }
            throw new \Exception ($exc);
        }

        return $response_data;
    }

    /**
     * The method is to convert the file to the required format and return the result url
     *
     * @param string $documentUri - Uri for the document to convert
     * @param string $fromExtension - Document extension
     * @param string $toExtension - Extension to which to convert
     * @param string $documentRevisionId - Key for caching on service
     * @param string $region - Region
     *
     * @return string
     */
    public function getConvertedUri($documentUri, $fromExtension, $toExtension, $documentRevisionId, $region = null) {
        $responceFromConvertService = $this->sendRequestToConvertService($documentUri, $fromExtension, $toExtension, $documentRevisionId, false, $region);
        $errorElement = $responceFromConvertService->Error;
        if ($errorElement->count() > 0) {
            $this->processConvServResponceError($errorElement);
        }

        $isEndConvert = $responceFromConvertService->EndConvert;

        if ($isEndConvert !== null && strtolower($isEndConvert) === 'true') {
            return $responceFromConvertService->FileUrl;
        }

        return "";
    }

    /**
     * Send command
     *
     * @param string $method - type of command
     *
     * @return array
     */
    public function commandRequest($method) {
        $urlCommand = $this->settingsManager->getCommandServiceUrl(true);
        if (empty($urlCommand)) {
            throw new \Exception(CommonError::message(CommonError::NO_COMMAND_ENDPOINT));
        }

        $data = [
            "c" => $method
        ];
        //TODO: remove hardcode
        $opts = [
            "headers" => [
                "Content-type" => "application/json"
            ],
            "body" => json_encode($data)
        ];

        if ($this->jwtManager->isJwtEnabled()) {
            $params = [
                "payload" => $data
            ];
            $token = $this->jwtManager->jwtEncode($params);
            $jwtHeader = $this->settingsManager->getJwtHeader();
            $jwtPrefix = $this->settingsManager->getJwtPrefix();
            if (empty($jwtHeader)) {
                throw new \Exception(CommonError::message(CommonError::NO_COMMAND_ENDPOINT));
            } elseif (empty($jwtPrefix)) {
                throw new \Exception(CommonError::message(CommonError::NO_JWT_PREFIX));
            }

            $opts["headers"][$jwtHeader] = $jwtPrefix . $token;
            $token = $this->jwtManager->jwtEncode($data);
            $data["token"] = $token;
            $opts["body"] = json_encode($data);
        }

        $response = $this->request($urlCommand, "post", $opts);
        $data = json_decode($response);
        $this->processCommandServResponceError($data->error);

        return $data;
    }

    /**
     * Checking document service location
     *
     * @return array
     */
    public function checkDocServiceUrl() {
        $version = null;
        $documentServerUrl = $this->settingsManager->getDocumentServerUrl();
        if (empty($documentServerUrl)) {
            throw new \Exception(CommonError::message(CommonError::NO_DOCUMENT_SERVER_URL));
        }

        try {
            if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == 1)
            || isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https"
            && preg_match('/^http:\/\//i', $documentServerUrl)) {
                throw new \Exception(CommonError::message(CommonError::MIXED_CONTENT));
            }
        } catch (\Exception $e) {
            return [$e->getMessage(), $version];
        }

        try {
            $healthcheckResponse = $this->healthcheckRequest();

            if (!$healthcheckResponse) {
                throw new \Exception(CommonError::message(CommonError::BAD_HEALTHCHECK_STATUS));
            }
        } catch (\Exception $e) {
            return [$e->getMessage(), $version];
        }

        try {
            $commandResponse = $this->commandRequest('version');

            if (empty($commandResponse)) {
                throw new \Exception(CommonError::message(CommonError::BAD_HEALTHCHECK_STATUS));
            }

            $version = $commandResponse->version;
            $versionF = floatval($version);

            if ($versionF > 0.0 && $versionF <= self::MIN_EDITORS_VERSION) {
                throw new \Exception(CommonError::message(CommonError::NOT_SUPPORTED_VERSION));
            }
        } catch (\Exception $e) {
            return [$e->getMessage(), $version];
        }

        return ['', $version];
    }

}