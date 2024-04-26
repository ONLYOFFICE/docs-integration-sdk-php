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

    private SettingsManager $settingsManager;
    private JwtManager $jwtManager;

    public function __construct(SettingsManager $settingsManager) {
        $this->settingsManager = $settingsManager;
        $this->jwtManager = new JwtManager($this->settingsManager);
    }

     /**
     * Request to Document Server with turn off verification
     *
     * @param string $url - request address
     * @param array $method - request method
     * @param array $opts - request options
     *
     * @return string
     */
    public function request($url, $method = 'GET', $opts = []) {

        /**TODO: rewrite with Guzzle */
        if (substr($url, 0, strlen('https')) === 'https') {
            $opts['verify'] = false;
        }
        if (!array_key_exists('timeout', $opts)) {
            $opts['timeout'] = 60;
        }

        $curl_info = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $opts['timeout'],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $opts['body'],
            CURLOPT_HTTPHEADER => $opts['headers'],
        ];

        if ($opts == []) {
            unset($curl_info[CURLOPT_POSTFIELDS]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, $curl_info);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
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
            case ConvertResponse::Unknown->value:
                $errorMessage = ConvertResponse::Unknown->message();
                break;
            case ConvertResponse::Timeout->value:
                $errorMessage = ConvertResponse::Timeout->message();
                break;
            case ConvertResponse::Conversion->value:
                $errorMessage = ConvertResponse::Conversion->message();
                break;
            case ConvertResponse::Downloading->value:
                $errorMessage = ConvertResponse::Downloading->message();
                break;
            case ConvertResponse::Password->value:
                $errorMessage = ConvertResponse::Password->message();
                break;
            case ConvertResponse::Database->value:
                $errorMessage = ConvertResponse::Database->message();
                break;
            case ConvertResponse::Input->value:
                $errorMessage = ConvertResponse::Input->message();
                break;
            case ConvertResponse::Token->value:
                $errorMessage = ConvertResponse::Token->message();
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
    private function processCommandServResponceError($errorCode) {
        $errorMessage = '';

        switch ($errorCode) {
            case CommandResponse::No->value:
                return;
            case CommandResponse::Key->value:
                $errorMessage = CommandResponse::Key->message();
                break;
            case CommandResponse::CallbackUrl->value:
                $errorMessage = CommandResponse::CallbackUrl->message();
                break;
            case CommandResponse::InternalServer->value:
                $errorMessage = CommandResponse::InternalServer->message();
                break;
            case CommandResponse::ForceSave->value:
                $errorMessage = CommandResponse::ForceSave->message();
                break;
            case CommandResponse::Command->value:
                $errorMessage = CommandResponse::Command->message();
                break;
            case CommandResponse::Token->value:
                $errorMessage = CommandResponse::Token->message();
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
            throw new \Exception(CommonError::NoHealthcheckEndpoint->message());
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
        $urlToConverter = $this->getConvertServiceUrl(true);
        if (empty($urlToConverter)) {
            throw new \Exception(CommonError::NoConvertServiceEndpoint->message());
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
                throw new \Exception(CommonError::NoJwtHeader->message());
            } elseif (empty($jwtPrefix)) {
                throw new \Exception(CommonError::NoJwtPrefix->message());
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
             throw new \Exception(CommonError::ReadXml->message());
        }

        $response_data = simplexml_load_string($response_xml_data);
        
        if (!$response_data) {
            $exc = CommonError::BadResponseXml->message();
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
            throw new \Exception(CommonError::NoCommandEndpoint->message());
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
                throw new \Exception(CommonError::NoJwtHeader->message());
            } elseif (empty($jwtPrefix)) {
                throw new \Exception(CommonError::NoJwtPrefix->message());
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
            throw new \Exception(CommonError::NoDocumentServerUrl->message());
        }

        try {
            if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == 1)
            || isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https"
            && preg_match('/^http:\/\//i', $documentServerUrl)) {
                throw new \Exception(CommonError::MixedContent->message());
            }
        } catch (\Exception $e) {
            return [$e->getMessage(), $version];
        }

        try {
            $healthcheckResponse = $this->healthcheckRequest();

            if (!$healthcheckResponse) {
                throw new \Exception(ConvertResponse::BadHealthcheckStatus->message());
            }
        } catch (\Exception $e) {
            return [$e->getMessage(), $version];
        }

        try {
            $commandResponse = $this->commandRequest('version');

            if (empty($commandResponse)) {
                throw new \Exception(ConvertResponse::BadHealthcheckStatus->message());
            }

            $version = $commandResponse->version;
            $versionF = floatval($version);

            if ($versionF > 0.0 && $versionF <= self::MIN_EDITORS_VERSION) {
                throw new \Exception(ConvertResponse::NotSupportedVersion->message());
            }
        } catch (\Exception $e) {
            return [$e->getMessage(), $version];
        }

        $convertedFileUri = null;

        try {
            $emptyFile = DocumentManager::createTempFile();

            if ($emptyFile['fileUrl'] !== null) {
                // ... storage url logic
                $convertedFileUri = $this->getConvertedUri($emptyFile['fileUrl'], 'docx', 'docx', 'check_' . rand());
            }
            
            unlink($emptyFile['filePath']);
        } catch (\Exception $e) {
            if (isset($emptyFile['filePath'])) {
                unlink($emptyFile['filePath']);
            }
            return [$e->getMessage(), $version];
        }

        try {
            $this->request($convertedFileUri);
        } catch (\Exception $e) {
            return [$e->getMessage(), $version];
        }

        return ['', $version];
    }

    /**
     * Create temporary file for convert service testing
     *
     * @return array
     */
    private function createTempFile() {
        /**TODO: need to rewrite */
        $fileUrl = null;
        $fileName = 'convert.docx';
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $baseName = strtolower(pathinfo($fileName, PATHINFO_FILENAME));
        $templatePath = DocumentManager::getEmptyTemplate($fileExt);
        $filePath = __DIR__ . '/' . $fileName;

        if ($fp = @fopen($filePath, 'w')) {
            /**... */
        }

        return [
            "fileUrl" => "",
            "filePath" => ""
        ];
    }

}