<?php
/*
 * This file is part of MODX Revolution.
 *
 * Copyright (c) MODX, LLC. All Rights Reserved.
 *
 * For complete copyright and license information, see the COPYRIGHT and LICENSE
 * files found in the top-level directory of this distribution.
 */

require_once (dirname(__DIR__).'/browser.class.php');

/**
 * Upload files to a directory
 *
 * @param string $path The target directory
 *
 * @package modx
 * @subpackage processors.browser.file
 */
class modBrowserFileUploadProcessor extends modBrowserProcessor {

    public $permission = 'file_upload';
    public $policy = 'create';
    public $languageTopics = ['file'];
    public $objectType = 'file';

    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {
            $path = $this->getProperty('path');
            $response = $this->source->uploadObjectsToContainer($path, $_FILES);
        }
        return $this->handleResponse($response);
    }

}
return 'modBrowserFileUploadProcessor';
