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
 * Updates a file.
 *
 * @param string $file The absolute path of the file
 * @param string $name Will rename the file if different
 * @param string $content The new content of the file
 *
 * @package modx
 * @subpackage processors.browser.file
 */
class modBrowserFileUpdateProcessor extends modBrowserProcessor {

    public $permission = 'file_update';
    public $policy = 'save';
    public $languageTopics = ['file'];
    public $objectType = 'file';

    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {
            $file = $this->getProperty('file');
            $response = $this->source->updateObject($file, $this->getProperty('content', ''));
        }
        return $this->handleResponse($response);
    }

}
return 'modBrowserFileUpdateProcessor';
