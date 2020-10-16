<?php
/*
 * This file is part of the MODX Revolution package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for Browser processors. To be extended for each derivative.
 *
 * @abstract
 *
 * @package processors.browser.browser
 */
abstract class modBrowserProcessor extends modProcessor
{
    /** @var modMediaSource $source */
    public $source;
    /** @var string $permission A required user permission */
    public $permission = '';
    /** @var string $policy A required media source policy */
    public $policy = '';
    /** @var array $languageTopics An array of language topics to load */
    public $languageTopics = [];

    public $currentAction = '';
    public $actionsWithFieldErrors = ['create', 'rename'];

    /**
     * @return bool
     */
    public function checkPermissions() {
        return !empty($this->permission)
            ? $this->modx->hasPermission($this->permission)
            : true;
    }

    /**
     * @return array
     */
    public function getLanguageTopics() {
        return $this->languageTopics;
    }

    /**
     * @return bool|null|string
     */
    public function initialize() {
        // $this->modx->log(modX::LOG_LEVEL_ERROR, 'New browser parent class initializing...', '', __CLASS__, __FILE__, __LINE__);
        $this->currentAction = $this->getCurrentAction();
        if (!$this->getSource()) {
            return $this->modx->lexicon('permission_denied');
        }
        if ($this->policy && !$this->source->checkPolicy($this->policy)) {
            return $this->modx->lexicon('permission_denied');
        }

        return true;
    }

    /**
     * Get the active Source
     *
     * @return modMediaSource|bool
     */
    public function getSource() {
        $source = $this->getProperty('source', 1);
        $this->source = modMediaSource::getDefaultSource($this->modx, $source);
        if (!$this->source->getWorkingContext()) {
            return $this->modx->lexicon('permission_denied');
        }
        $this->source->setRequestProperties($this->getProperties());
        if (!$this->source->initialize()) {
            // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not init source obj!', '', __CLASS__, __FILE__, __LINE__);
            return false;
        }

        return $this->source;
    }

    /**
     * Get the list of protected directories
     *
     * @return array
     */
    public function getProtectedPathDirectories() {
        $protectedDirectories = [
            MODX_ASSETS_PATH,
            MODX_BASE_PATH,
            MODX_CONNECTORS_PATH,
            MODX_CORE_PATH,
            MODX_MANAGER_PATH,
            MODX_PROCESSORS_PATH,
            XPDO_CORE_PATH,
        ];

        return $protectedDirectories;
    }

    /**
     * @param $response
     *
     * @return array|string
     */
    public function handleResponse($response) {
        if (empty($response)) {
            // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Response, handleResponse(): '.print_r($response, true), '', __CLASS__, __FILE__, __LINE__);
            $message = ''; // not in 3.x, should it be?
            $errors = $this->source->getErrors();
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Errors, handleResponse(): '.print_r($errors, true), '', __CLASS__, __FILE__, __LINE__);
            if (count($errors) > 1) {
                foreach ($errors as $key => $message) {
                    $this->modx->error->addField($key, $message); // from 3.x, does same as 2.x but directly
                    // $this->addFieldError($key,$message); // 2.x
                }
                // return $this->failure($message); // 2.x
                return $this->failure(); // from 3.x
            } else {
                // return $this->failure(array_shift($errors));
                return $this->failure();
            }
        }

        return $this->success(); // from 3.x
        // return $this->success('',array(
        //     'file' => $directory.ltrim($name,'/'),
        // )); // 2.x

    }

