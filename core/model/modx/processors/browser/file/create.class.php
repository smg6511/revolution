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
 * Creates a file.
 *
 * @param string $file The absolute path of the file
 * @param string $name Will rename the file if different
 * @param string $content The new content of the file
 *
 * @package modx
 * @subpackage processors.browser.file
 */
class modBrowserFileCreateProcessor extends modBrowserProcessor {

    public $permission = 'file_create';
    public $policy = 'create';
    public $languageTopics = ['file'];
    public $objectType = 'file';

    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {
            $directory = $this->getProperty('directory');
            $name = $this->getProperty('name');
            $response = $this->source->createObject($directory, $name, $this->getProperty('content'));
        }

        return empty($response)
            ? $this->handleResponse($response)
            : $this->success('', [
                'file' => rawurlencode($directory.ltrim($name, '/')),
            ]);
    }

}
return 'modBrowserFileCreateProcessor';
