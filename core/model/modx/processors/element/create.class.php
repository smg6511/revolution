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
 * Abstract class for Create Element processors. To be extended for each derivative element type.
 *
 * @abstract
 * @package modx
 * @subpackage processors.element
 */
abstract class modElementCreateProcessor extends modObjectCreateProcessor {
    /** @var modElement $object */
    public $object;

    /**
     * Validate the form
     * @return boolean
     */
    public function beforeSave() {
        $locked = (boolean)$this->getProperty('locked',false);
        $this->object->set('locked',$locked);

        $this->prepareEntityName();

        $category = $this->getProperty('category',0);
        if (!empty($category)) {
            /** @var modCategory $category */
            $category = $this->modx->getObject('modCategory',array('id' => $category));
            if ($category === null) {
                $this->addFieldError('category',$this->modx->lexicon('category_err_nf'));
            }
            if ($category !== null && !$category->checkPolicy('add_children')) {
                $this->addFieldError('category',$this->modx->lexicon('access_denied'));
            }
        }

        $this->setElementProperties();
        $this->validateElement();

        if ($this->object->staticContentChanged()) {
            if ($this->object->get('content') !== '' && !$this->object->isStaticSourceMutable()) {
                $this->addFieldError('static_file', $this->modx->lexicon('element_static_source_immutable'));
            } else if (!$this->object->isStaticSourceValidPath()) {
                $this->addFieldError('static_file',$this->modx->lexicon('element_static_source_protected_invalid'));
            }
        }

        return !$this->hasErrors();
    }

    /**
     * Set the properties on the Element
     * @return mixed
     */
    public function setElementProperties() {
        $properties = null;
        $propertyData = $this->getProperty('propdata',null);
        if ($propertyData != null && is_string($propertyData)) {
            $propertyData = $this->modx->fromJSON($propertyData);
        }
        if (is_array($propertyData)) {
            $this->object->setProperties($propertyData);
        }
        return $propertyData;
    }

    /**
     * Run object-level validation on the element
     * @return void
     */
    public function validateElement() {
        if (!$this->object->validate()) {
            /** @var modValidator $validator */
            $validator = $this->object->getValidator();
            if ($validator->hasMessages()) {
                foreach ($validator->getMessages() as $message) {
                    $this->addFieldError($message['field'], $this->modx->lexicon($message['message']));
                }
            }
        }
    }

    /**
     * Clear the cache post-save
     * @return void
     */
    public function clearCache() {
        if ($this->getProperty('clearCache')) {
            $this->modx->cacheManager->refresh();
        }
    }

    /**
     * Cleanup the process and send back the response
     * @return array
     */
    public function cleanup() {
        $this->clearCache();
        $fields = array_unique(['id', $this->getEntityNameField(), 'description', 'locked', 'category']);
        return $this->success('',$this->object->get($fields));
    }
}
