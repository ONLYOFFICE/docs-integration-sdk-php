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
use Onlyoffice\DocsIntegrationSdk\Models\CommentGroups;

 class Permissions
 {
    private $chat;
    private $comment;
    private $commentGroups;
    private $copy;
    private $deleteCommentAuthorOnly;
    private $download;
    private $edit;
    private $editCommentAuthorOnly;
    private $fillForms;
    private $modifyContentControl;
    private $modifyFilter;
    private $print;
    private $protect;
    private $rename;
    private $review;
    private $reviewGroups; //string array
    private $userInfoGroups; //string array

    public function __construct (bool $chat, bool $comment, CommentGroups $commentGroups, bool $copy,
    bool $deleteCommentAuthorOnly, bool $download, bool $edit, bool $editCommentAuthorOnly, bool $fillForms,
    bool $modifyContentControl, bool $modifyFilter, bool $print, bool $protect, bool $rename, bool $review,
    array $reviewGroups, array $userInfoGroups)
    {
        $this->chat = $chat;
        $this->comment = $comment;
        $this->commentGroups = $commentGroups;
        $this->copy = $copy;
        $this->deleteCommentAuthorOnly = $deleteCommentAuthorOnly;
        $this->download = $download;
        $this->edit = $edit;
        $this->editCommentAuthorOnly = $editCommentAuthorOnly;
        $this->fillForms = $fillForms;
        $this->modifyContentControl = $modifyContentControl;
        $this->modifyFilter = $modifyFilter;
        $this->print = $print;
        $this->protect = $protect;
        $this->rename = $rename;
        $this->review = $review;
        $this->reviewGroups = $reviewGroups;
        $this->userInfoGroups = $userInfoGroups;
    }

    /**
     * Get the value of chat
     */ 
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * Set the value of chat
     */ 
    public function setChat($chat)
    {
        $this->chat = $chat;
    }

    /**
     * Get the value of comment
     */ 
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the value of comment
     */ 
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the value of commentGroups
     */ 
    public function getCommentGroups()
    {
        return $this->commentGroups;
    }

    /**
     * Set the value of commentGroups
     */ 
    public function setCommentGroups($commentGroups)
    {
        $this->commentGroups = $commentGroups;
    }

    /**
     * Get the value of copy
     */ 
    public function getCopy()
    {
        return $this->copy;
    }

    /**
     * Set the value of copy
     */ 
    public function setCopy($copy)
    {
        $this->copy = $copy;
    }

    /**
     * Get the value of deleteCommentAuthorOnly
     */ 
    public function getDeleteCommentAuthorOnly()
    {
        return $this->deleteCommentAuthorOnly;
    }

    /**
     * Set the value of deleteCommentAuthorOnly
     */ 
    public function setDeleteCommentAuthorOnly($deleteCommentAuthorOnly)
    {
        $this->deleteCommentAuthorOnly = $deleteCommentAuthorOnly;
    }

    /**
     * Get the value of download
     */ 
    public function getDownload()
    {
        return $this->download;
    }

    /**
     * Set the value of download
     */ 
    public function setDownload($download)
    {
        $this->download = $download;
    }

    /**
     * Get the value of edit
     */ 
    public function getEdit()
    {
        return $this->edit;
    }

    /**
     * Set the value of edit
     */ 
    public function setEdit($edit)
    {
        $this->edit = $edit;
    }

    /**
     * Get the value of editCommentAuthorOnly
     */ 
    public function getEditCommentAuthorOnly()
    {
        return $this->editCommentAuthorOnly;
    }

    /**
     * Set the value of editCommentAuthorOnly
     */ 
    public function setEditCommentAuthorOnly($editCommentAuthorOnly)
    {
        $this->editCommentAuthorOnly = $editCommentAuthorOnly;
    }

    /**
     * Get the value of fillForms
     */ 
    public function getFillForms()
    {
        return $this->fillForms;
    }

    /**
     * Set the value of fillForms
     */ 
    public function setFillForms($fillForms)
    {
        $this->fillForms = $fillForms;
    }

    /**
     * Get the value of modifyContentControl
     */ 
    public function getModifyContentControl()
    {
        return $this->modifyContentControl;
    }

    /**
     * Set the value of modifyContentControl
     */ 
    public function setModifyContentControl($modifyContentControl)
    {
        $this->modifyContentControl = $modifyContentControl;
    }

    /**
     * Get the value of modifyFilter
     */ 
    public function getModifyFilter()
    {
        return $this->modifyFilter;
    }

    /**
     * Set the value of modifyFilter
     */ 
    public function setModifyFilter($modifyFilter)
    {
        $this->modifyFilter = $modifyFilter;
    }

    /**
     * Get the value of print
     */ 
    public function getPrint()
    {
        return $this->print;
    }

    /**
     * Set the value of print
     */ 
    public function setPrint($print)
    {
        $this->print = $print;
    }

    /**
     * Get the value of protect
     */ 
    public function getProtect()
    {
        return $this->protect;
    }

    /**
     * Set the value of protect
     */ 
    public function setProtect($protect)
    {
        $this->protect = $protect;
    }

    /**
     * Get the value of rename
     */ 
    public function getRename()
    {
        return $this->rename;
    }

    /**
     * Set the value of rename
     */ 
    public function setRename($rename)
    {
        $this->rename = $rename;
    }

    /**
     * Get the value of review
     */ 
    public function getReview()
    {
        return $this->review;
    }

    /**
     * Set the value of review
     */ 
    public function setReview($review)
    {
        $this->review = $review;
    }

    /**
     * Get the value of reviewGroups
     */ 
    public function getReviewGroups()
    {
        return $this->reviewGroups;
    }

    /**
     * Set the value of reviewGroups
     */ 
    public function setReviewGroups($reviewGroups)
    {
        $this->reviewGroups = $reviewGroups;
    }

    /**
     * Get the value of userInfoGroups
     */ 
    public function getUserInfoGroups()
    {
        return $this->userInfoGroups;
    }

    /**
     * Set the value of userInfoGroups
     */ 
    public function setUserInfoGroups($userInfoGroups)
    {
        $this->userInfoGroups = $userInfoGroups;
    }
 }