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
 * Send a file to user
 *
 * @property string $file The absolute path of the file
 *
 * @package modx
 * @subpackage processors.browser.file
 */
class modBrowserFileDownloadProcessor extends modBrowserProcessor {

    public $permission = 'file_view';
    public $policy = 'view';
    public $languageTopics = ['file'];
    public $objectType = 'file';

    public function process() {

        $response = null;

        if ($this->prepareBrowserItem()) {
            $file = $this->getProperty('file');

            // Manager asks for file url
            if (!$this->getProperty('download')) {
                return $this->success('', ['url' => rawurlencode($this->source->getObjectUrl($file))]);
            }

            // Download file
            @session_write_close();
            try {
                if ($data = $this->source->getObjectContents($file)) {
                    $name = preg_replace('/[^\w\-.]/ui', '_', $data['basename']);
                    header('Content-type: ' . $data['mime']);
                    header('Content-Length: ' . $data['size']);
                    header('Content-Disposition: attachment; filename=' . $name);

                    exit($data['content']);
                } else {
                    exit($this->modx->lexicon('file_err_open') . $this->getProperty('file'));
                }
            } catch (Exception $e) {
                exit($e->getMessage());
            }
        }
        return $this->handleResponse($response);
    }

}
return 'modBrowserFileDownloadProcessor';
