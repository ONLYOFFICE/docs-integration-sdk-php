<?php

namespace Onlyoffice\DocsIntegrationSdk\Service\DocEditorConfig;

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
use Onlyoffice\DocsIntegrationSdk\Manager\Document\DocumentManager;
use Onlyoffice\DocsIntegrationSdk\Service\DocEditorConfig\DocEditorConfigServiceInterface;
use Onlyoffice\DocsIntegrationSdk\Manager\Security\JwtManager;
use Onlyoffice\DocsIntegrationSdk\Util\CommonError;
use Onlyoffice\DocsIntegrationSdk\Models\User;
use Onlyoffice\DocsIntegrationSdk\Util\EnvUtil;

abstract class DocEditorConfigService implements DocEditorConfigServiceInterface
{

   private $config;
   public $request;
   public $settingsManager;
   public $docManager;
   public $user;

   public abstract function getFilePath();
   public abstract function processNoModeWarning();

   public function __construct($request, SettingsManager $settingsManager, DocumentManager $docManager, User $user)
   {
      EnvUtil::loadEnvSettings();
      $this->request = $request;
      $this->settingsManager = $settingsManager;
      $this->docManager = $docManager;
      $this->user = $user;      
      $this->config = null;
   }

   public function isMobileAgent() {
      $userAgent = $_SERVER["HTTP_USER_AGENT"];
      $envKey = EnvUtil::envKey("EDITING_SERVICE_MOBILE_USER_AGENT");
      $agentList = isset($_ENV[$envKey]) && !empty($_ENV[$envKey]) ? $_ENV[$envKey] : "android|avantgo|playbook|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino";
      return preg_match($agentList, $userAgent);
   }

   protected function buildConfig() {
      $documentServerUrl = $this->settingsManager->getDocumentServerUrl();
      if (empty($documentServerUrl)) {
         throw new \Exception(CommonError::message(CommonError::NO_DOCUMENT_SERVER_URL));
      }

      if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == 1)
      || isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https"
      && preg_match('/^http:\/\//i', $documentServerUrl)) {
            throw new \Exception(CommonError::message(CommonError::MIXED_CONTENT));
      }

      $docApiUrl = $this->settingsManager->getDocumentServerApiUrl();
      $jwtManager = new JwtManager($this->settingsManager);

      $config = [];

      $fileId = $request["fileId"] ?? "";

      $filePath = $this->getFilePath();
      $extension = $this->docManager->getExt($filePath);
      $fileUrl = $this->docManager->getFileUrl();
      if (!empty($this->settingsManager->getStorageUrl())) {
         $fileUrl = str_replace($this->settingsManager->getServerUrl(), $this->settingsManager->getStorageUrl(), $fileUrl);
      }

      $config = [
         "type" => !self::isMobileAgent() ? "desktop" : "mobile",
         "documentType" => $this->docManager->getDocType($extension),
         "document" => [
               "fileType" => $extension,
               "key" => $this->docManager->getDocumentKey(),
               "title" => $filePath,
               "url" => $fileUrl
         ],
         "editorConfig" => [
               "lang" => $this->docManager->lang,
               "region" => $this->docManager->lang,
               "user" => [
                  "id" => strval($this->user->getId()),
                  "name" => $this->user->getName()
               ],
               "customization" => [
                  "goback" => [
                     "blank" => false,
                     "requestClose" => false,
                     "text" => "Back",
                     "url" => $this->docManager->getUrlToLocation()
                  ],
                  "compactHeader" => true,
                  "toolbarNoTabs" => true
               ]
         ]
      ];

      $accessRights = $this->docManager->getDocumentAccessRights();
      $canEdit = $this->docManager->isDocumentEditable($filePath);
      $isReadonly = $this->docManager->isDocumentReadOnly();

      if ($canEdit && $accessRights && !$isReadonly) {
         $config["editorConfig"]["mode"] = "edit";
         $callback = $this->docManager->getCallbackUrl();
    
        if (!empty($this->settingsManager->getStorageUrl())) {
            $callback = str_replace($this->settingsManager->getServerUrl(), $this->settingsManager->getStorageUrl(), $callback);
        }
        $config["editorConfig"]["callbackUrl"] = $callback;
      } else {
         $canView = $this->docManager->isDocumentViewable($filePath);
         if ($canView) {
            $config["editorConfig"]["mode"] = "view";
         } else {
            $this->processNoModeWarning();
         }
      }
      $config["document"]["permissions"]["edit"] = $accessRights && !$isReadonly;

      if (!empty($this->settingsManager->getJwtKey())) {
         $token = $jwtManager->jwtEncode($config, $this->settingsManager->getJwtKey());
         $config["token"] = $token;
      }

      $this->config = $config;
   }

   public function getConfig() {
      if (empty($this->config)) {
         $this->buildConfig();
      }
      return $this->config;
   }
}