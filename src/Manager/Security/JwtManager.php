<?php

namespace Onlyoffice\DocsIntegrationSdk\Manager\Security;

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
use Onlyoffice\DocsIntegrationSdk\Manager\Security\JwtManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Default JWT Manager.
 *
 * @package Onlyoffice\DocsIntegrationSdk\Manager\Security
 */

 class JwtManager implements JwtManagerInterface
 {

    private SettingsManager $settingsManager;

    public function __construct(SettingsManager $settingsManager) {
        $this->settingsManager = $settingsManager;
    }

    /**
     * Check if a secret key to generate token exists or not.
     *
     * @return bool
     */
    public function isJwtEnabled(): bool
    {
        return !empty($this->settingsManager->getJwtKey());
    }

    /**
     * Encode a payload object into a token using a secret key
     *
     * @param array $payload
     *
     * @return string
     */
    public function jwtEncode($payload)
    {
        return JWT::encode($payload, $this->settingsManager->getJwtKey(), 'HS256');
    }

    /**
     * Decode a token into a payload object using a secret key
     *
     * @param string $token
     *
     * @return string
     */
    public function jwtDecode($token)
    {
        try {
            $payload = JWT::decode(
                $token,
                new Key($this->settingsManager->getJwtKey(), 'HS256')
            );
        } catch (\UnexpectedValueException $e) {
            $payload = "";
        }

        return $payload;
    }
 }