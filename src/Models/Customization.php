<?php

namespace Onlyoffice\DocsIntegrationSdk\Models;

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
use Onlyoffice\DocsIntegrationSdk\Models\Anonymous;
use Onlyoffice\DocsIntegrationSdk\Models\Customer;
use Onlyoffice\DocsIntegrationSdk\Models\Features;
use Onlyoffice\DocsIntegrationSdk\Models\Logo;
use Onlyoffice\DocsIntegrationSdk\Models\Review;

class Customization
{
    private $anonymous;
    private $autosave;
    private $comments;
    private $compactHeader;
    private $compactToolbar;
    private $compatibleFeatures;
    private $customer;
    private $features;
    private $feedback;
    private $forcesave;
    private $goback;
    private $help;
    private $hideNotes;
    private $hideRightMenu;
    private $hideRulers;
    private $integrationMode;
    private $logo;
    private $macros;
    private $macrosMode;
    private $mentionShare;
    private $mobileForceView;
    private $plugins;
    private $review;
    private $submitForm;
    private $toolbarHideFileName;
    private $toolbarNoTabs;
    private $uiTheme;
    private $unit;
    private $zoom;

    public function __construct (Anonymous $anonymous = null,
                                bool $autosave = true,
                                bool $comments = true,
                                bool $compactHeader = false,
                                bool $compactToolbar = false,
                                bool $compatibleFeatures = false,
                                Customer $customer = null,
                                Features $features = null,
                                bool $feedback = false,
                                bool $forcesave = false,
                                GoBack $goback = null,
                                bool $help = true,
                                bool $hideNotes = false,
                                bool $hideRightMenu = false,
                                bool $hideRulers = false,
                                string $integrationMode = "embed",
                                Logo $logo = null,
                                bool $macros = true,
                                MacrosMode $macrosMode = null,
                                bool $mentionShare = true,
                                bool $mobileForceView = true,
                                bool $plugins = true,
                                Review $review = null,
                                bool $submitForm = false,
                                bool $toolbarHideFileName = false,
                                bool $toolbarNoTabs = false,
                                string $uiTheme = "",
                                string $unit = "cm",
                                int $zoom = 100
                                )
    {
        $this->anonymous =  $anonymous !== null ? $anonymous : new Anonymous;
        $this->autosave = $autosave;
        $this->chat = $chat;
        $this->comments = $comments;
        $this->compactHeader = $compactHeader;
        $this->compactToolbar = $compactToolbar;
        $this->compatibleFeatures = $compatibleFeatures;
        $this->customer = $customer !== null ? $customer : new Customer;
        $this->features = $features !== null ? $features : new Features;
        $this->feedback = $feedback;
        $this->forcesave = $forcesave;
        $this->goback = $goback !== null ? $goback : new GoBack;
        $this->help = $help;
        $this->hideNotes = $hideNotes;
        $this->hideRightMenu = $hideRightMenu;
        $this->hideRulers = $hideRulers;
        $this->integrationMode = $integrationMode;
        $this->logo = $logo !== null ? $logo : new Logo;
        $this->macros = $macros;
        $this->macrosMode = $macrosMode !== null ? $macrosMode : new MacrosMode;
        $this->mentionShare = $mentionShare;
        $this->mobileForceView = $mobileForceView;
        $this->plugins = $plugins;
        $this->review = $review !== null ? $review : new Review;
        $this->submitForm = $submitForm;
        $this->toolbarHideFileName = $toolbarHideFileName;
        $this->toolbarNoTabs = $toolbarNoTabs;
        $this->uiTheme = $uiTheme;
        $this->unit = $unit;
        $this->zoom = $zoom;
    }
}