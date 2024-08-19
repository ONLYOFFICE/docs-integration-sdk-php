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
use Onlyoffice\DocsIntegrationSdk\Models\Document;
use Onlyoffice\DocsIntegrationSdk\Models\GoBack;
use Onlyoffice\DocsIntegrationSdk\Models\Permissions;
use Onlyoffice\DocsIntegrationSdk\Models\ReferenceData;
use Onlyoffice\DocsIntegrationSdk\Models\Type;
use Onlyoffice\DocsIntegrationSdk\Models\User;
use Onlyoffice\DocsIntegrationSdk\Util\EnvUtil;

abstract class DocEditorConfigService implements DocEditorConfigServiceInterface
{

   private $documentManager;
   private $jwtManager;
   private $settingsManager;

   public function __construct (SettingsManager $settingsManager, JwtManager $jwtManager = null, DocumentManager $documentManager = null) {
      EnvUtil::loadEnvSettings();
      $this->settingsManager = $settingsManager;
      $this->jwtManager = $jwtManager !== null ? $jwtManager : new JwtManager($settingsManager);
      $this->documentManager = $documentManager !== null ? $documentManager : new DocumentManager($settingsManager);
   }

   public function isMobileAgent(string $userAgent = "") {
      $userAgent = !empty($userAgent) ? $userAgent : $_SERVER["HTTP_USER_AGENT"];
      $envKey = EnvUtil::envKey("EDITING_SERVICE_MOBILE_USER_AGENT");
      $agentList = isset($_ENV[$envKey]) && !empty($_ENV[$envKey]) ? $_ENV[$envKey] : "android|avantgo|playbook|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino";
      return preg_match($agentList, $userAgent);
   }

   public function getDocEditorConfig() {
      $customization = $this->getCustomization();
   }

   public function getDocument(string $fileId, Type $type) {
      $documentName = $this->documentManager->getDocumentName($fileId);
      $permissions = $this->getPermissions($fileId);
      $document = new Document($this->documentManager->getExt($documentName),
                              $this->documentManager->getDocumentKey($fileId, $type->value === Type::EMBEDDED),
                              $this->getReferenceData($fileId),
                              $documentName,
                              $this->documentManager->getFileUrl($fileId),
                              $this->getInfo($fileId),
                              $permissions

   );
   }

   public function getCustomization(string $fileId) {
      $goback = new GoBack;

      if (!empty($this->documentManager->getGobackUrl($fileId))) {
         $goback->setUrl($this->documentManager->getGobackUrl($fileId));
      }

      $customization = new Customization;
      $customization.setGoback($goback);

      return $customization;
   }

   public function getPermissions(string $fileId) {
      return new Permissions;
   }

   public function getReferenceData(string $fileId) {
      return new ReferenceData;
   }

   public function getIndo(string $fileId) {
      return new Info;
   }

   public function getType(string $userAgent = "") {
      return $this->isMobileAgent($userAgent) ? new Type(Type::MOBILE) : new Type(Type::DESKTOP);
   }
}