    /**
     * Prepare directory and file name and path properties for pre-validation
     *
     * @param string $subjectPath A relative path dependent upon the browser object's type and current action being requested
     * @param string $itemName The new name for a directory or file
     *
     * @return bool Returns false if pre-validation fails and triggers an error message
     */
    public function prepareBrowserItem() {

        $this->modx->log(modX::LOG_LEVEL_ERROR, 'Mode of "'.$this->currentAction.'" derived from calling class: '.get_class($this), '', __CLASS__, __FILE__, __LINE__);
        $this->modx->log(modX::LOG_LEVEL_ERROR, 'Properties: '.print_r($this->getProperties(), true), '', __CLASS__, __FILE__, __LINE__);
        // $instantiator = get_class($this);
        // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Instantiating class, prepareBrowserItem(): '.$instantiator, '', __CLASS__, __FILE__, __LINE__);
        // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Object type, prepareBrowserItem(): '.$this->objectType, '', __CLASS__, __FILE__, __LINE__);
        $primaryIdentifierMap = [
            'file' => [
                'create'    => [ 'field' => 'name', 'errLexKey' => '_err_ns_name', 'errTarget' => 'field' ],
                'rename'    => [ 'field' => 'name', 'errLexKey' => '_err_ns_name', 'errTarget' => 'field' ],
                'remove'    => [ 'field' => 'file', 'errLexKey' => '_err_ns', 'errTarget' => 'window' ],
                'update'    => [ 'field' => 'file', 'errLexKey' => '_err_ns', 'errTarget' => 'window' ],
                'get'       => [ 'field' => 'file', 'errLexKey' => '_err_ns', 'errTarget' => 'window' ],
                'download'  => [ 'field' => 'file', 'errLexKey' => '_err_download_file_unspecified', 'errTarget' => 'window' ],
                'upload'    => [ 'field' => 'path', 'errLexKey' => '_err_upload_directory_unspecified', 'errTarget' => 'window' ],
                'unpack'    => [ 'field' => 'file', 'errLexKey' => '_err_ns', 'errTarget' => 'window' ],
            ],
            'directory' => [
                'create'    => [ 'field' => 'name', 'errLexKey' => '_err_ns_name', 'errTarget' => 'field' ],
                'rename'    => [ 'field' => 'name', 'errLexKey' => '_err_ns_name', 'errTarget' => 'field' ],
                'update'    => [ 'field' => 'name', 'errLexKey' => '_err_ns_name', 'errTarget' => 'field' ],
            ]
        ];

        // Assess the presence of the required value of this object's primary identifying field/property
        $primaryFieldName = $primaryIdentifierMap[$this->objectType][$this->currentAction]['field'];
        $primaryFieldErrLexKey = $primaryIdentifierMap[$this->objectType][$this->currentAction]['errLexKey'];
        $primaryFieldVal = $this->getProperty($primaryFieldName);

        if (!($this->objectType == 'file' && $this->currentAction == 'upload' && $primaryFieldVal === '/')) {
            $primaryFieldVal = $this->sanitize($primaryFieldVal);
        }
        // Force failure test
        // if ( $this->objectType == 'file' && $this->currentAction == 'download' ) {
            // $primaryFieldVal = null;
        // }
        $this->setProperty($primaryFieldName, $primaryFieldVal);
        
        if (empty($primaryFieldVal)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Primary field is empty, prepareBrowserItem()', '', __CLASS__, __FILE__, __LINE__);
            if ($primaryIdentifierMap[$this->objectType][$this->currentAction]['errTarget'] == 'field') {
                $this->addFieldError($primaryFieldName,'My err: '.$this->modx->lexicon($this->objectType.$primaryFieldErrLexKey));
            } else {
                $this->failure($this->modx->lexicon($this->objectType.$primaryFieldErrLexKey));
            }
            return false;
        }

        if ($this->objectType === 'file') {

            switch ($this->currentAction) {
                case 'create':
                    // $containerPath is relative path to directory we're attempting to write this file to
                    $containerPath = $this->sanitize($this->getProperty('directory', ''), 'directory');
                    // $this->setProperty('directory', $containerPath);
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Create $containerPath, prepareBrowserItem(): '.$containerPath, '', __CLASS__, __FILE__, __LINE__);
                    return $this->validateBrowserItem($containerPath, $primaryFieldVal);
                    break;

                case 'rename':
                    // $originalPath is the relative path to the original file
                    //
                    $originalPath = $this->getProperty('path');
                    $originalPathClean = $this->sanitize($originalPath);
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Rename $originalPath, prepareBrowserItem(): '.$originalPath, '', __CLASS__, __FILE__, __LINE__);
                    // if ($originalPath != $originalPathClean) {
                    //     // Should the original file path have an invalid format, exit here with warning to manually fix issue
                    //     $this->failure(sprintf($this->modx->lexicon($this->objectType.'_err_rename_original_unclean'), $originalPath, $originalPathClean));
                    //     return false;
                    // }
                    return $this->validateBrowserItem($originalPath, $primaryFieldVal);
                    break;
                case 'unpack':
                    // $containerPath is relative path of this media source
                    // $primaryFieldVal is relative path to file (not just the file name)
                    $containerPath = $this->sanitize($this->getProperty('path'), 'path');
                    // $this->setProperty('path', $containerPath);
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Unpack $containerPath, prepareBrowserItem(): '.$containerPath, '', __CLASS__, __FILE__, __LINE__);
                    return $this->validateBrowserItem($containerPath, $primaryFieldVal);
                    // return true;
                    break;
                // No user input to prepare or pre-validate here
                case 'get':
                case 'download':
                case 'remove':
                case 'update':
                case 'upload':
                    return true;
                    break;
            }

        } else {

            switch ($this->currentAction) {
                case 'create':
                    // $containerPath is relative path to directory we're attempting to write a new directory within
                    $containerPath = $this->sanitize($this->getProperty('parent', ''), 'parent');
                    // $this->setProperty('parent', $containerPath);
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Create $containerPath (parent property), prepareBrowserItem(): '.$containerPath, '', __CLASS__, __FILE__, __LINE__);
                    return $this->validateBrowserItem($containerPath, $primaryFieldVal);
                    break;
                case 'rename':
                case 'update':
                    // $originalPath is the relative path to the original directory we're renaming
                    $originalPath = $this->sanitize($this->getProperty('path'), 'path');
                    // $this->setProperty('path', $originalPath);
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Rename $path, prepareBrowserItem(): '.$originalPath, '', __CLASS__, __FILE__, __LINE__);
                    return $this->validateBrowserItem($originalPath, $primaryFieldVal);
                    break;
                // No user input to prepare or pre-validate here
                case 'remove':
                    break;
            }
        }
    }

