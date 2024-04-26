<?php

namespace Onlyoffice\DocsIntegrationSdk\Manager\Settings;

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
use Onlyoffice\DocsIntegrationSdk\Manager\Settings\SettingsManagerInterface;
use Dotenv\Dotenv;

/**
 * Default Settings Manager.
 *
 * @package Onlyoffice\DocsIntegrationSdk\Manager\Settings
 */

 abstract class SettingsManager implements SettingsManagerInterface
 {

    public abstract function getSetting($settingName);

    public abstract function setSetting($settingName, $value);

    /**
     * The settings key for the demo server
     *
     * @var string
     */
    private const USE_DEMO = "demo";

    /**
     * The settings key for the document server address
     *
     * @var string
     */
    private const DOCUMENT_SERVER_URL = "documentServerUrl";

    /**
     * The config key for the document server address available from storage
     *
     * @var string
     */
    private const DOCUMENT_SERVER_INTERNAL_URL = "documentServerInternalUrl";

    /**
     * The config key for JWT header
     *
     * @var string
     */
    private const JWT_HEADER = "jwtHeader";

    /**
     * The config key for JWT secret key
     *
     * @var string
     */
    private const JWT_KEY = "jwtKey";

    /**
     * The config key for JWT prefix
     *
     * @var string
     */
    private const JWT_PREFIX = "jwtPrefix";

    /**
     * The config key for JWT leeway
     *
     * @var string
     */
    private const JWT_LEEWAY = "jwtLeeway";

    /**
     * The config key for HTTP ignore SSL setting
     *
     * @var string
     */
    private const HTTP_IGNORE_SSL = "ignoreSSL";

    /** The demo url. */
    private const DEMO_URL = "https://onlinedocs.onlyoffice.com/";
    /** The demo security header. */
    private const DEMO_JWT_HEADER = "AuthorizationJWT";
    /** The demo security key. */
    private const DEMO_JWT_KEY = "sn2puSUF7muF5Jas";
    /** The demo security prefix. */
    private const DEMO_JWT_PREFIX = "Bearer ";
    /** The number of days that the demo server can be used. */
    private const DEMO_TRIAL_PERIOD = 30;

    private const ENV_SETTINGS_PREFIX = "DOCS_INTEGRATION_SDK";

    public function __construct() {
        self::loadEnvSettings();
    }

    protected function loadEnvSettings() {
        $dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
        $dotenv->safeLoad();
    }

    private function envKey($key) {
        return mb_strtoupper(self::ENV_SETTINGS_PREFIX . "_" . $key);
    }

    /**
     * Get demo data
     *
     * @return bool
     */
    public function useDemo() {
        return boolval($this->getSetting(self::USE_DEMO)) === true;
    }

    private function getBaseSettingValue(string $settingKey, string $envKey, string $demoKey = "") {
        if ($this->useDemo() && !empty($demoKey)) {
            return $demoKey;
        }

        $settingValue = $this->getSetting($settingKey);
        if (empty($url) && !empty($_ENV[$envKey])) {
            $settingValue = $_ENV[$envKey];
        }

        return $settingValue;
    }

    /**
     * Get the document service address from the application configuration
     *
     * @return string
     */
    public function getDocumentServerUrl() {
        $url = $this->getBaseSettingValue(self::DOCUMENT_SERVER_URL, $this->envKey("DOCUMENT_SERVER_URL"), self::DEMO_URL);
        $url = !empty($url) ? $this->normalizeUrl($url) : "";
        return (string)$url;
    }

    public function getDocumentServerInternalUrl() {
        if ($this->useDemo()) {
            return $this->getDocumentServerUrl();
        }

        $url = $this->getSetting(self::DOCUMENT_SERVER_INTERNAL_URL);
        if (empty($url)) {
            return $this->getDocumentServerUrl();
        }

        return (string)$url;
    }

    /**
     * Replace domain in document server url with internal address from configuration
     *
     * @param string $url - document server url
     *
     * @return string
     */
    public function replaceDocumentServerUrlToInternal($url) {
        $documentServerUrl = $this->getDocumentServerInternalUrl();
        if (!empty($documentServerUrl)) {
            $from = $this->getDocumentServerUrl();

            if (!preg_match("/^https?:\/\//i", $from)) {
                $parsedUrl = parse_url($url);
                $from = $parsedUrl["scheme"] . "://" . $parsedUrl["host"] . (array_key_exists("port", $parsedUrl) ? (":" . $parsedUrl["port"]) : "") . $from;
            }

            $url = $from !== $documentServerUrl ?? str_replace($from, $documentServerUrl, $url);
        }
        return $url;
    }

    private function getDocumentServerCustomUrl($settingKey, $useInternalUrl = false) {
        if (!$useInternalUrl) {
            $serverUrl = $this->getDocumentServerUrl();
        } else {
            $serverUrl = $this->getDocumentServerInternalUrl();
        }
        $customUrl = "";

        if (!empty($serverUrl) && !empty($_ENV[$this->envKey($settingKey)])) {
            $customUrl = $_ENV[$this->envKey($settingKey)];
            $customUrl = $this->normalizeUrl($serverUrl .= $customUrl);
        }

        return (string)$customUrl;
    }

    /**
     * Get the document server API URL
     *
     * @return string
     */
    public function getDocumentServerApiUrl($useInternalUrl = false) {
        return $this->getDocumentServerCustomUrl("DOCUMENT_SERVER_API_URL", $useInternalUrl);
    }

    /**
     * Get the document server preloader url
     *
     * @return string
     */
    public function getDocumentServerPreloaderUrl($useInternalUrl = false) {
        return $this->getDocumentServerCustomUrl("DOCUMENT_SERVER_API_PRELOADER_URL", $useInternalUrl);
    }

    /**
     * Get the document server healthcheck url
     *
     * @return string
     */
    public function getDocumentServerHealthcheckUrl($useInternalUrl = false) {
        return $this->getDocumentServerCustomUrl("DOCUMENT_SERVER_HEALTHCHECK_URL", $useInternalUrl);
    }

    /**
     * Get the convert service url
     *
     * @return string
     */
    public function getConvertServiceUrl($useInternalUrl = false) {
        return $this->getDocumentServerCustomUrl("CONVERT_SERVICE_URL", $useInternalUrl);
    }

    /**
     * Get the command service url
     *
     * @return string
     */
    public function getCommandServiceUrl($useInternalUrl = false) {
        return $this->getDocumentServerCustomUrl("COMMAND_SERVICE_URL", $useInternalUrl);
    }

    /**
     * Get the JWT Header
     *
     * @return string
     */
    public function getJwtHeader() {
        $jwtHeader = $this->getBaseSettingValue(self::JWT_HEADER, $this->envKey("JWT_HEADER"), self::DEMO_JWT_HEADER);
        return (string)$jwtHeader;
    }

    /**
     * Get the JWT secret
     *
     * @return string
     */
    public function getJwtKey() {
        $jwtKey = $this->getBaseSettingValue(self::JWT_KEY, $this->envKey("JWT_KEY"), self::DEMO_JWT_KEY);
        return (string)$jwtKey;
    }

    /**
     * Get the JWT prefix
     *
     * @return string
     */
    public function getJwtPrefix() {
        $jwtPrefix = $this->getBaseSettingValue(self::JWT_PREFIX, $this->envKey("JWT_PREFIX"), self::DEMO_JWT_PREFIX);
        return (string)$jwtPrefix;
    }

    /**
     * Get the JWT leeway
     *
     * @return string
     */
    public function getJwtLeeway() {
        $jwtLeeway = $this->getBaseSettingValue(self::JWT_LEEWAY, $this->envKey("JWT_LEEWAY"));
        return (string)$jwtLeeway;
    }

    /**
     * Get the ignore SSL value
     *
     * @return bool
     */
    public function isIgnoreSSL() {
        if (!$this->useDemo()) {
            return boolval($this->getBaseSettingValue(self::HTTP_IGNORE_SSL, $this->envKey("HTTP_IGNORE_SSL"))) === true;
        }

        return false;
    }

    /**
     * Add backslash to url if it's needed
     *
     * @return string
     */
    public function processUrl($url) {
        if ($url !== null && $url !== "/") {
            $url = rtrim($url, "/");
            if (strlen($url) > 0) {
                $url = $url . "/";
            }
        }
        return $url;
    }

    public function normalizeUrl($url) {
        $url = preg_replace('/([^:])(\/{2,})/', '$1/', $url);
        $url = filter_var($url, FILTER_SANITIZE_URL);
        // TODO: additional processing
        //...
        return $url;
    }

 }