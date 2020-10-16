<?php
/*
 * This file is part of MODX Revolution.
 *
 * Copyright (c) MODX, LLC. All Rights Reserved.
 *
 * For complete copyright and license information, see the COPYRIGHT and LICENSE
 * files found in the top-level directory of this distribution.
 */

/**
 * Abstract class for Duplicate Element processors. To be extended for each derivative element type.
 *
 * @abstract
 * @package modx
 * @subpackage processors.element
 */
class modElementDuplicateProcessor extends modObjectDuplicateProcessor {

    public function beforeSave() {
        // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Value for $this->getEntityNameField(), via beforeSave(): '.$this->getEntityNameField(), '', __CLASS__, __FILE__, __LINE__);
        $this->prepareEntityName();
        return !$this->hasErrors();
    }

    public function afterSave() {
        if ($this->getProperty('clearCache')) {
            $this->modx->cacheManager->refresh();
        }
        return parent::afterSave();
    }

    public function cleanup() {
        // $this->modx->log(modX::LOG_LEVEL_ERROR, 'Value for $this->getEntityNameField(), via cleanup(): '.$this->getEntityNameField(), '', __CLASS__, __FILE__, __LINE__);
        $fields = array_unique(['id', $this->getEntityNameField(), 'description', 'locked', 'category']);
        return $this->success('',$this->newObject->get($fields));
    }

}
