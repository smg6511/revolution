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
 * Unpacks archives, currently only zip
 *
 * @package modx
 * @subpackage processors.browser.file
 */
class modUnpackProcessor extends modBrowserProcessor {

    public $permission = 'file_unpack';
    public $policy = 'view';
    public $languageTopics = ['file'];
    public $objectType = 'file';

    /**
     * {@inheritDoc}
     *
     * @return array|string
     */
    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {
            $target = htmlspecialchars($this->modx->getOption('base_path').$this->getProperty('path').$this->getProperty('file'));
            $file = $this->source->fileHandler->make($target);
            if (!$file->unpack(dirname($target), ['check_filetype' => true])) {
                return $this->failure($this->modx->lexicon('file_err_unzip'));
            }
            $fileName = $file->getBaseName();
            return $this->success(sprintf($this->modx->lexicon('file_unpacked_msg'), $fileName, str_replace($fileName, '', $this->getProperty('file'))));

        }
        return $this->handleResponse($response);
    }

}
return 'modUnpackProcessor';
