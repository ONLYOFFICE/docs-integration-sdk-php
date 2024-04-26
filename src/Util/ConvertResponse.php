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

 enum ConvertResponse : int {
    case Unknown = -1;
    case Timeout = -2;
    case Conversion = -3;
    case Downloading = -4;
    case Password = -5;
    case Database = -6;
    case Input = -7;
    case Token = -8;

    public function message(): string
    {
        return match($this) 
        {
            ConvertResponse::Unknown => "Unknown error",
            ConvertResponse::Timeout => "Timeout conversion error",
            ConvertResponse::Conversion => "Conversion error",
            ConvertResponse::Downloading => "Error while downloading the document file to be converted",
            ConvertResponse::Password => "Incorrect password",
            ConvertResponse::Database => "Error while accessing the conversion result database",
            ConvertResponse::Input => "Error document request",
            ConvertResponse::Token => "Invalid token",
        };
    }
}