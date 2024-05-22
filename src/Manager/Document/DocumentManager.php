<?php

namespace Onlyoffice\DocsIntegrationSdk\Manager\Document;

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

use Onlyoffice\DocsIntegrationSdk\Manager\Document\DocumentManagerInreface;
use Onlyoffice\DocsIntegrationSdk\Models\Format;
use Onlyoffice\DocsIntegrationSdk\Manager\Formats\FormatsManager;
use Onlyoffice\DocsIntegrationSdk\Util\CommonError;

 class DocumentManager implements DocumentManagerInterface
 {

    private const PATHINFO_DIRNAME = "dirname";
    private const PATHINFO_BASENAME = "basename";
    private const PATHINFO_EXTENSION = "extension";
    private const PATHINFO_FILENAME = "filename";

    private const FORMATINFO_TYPE = "type";
    private const FORMATINFO_ACTIONS = "actions";
    private const FORMATINFO_CONVERT = "convert";
    private const FORMATINFO_MIMES = "mimes";
    private const FORMATINFO_MIME = "mime";

    /**
     * Formats list
     */
    public $formats;

    public function __construct(FormatsManager $formats = null) {
        if ($formats === null) {
            $formats = new FormatsManager(true);
        }
        $this->formats = $formats;
    }

    private function getFormatInfo(string $extension, string $option = null)  {
        $search= null;
        $formats = $this->formats->getFormatsList();
        if (!array_key_exists($extension, $formats)) {
            foreach ($formats as $format) {
                if ($format->getName() === $extension) {
                    $search = $format;
                    break;
                }
            }
            if ($search === null) {
                throw new \Exception(CommonError::message(CommonError::UNKNOWN_EXT));
            }
        } else {
            $search = $formats[$extension];
        }

        switch($option) {
            case self::FORMATINFO_TYPE:
                return $search->getType();
            case self::FORMATINFO_ACTIONS:
                return $search->getActions();
            case self::FORMATINFO_CONVERT:
                return $search->getConvert();
            case self::FORMATINFO_MIMES:
                return $search->getMimes();
            case self::FORMATINFO_MIME:
                return $search->getMimes()[0];
            default:
                return $search;
        }
    }

    /**
     * Return file type by extension
     */
    public function getDocType(string $extension): string {
        return $this->getFormatInfo($extension, self::FORMATINFO_TYPE);
    }

    /**
     * Return actions for file by extension
     */
    public function getDocActions(string $extension): array {
        return $this->getFormatInfo($extension, self::FORMATINFO_ACTIONS);
    }

    /**
     * Return convert extensions for file by current extension
     */
    public function getDocConvert(string $extension): array {
        return $this->getFormatInfo($extension, self::FORMATINFO_CONVERT);
    }

    /**
     * Return array of all mime types for file by extension
     */
    public function getDocMimes(string $extension): array {
        return $this->getFormatInfo($extension, self::FORMATINFO_MIMES);
    }

    /**
     * Return mime type of the file by extension
     */
    public function getDocMimeType(string $extension): string {
        return $this->getFormatInfo($extension, self::FORMATINFO_MIME);
    }

    /**
     * Return file path info
     */
    public function getPathInfo(string $filePath, string $option = null) {
        $result = ["dirname" => "", "basename" => "", "extension" => "", "filename" => ""];
        $pathInfo = [];
        if (preg_match("#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m", $filePath, $pathInfo)) {
            if (array_key_exists(1, $pathInfo)) {
                $result["dirname"] = $pathInfo[1];
            }
            if (array_key_exists(2, $pathInfo)) {
                $result["basename"] = $pathInfo[2];
            }
            if (array_key_exists(5, $pathInfo)) {
                $result["extension"] = mb_strtolower($pathInfo[5]);
            }
            if (array_key_exists(3, $pathInfo)) {
                $result["filename"] = $pathInfo[3];
            }
        }

        switch ($option) {
            case self::PATHINFO_DIRNAME:
                return $result["dirname"];
            case self::PATHINFO_BASENAME:
                return $result["basename"];
            case self::PATHINFO_EXTENSION:
                return $result["extension"];
            case self::PATHINFO_FILENAME:
                return $result["filename"];
            default:
                return $result;
        }
    }

    public function getDirName(string $filePath) {
        return $this->getPathInfo($filePath, self::PATHINFO_DIRNAME);
    }

    public function getBaseName(string $filePath) {
        return $this->getPathInfo($filePath, self::PATHINFO_BASENAME);
    }

    public function getExt(string $filePath) {
        return $this->getPathInfo($filePath, self::PATHINFO_EXTENSION);
    }

    public function getFileName(string $filePath) {
        return $this->getPathInfo($filePath, self::PATHINFO_FILENAME);
    }

    public function isDocumentViewable(string $filePath): bool {
        return $this->getFormatInfo($this->getExt($filePath))->isViewable();
    }

    public function isDocumentEditable(string $filePath): bool {
        return $this->getFormatInfo($this->getExt($filePath))->isEditable();
    }

    public function isDocumentConvertable(string $filePath): bool {
        return $this->getFormatInfo($this->getExt($filePath))->isAutoConvertable();
    }

    public function isDocumentFillable(string $filePath): bool {
        return $this->getFormatInfo($this->getExt($filePath))->isFillable();
    }

 }