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
 * Renames a directory
 *
 * @param string $file The file to rename
 * @param string $newname The new name for the file
 *
 * @package modx
 * @subpackage processors.browser.directory
 */
class modBrowserFolderRenameProcessor extends modBrowserProcessor {

    public $permission = 'directory_update';
    public $policy = 'save';
    public $languageTopics = ['file'];
    public $objectType = 'directory';

    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {
            $path = $this->getProperty('path');
            $name = $this->getProperty('name');
            // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Properties: '.print_r($this->getProperties(), true), '', __CLASS__, __FILE__, __LINE__);
            $response = $this->source->renameContainer($path, $name);
        }
        return $this->handleResponse($response);
    }

}
return 'modBrowserFolderRenameProcessor';
