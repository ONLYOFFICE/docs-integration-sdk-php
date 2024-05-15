<?php

namespace Onlyoffice\DocsIntegrationSdk\Util;

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
 * Error messages.
 *
 * @package Onlyoffice\DocsIntegrationSdk\Util
 */

 enum CommonError : int {
    case NoHealthcheckEndpoint = 1;
    case NoDocumentServerUrl = 2;
    case NoConvertServiceEndpoint = 3;
    case NoJwtHeader = 4;
    case NoJwtPrefix = 5;
    case ReadXml = 6;
    case BadResponseXml = 7;
    case NoCommandEndpoint = 8;
    case MixedContent = 9;
    case BadHealthcheckStatus = 10;
    case DocserviceError = 11;
    case NotSupportedVersion = 12;

    public function message(): string
    {
        return match($this) 
        {
            CommonError::NoHealthcheckEndpoint => "There is no healthcheck endpoint in the application configuration",
            CommonError::NoDocumentServerUrl => "There is no document server URL in the application configuration",
            CommonError::NoConvertServiceEndpoint => "There is no convert service endpoint in the application configuration",
            CommonError::NoJwtHeader => "There is no JWT header in the application configuration",
            CommonError::NoJwtPrefix => "There is no JWT prefix in the application configuration",
            CommonError::ReadXml => "Can't read XML",
            CommonError::BadResponseXml => "Bad response",
            CommonError::NoCommandEndpoint => "There is no command endpoint in the application configuration",
            CommonError::MixedContent => "Mixed Active Content is not allowed. HTTPS address for ONLYOFFICE Docs is required",
            CommonError::BadHealthcheckStatus => "Bad healthcheck status",
            CommonError::DocserviceError => "Error occurred in the document service",
            CommonError::NotSupportedVersion => "Not supported version",
            CommonError::EmptyFormatsAsset => "formats submodule error",
        };
    }
}