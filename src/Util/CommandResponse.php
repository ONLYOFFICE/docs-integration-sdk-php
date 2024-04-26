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

 enum CommandResponse : int {
    case No = 0;
    case Key = 1;
    case CallbackUrl = 2;
    case InternalServer = 3;
    case ForceSave = 4;
    case Command = 5;
    case Token = 6;

    public function message(): string
    {
        return match($this) 
        {
            CommandResponse::No => "No errors",
            CommandResponse::Key => "Document key is missing or no document with such key could be found",
            CommandResponse::CallbackUrl => "Callback url not correct",
            CommandResponse::InternalServer => "Internal server error",
            CommandResponse::ForceSave => "No changes were applied to the document before the forcesave command was received",
            CommandResponse::Command => "Command not correct",
            CommandResponse::Token => "Invalid token", 
        };
    }
}