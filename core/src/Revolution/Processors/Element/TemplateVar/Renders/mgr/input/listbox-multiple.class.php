<?php

/*
 * This file is part of MODX Revolution.
 *
 * Copyright (c) MODX, LLC. All Rights Reserved.
 *
 * For complete copyright and license information, see the COPYRIGHT and LICENSE
 * files found in the top-level directory of this distribution.
 */

// namespace MODX\Revolution\Processors\Element\TemplateVar\Renders\mgr\input;

use MODX\Revolution\modTemplateVarInputRender;

/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */
class modTemplateVarInputRenderListboxMultiple extends modTemplateVarInputRender
{
    public function render($value, array $params = [])
    {
        $this->useNewLoader = true;
        $value = str_replace('||', ',', $value);

        $this->componentConfig = [
            'columnWidth' => $params['columnWidth'],
            'groupWith' => $params['groupWith'],
            'config' => [
                'xtype' => 'superboxselect',
                'id' => 'tv' . $params['id'],
                'name' => 'tv' . $params['id'] . '[]',
                'fieldLabel' => $params['caption'],
                'description' => $this->getComponentDescription($params['name'], $params['description']),
                'triggerAction' => 'all',
                'maxHeight' => 300,
                'extraItemCls' => 'x-tag',
                'expandBtnCls' => 'x-form-trigger',
                'clearBtnCls' => 'x-form-trigger',
                'listClass' => 'modx-superboxselect modx-tv-listbox-multiple',
                'addNewDataOnBlur' => true,
                'resizable' => true,
                'mode' => 'local',
                'store' => $this->getListData(),
                'value' => $value
            ]
        ];
        $this->componentConfig['config']['allowBlank'] = $params['allowBlank'] == 1 || $params['allowBlank'] === 'true' ? true : false ;

        if (!empty($params['title'])) {
            $this->componentConfig['config']['title'] = $params['title'];
        }
        if (!empty($params['listEmptyText'])) {
            $this->componentConfig['config']['listEmptyText'] = $params['listEmptyText'];
        }
        if ($params['typeAhead'] === 'true' || $params['typeAhead'] == 1) {
            $this->componentConfig['config']['editable'] = true;
            $this->componentConfig['config']['typeAhead'] = true;
            if (!empty($params['typeAheadDelay']) || $params['typeAheadDelay'] === 0) {
                $this->componentConfig['config']['typeAheadDelay'] = (int)$params['typeAheadDelay'];
            }
        } else {
            $this->componentConfig['config']['editable'] = false;
            $this->componentConfig['config']['typeAhead'] = false;
        }
        if ($params['stackItems'] === 'true' || $params['stackItems'] == 1) {
            $this->componentConfig['config']['stackItems'] = true;
        }
        if ($params['forceSelection'] === 'true' || $params['forceSelection'] == 1) {
            $this->componentConfig['config']['forceSelection'] = true;
        } else {
            $this->componentConfig['config']['allowAddNewData'] = true;
        }

        return $this->componentConfig;
    }

    private function getListData(): array
    {
        $options = $this->getInputOptions();
        $store = [];

        /*
            Note that in Ext JS the order is the opposite of how MODx formats associative arrays for stores
            (i.e., value->label [Ext] vs. label->value [MODx])

            Also, the previous version of this class re-ordered store items; this
            should be done on the js side via a listener to maintain order on the fly.
        */
        foreach ($options as $option) {
            $opt = explode('==', $option);
            $opt[0] = htmlspecialchars($opt[0], ENT_COMPAT | ENT_HTML5, 'UTF-8');
            if (count($opt) === 1) {
                $opt[1] = $opt[0];
            } else {
                $opt[1] = htmlspecialchars($opt[1], ENT_COMPAT | ENT_HTML5, 'UTF-8');
            }
            $store[] = [$opt[1], $opt[0]];
        }
        return $store;
    }
}
return 'modTemplateVarInputRenderListboxMultiple';
