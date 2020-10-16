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
 * Abstract class for Update Element processors. To be extended for each derivative element type.
 *
 * @abstract
 * @package modx
 * @subpackage processors.element
 */
abstract class modElementUpdateProcessor extends modObjectUpdateProcessor {
    public $previousCategory;
    /** @var modElement $object */
    public $object;

    public function beforeSet() {
        // Make sure the element isn't locked
        if ($this->object->get('locked') && !$this->modx->hasPermission('edit_locked')) {
            return $this->modx->lexicon($this->objectType.'_err_locked');
        }

        return parent::beforeSet();
    }

    public function beforeSave() {
        $locked = $this->getProperty('locked');
        if (!is_null($locked)) {
            $this->object->set('locked',(boolean)$locked);
        }

        $this->prepareEntityName();

        /* category */
        $category = $this->object->get('category');
        $this->previousCategory = $category;
        if (!empty($category)) {
            $category = $this->modx->getObject('modCategory',array('id' => $category));
            if (empty($category)) {
                $this->addFieldError('category',$this->modx->lexicon('category_err_nf'));
            }
        }

        /* can't change content if static source is not writable */
        if ($this->object->staticContentChanged()) {
            if (!$this->object->isStaticSourceMutable()) {
                $this->addFieldError('static_file', $this->modx->lexicon('element_static_source_immutable'));
            } else if (!$this->object->isStaticSourceValidPath()) {
                $this->addFieldError('static_file',$this->modx->lexicon('element_static_source_protected_invalid'));
            }
        }

        return !$this->hasErrors();
    }

    public function afterSave() {
        if ($this->getProperty('clearCache',true)) {
            $this->modx->cacheManager->refresh();
        }
    }

    public function cleanup() {
        $fields = array_unique(['id', $this->getEntityNameField(), 'description', 'locked', 'category', 'content']);
        return $this->success('',array_merge($this->object->get($fields), array('previous_category' => $this->previousCategory)));
    }
}
