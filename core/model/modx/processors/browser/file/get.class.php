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
 * Gets the contents of a file, used by Quick Update File
 *
 * @param string $file The absolute path of the file
 *
 * @package modx
 * @subpackage processors.browser.file
 */
class modBrowserFileGetProcessor extends modBrowserProcessor {

    public $permission = 'file_view';
    public $languageTopics = ['file'];
    public $objectType = 'file';

    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {

            $file = $this->getProperty('file','');
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Get class called, $file: '.$file, '', __CLASS__, __FILE__, __LINE__);

            // Shouldn't this be checking for 'save' policy?
            if (!$this->source->checkPolicy('delete')) {
                return $this->failure($this->modx->lexicon('permission_denied'));
            }

            $response = $this->source->getObjectContents($file);
        }

        return $this->handleResponse($response);
    }

}
return 'modBrowserFileGetProcessor';