    /**
     * Pre-validate item before attempting to create
     *
     * @param string $subjectPath A relative path dependent upon the browser object's type and current action being requested
     * @param string $itemName The new name for a directory or file
     *
     * @return bool
     */
    public function validateBrowserItem(string $subjectPath = '', string $itemName = '') : bool {

        $this->modx->log(modX::LOG_LEVEL_ERROR, '1: $subjectPath, validateBrowserItem(): '.$subjectPath, '', __CLASS__, __FILE__, __LINE__);
        $this->modx->log(modX::LOG_LEVEL_ERROR, '2: $itemName, validateBrowserItem(): '.$itemName, '', __CLASS__, __FILE__, __LINE__);
        $isValid = true;

        $basePath = $this->source->getBasePath($subjectPath);
        $this->modx->log(modX::LOG_LEVEL_ERROR, '3: $basePath, validateBrowserItem(): '.$basePath, '', __CLASS__, __FILE__, __LINE__);

        $previousItemPath = '';
        $itemFullPath = '';

        if($this->objectType === 'file') {
            // Working with a regular file...
            $expectFileExists = false;
            switch ($this->currentAction) {
                case 'create':
                    $itemFullPath = $basePath.ltrim($subjectPath,'/').ltrim($itemName,'/');
                    $isValid = $this->isValidItemName($itemName, $itemFullPath);
                    break;
                case 'rename':
                    $previousItemPath = $basePath.$subjectPath;
                    $itemFullPath = dirname($previousItemPath).'/'.$itemName;
                    $isValid = $this->isValidItemName($itemName, $itemFullPath);
                    break;
                case 'update':
                    $itemFullPath = $basePath.$subjectPath;
                    break;
                case 'remove':
                    $expectFileExists = true;
                    // $fullPath = $basePath;
                    break;
                case 'unpack':
                    $expectFileExists = true;
                    $itemFullPath = $basePath.$itemName;
                    $isValid = $this->isValidItemName($itemName, $itemFullPath);


                    $itemDirectory = dirname($itemFullPath);
                    // $this->modx->log(modX::LOG_LEVEL_ERROR, '$itemName, validateBrowserItem(): '.$itemName, '', __CLASS__, __FILE__, __LINE__);
                    if (!is_writable($itemDirectory)) {
                        $i = strrpos($itemName, '/');
                        $directoryDisplay = $i !== false ? substr($itemName, 0, $i+1) : '(root)' ;
                        $this->failure(sprintf($this->modx->lexicon($this->objectType.'_err_unpack_directory_locked'), $directoryDisplay));
                        $isValid = false;
                    }
                    break;
            }
            $this->modx->log(modX::LOG_LEVEL_ERROR, '4: New file path, validateBrowserItem(): '.$itemFullPath, '', __CLASS__, __FILE__, __LINE__);

            if ($expectFileExists) {
                if (!file_exists($itemFullPath)) {
                    // $this->modx->log(modX::LOG_LEVEL_ERROR, 'File ('.$itemFullPath.') does not exist, validateBrowserItem()', '', __CLASS__, __FILE__, __LINE__);
                    $this->failure(sprintf($this->modx->lexicon($this->objectType.'_err_unpack_not_found'), $itemName));
                    $isValid = false;
                }
            } else {
                if (file_exists($itemFullPath)) {
                    if (is_dir($itemFullPath)) {
                        $msgKey = $this->objectType.'_err_is_folder';
                    } else {
                        $msgKey = $this->objectType.'_err_ae';
                    }
                    $this->addFieldError('name','My err: '.sprintf($this->modx->lexicon($msgKey), $itemName));
                    $isValid = false;
                } else {
                    // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Checking extension for file, validateBrowserItem(): '.$itemFullPath, '', __CLASS__, __FILE__, __LINE__);
                    $ext = pathinfo($itemFullPath, PATHINFO_EXTENSION);
                    // $this->modx->log(modX::LOG_LEVEL_ERROR, 'File extension, validateBrowserItem(): '.$ext, '', __CLASS__, __FILE__, __LINE__);
                    $ext = strtolower($ext);
                    if(empty($ext)) {
                        $this->addFieldError('name','My err: '.$this->modx->lexicon($this->objectType.'_err_ext_ns'));
                        $isValid = false;
                    } else {
                        if (!$this->source->checkFiletype($itemFullPath)) {
                            $allowed = $this->source->getOption('allowedFileTypes');
                            // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Allowed file types, validateBrowserItem(): '.print_r($allowed, true), '', __CLASS__, __FILE__, __LINE__);
                            $this->addFieldError('name','My err: '.$this->modx->lexicon($this->objectType.'_err_ext_not_allowed',
                                [ 'ext' => $ext ]
                            ));
                            $isValid = false;
                        }
                    }
                }
            }

        } else {
            // Working with a directory...

            // A positive match test for legal use of a dot in directory name (only at the beginning)
            $hasLegalDotChar = preg_match_all('/^\.?[^.]+$/', $itemName);
            // $this->modx->log(modX::LOG_LEVEL_ERROR, '$hasLegalDotChar val, validateBrowserItem(): '.$hasLegalDotChar, '', __CLASS__, __FILE__, __LINE__);
            if ($hasLegalDotChar === 0) {
                $this->addFieldError('name','My err: '.sprintf($this->modx->lexicon($this->objectType.'_err_invalid_dots'), $itemName));
                $isValid = false;
            }

            switch ($this->currentAction) {
                case 'create':
                    $containerPath = $basePath.ltrim($subjectPath,'/');
                    $itemFullPath = $containerPath.'/'.$itemName;
                    if (strpos($itemName,'/') !== false) {
                        $this->addFieldError('name','My err: '.$this->modx->lexicon($this->objectType.'_err_nesting_unsupported'));
                        $isValid = false;
                    }
                    if (!is_dir($containerPath)) {
                        $this->addFieldError('parent','My err: '.sprintf($this->modx->lexicon($this->objectType.'_err_parent_invalid'), $subjectPath));
                        $isValid = false;
                    }
                    break;
                case 'rename':
                    // $subjectPath is relative path to original directory we're renaming
                    if (strpos($itemName,'/') !== false) {
                        $this->addFieldError('name','My err: '.$this->modx->lexicon($this->objectType.'_err_name_is_path'));
                        $isValid = false;
                    }
                    $containerPath = dirname($basePath.$subjectPath);
                    $itemFullPath = $containerPath.'/'.$itemName;
                    break;
                case 'update':
                    $fullPath = $basePath;
                    break;
                case 'remove':
                    $fullPath = $basePath;
                    break;
            }
            $this->modx->log(modX::LOG_LEVEL_ERROR, '4: New directory path, validateBrowserItem(): '.$itemFullPath, '', __CLASS__, __FILE__, __LINE__);

            if (file_exists($itemFullPath)) {
                $this->addFieldError('name','My err: '.sprintf($this->modx->lexicon($this->objectType.'_err_ae'), $itemName));
                $isValid = false;
            }
        }
        // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Bases, validateBrowserItem(): '.print_r($bases, true), '', __CLASS__, __FILE__, __LINE__);

        return $isValid;
    }

