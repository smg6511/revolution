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
 * Create a directory.
 *
 * @param string $name The name of the directory to create
 * @param string $parent The parent directory
 * @param boolean $prependPath (optional) If true, will prepend rb_base_dir to
 * the final path
 *
 * @package modx
 * @subpackage processors.browser.directory
 */
class modBrowserFolderCreateProcessor extends modBrowserProcessor {

    public $permission = 'directory_create';
    public $policy = 'create';
    public $languageTopics = ['file'];
    public $objectType = 'directory';

    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {
            $parent = $this->getProperty('parent');
            $name = $this->getProperty('name');
            // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Properties: '.print_r($this->getProperties(), true), '', __CLASS__, __FILE__, __LINE__);
            $response = $this->source->createContainer($name, $parent);
        }
        return $this->handleResponse($response);
    }

}
return 'modBrowserFolderCreateProcessor';
