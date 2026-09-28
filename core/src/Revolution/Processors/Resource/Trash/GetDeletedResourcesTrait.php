<?php

/*
 * This file is part of MODX Revolution.
 *
 * Copyright (c) MODX, LLC. All Rights Reserved.
 *
 * For complete copyright and license information, see the COPYRIGHT and LICENSE
 * files found in the top-level directory of this distribution.
 */

namespace MODX\Revolution\Processors\Resource\Trash;

use modResource;
use MODX\Revolution\modContext;
use xPDOQuery;

trait GetDeletedResourcesTrait
{
    /**
     * Gets a collection of deleted Resources accessible to the current user,
     * honoring Context and Resource group permissions.
     *
     * @return array An array of Resource objects
     */
    protected function getDeletedResources(): array
    {
        $contexts = $this->modx->getCollection(modContext::class, ['key:!=' => 'mgr']);
        if (!$contexts) {
            return [];
        }
        $listableContexts = [];
        foreach ($contexts as $context) {
            if ($context->checkPolicy('list')) {
                $listableContexts[] = $context->get('key');
            }
        }
        if (empty($listableContexts)) {
            return [];
        }
        $resourceClass = $this->classKey ?? modResource::class;
        $c = $this->modx->newQuery($resourceClass);
        $c->select($this->modx->getSelectColumns($resourceClass, $c->getAlias(), '', ['id', 'context_key']));
        $c->where([
            $c->getAlias() . '.deleted' => true,
            $c->getAlias() . '.context_key:IN' => $listableContexts
        ]);

        // Note that getCollection() handles filtering out items in resource groups not accessible to the user
        $resources = $this->modx->getCollection($resourceClass, $c);

        return $resources;
    }

    /**
     * Gets the count of deleted Resources accessible to the current user.
     */
    private function getDeletedResourcesCount(): int
    {
        $deletedResources = $this->getDeletedResources();
        return !empty($deletedResources) ? count($deletedResources) : 0;
    }

    /**
     * Sets the deleted resource count on the processor's object.
     */
    protected function setDeletedResourceCount(): void
    {
        $this->object->set('deletedCount', $this->getDeletedResourcesCount());
    }

    /**
     * Updates the deleted resource count in the output array.
     *
     * @param array $outputArray The output array to update
     */
    protected function updateDeletedResourceCount(array &$outputArray): void
    {
        $outputArray['deletedCount'] = $this->getDeletedResourcesCount();
    }
}
