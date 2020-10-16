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
 * Create a category.
 *
 * @param string $category The new name of the category.
 *
 * @package modx
 * @subpackage processors.element.category
 */
class modElementCategoryCreateProcessor extends modObjectCreateProcessor {
    public $classKey = 'modCategory';
    public $languageTopics = array('category');
    public $permission = 'save_category';
    public $objectType = 'category';

    /**
     * Validate the creation
     * @return boolean
     */
    public function beforeSave() {
        parent::prepareEntityName();
        return parent::beforeSave();
    }
}
return 'modElementCategoryCreateProcessor';