    /**
     * Validate item name, looking specifically for the existance and writability of its containing directory.
     * Also issues error when invalid name or container is found
     *
     * @param string $itemName The current value of the name field
     * @param string $itemFullPath Complete path to the directory or file
     * @param string $fieldKey Optional; The form field where error message will be reported
     * @param array $references Optional; Associative array of named template values for use in message display via lexicon entries
     *
     * @return bool
     */
    public function isValidItemName(string $itemName, string $itemFullPath, string $fieldKey = 'name', array $references = []) : bool {

        $isValid = true;
        $reportFieldErrors = in_array($this->currentAction, $this->actionsWithFieldErrors) ? true : false ;
        $i = strrpos($itemName, '/');
        if ($i !== false) {
            $container = dirname($itemFullPath);
            // Make a display-friendly reference of the containing directory
            // $basePath = $this->source->getBasePath($itemFullPath);
            // $references['container'] = substr($itemName, 0, $i+1);
            $references['container'] = str_replace($this->source->getBasePath($itemFullPath), '', $container);
            $this->modx->log(modX::LOG_LEVEL_ERROR, '$container to write to, isValidItemName(): '.$container.'; $itemName: '.$itemName.'; Last slash index: '.$i, '', __CLASS__, __FILE__, __LINE__);
            $this->modx->log(modX::LOG_LEVEL_ERROR, '$container display, isValidItemName(): '.$references['container'], '', __CLASS__, __FILE__, __LINE__);
            // $this->modx->log(modX::LOG_LEVEL_ERROR, 'source->getPath(), isValidItemName(): '.$p, '', __CLASS__, __FILE__, __LINE__);
            if (!is_dir($container)) {
                $msg = $this->modx->lexicon($this->objectType.'_err_'.$this->currentAction.'_container_not_exists');
                $isValid = false;
            } else if (!is_writable($container)) {
                $msg = $this->modx->lexicon($this->objectType.'_err_'.$this->currentAction.'_container_not_writable');
                $isValid = false;
            }

            // Convert named references to indexed ones for use in vsprintf
            if (!empty($references)) {
                $idx = 1;
                foreach ($references as $k => $v) {
                    $msg = str_replace("%{$k}$", "%{$idx}$", $msg);
                    $idx++;
                }
                $msg = vsprintf($msg, array_values($references));
            }

            // Issue error, if any, to the appropriate context (field or status)
            if (!$isValid) {
                if ($reportFieldErrors) {
                    $this->addFieldError($fieldKey, $msg);
                } else {
                    $this->failure($msg);
                }
            }

        }
        $this->modx->log(modX::LOG_LEVEL_ERROR, '$isValid, isValidItemName(): '.$isValid, '', __CLASS__, __FILE__, __LINE__);
        return $isValid;
    }

