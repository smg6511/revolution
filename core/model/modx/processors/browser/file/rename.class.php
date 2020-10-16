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
 * Renames a file
 *
 * @param string $file The file to rename
 * @param string $newname The new name for the file
 *
 * @package modx
 * @subpackage processors.browser.file
 */
class modBrowserFileRenameProcessor extends modBrowserProcessor {

    public $permission = 'file_update';
    public $policy = 'save';
    public $languageTopics = ['file'];
    public $objectType = 'file';

    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {
            $path = $this->getProperty('path');
            $name = $this->getProperty('name');
            $response = $this->source->renameObject($path, $name);
        }
        return $this->handleResponse($response);
    }

}
return 'modBrowserFileRenameProcessor';
