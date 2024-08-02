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

 class User
 {
    
    private $id;
    private $name;
    private $group;
    private $image;
    

    public function __construct(string $id = "", string $name = "", string $group = "", string $image = "") {
        $this->id = $id;
        $this->name = $name;
        $this->group = $group;
        $this->image = $image;
    }

    public function getGroup() {
        return $this->group;
    }

    public function setGroup(string $group) {
        $this->group = $group;
    }

    public function getId() {
        return $this->id;
    }

    public function setId(string $type) {
        $this->id = $id;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage(array $image) {
        $this->image = $image;
    }

    public function getName() {
        return $this->name;
    }

    public function setName(array $name) {
        $this->name = $name;
    }
 }