    /**
     * @param string $itemVal The item's value to clean
     * @param string $itemKey The item's corresponding property name; when present the value will be re-set on the specified property.
     * @param bool $isFullPath Use to indicate the value is a full (relative or absolute) path instead of a file/directory name.
     *
     * @return string
     */
    public function sanitize(string $itemVal, string $itemKey = '', bool $isFullPath = false) : string {
        $itemVal = trim($itemVal);
        if (!empty($itemVal)) {
            $itemVal = rawurldecode($itemVal);
            /*
                Note: removed strip_tags, as it removes everything after the first left angle
                bracket (<) when no right angle bracket is present, which can lead to some confusion;
                it's better to explicity remove the brackets via regex
            */
            $itemVal = preg_replace('/[<>]/', '', $itemVal);
            // Files may have multiple extensions or may be hidden with the dot prefix, so only sub single dot for multiple consecutive ones
            if ($this->objectType == 'file') {
                // $itemVal = preg_replace('/[\.]{2,}/', '.', $itemVal);
            } else {
                // Allow dot prefix on directory items, but replace all others
                // $itemVal = strpos($itemVal, '.') === 0 ? '.'.str_replace('.', '', $itemVal) : str_replace('.', '', $itemVal) ;
            }
            $itemVal = preg_replace('/[\.]{2,}/', '.', $itemVal);

            // Double slash to single slash
            $itemVal = preg_replace('/[\/]{2,}/', '/', $itemVal);

            if (!$isFullPath) {
                // Names should never begin with slash
                $itemVal = preg_replace('/^[\/]+/', '', $itemVal);
            }

            // Avoid known issue on Windows when ending names with dot
            $itemVal = rtrim($itemVal, '.');

            // Final value prep
            $itemVal = htmlspecialchars($itemVal);
        }
        if (!empty($itemKey)) {
            $this->setProperty($itemKey, $itemVal);
        }
        return $itemVal;
    }
}
