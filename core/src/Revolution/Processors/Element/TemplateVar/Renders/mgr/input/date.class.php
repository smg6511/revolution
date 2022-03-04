<?php

/*
 * This file is part of MODX Revolution.
 *
 * Copyright (c) MODX, LLC. All Rights Reserved.
 *
 * For complete copyright and license information, see the COPYRIGHT and LICENSE
 * files found in the top-level directory of this distribution.
 */

use MODX\Revolution\modTemplateVarInputRender;

/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */
class modTemplateVarInputRenderDate extends modTemplateVarInputRender
{
    public function process($value, array $params = [])
    {
        $v = $value;
        if ($v != '' && $v != '0' && $v != '0000-00-00 00:00:00') {
            $v = date('Y-m-d H:i:s', strtotime($v));
        }
        $this->tv->set('value', $v);

        // if (!empty($params['disabledDates'])) {
        //     $params['disabledDates'] = $this->modx->toJSON(explode(',', $params['disabledDates']));
        // }
        // if (!empty($params['disabledDays'])) {
        //     $params['disabledDays'] = $this->modx->toJSON(explode(',', $params['disabledDays']));
        // }
        // if (!empty($params['maxTimeValue'])) {
        //     $params['maxTimeValue'] = date('g:i A', strtotime($params['maxTimeValue']));
        // }
        // if (!empty($params['minTimeValue'])) {
        //     $params['minTimeValue'] = date('g:i A', strtotime($params['minTimeValue']));
        // }
        // $this->setPlaceholder('params', $params);
        // $this->setPlaceholder('tv', $this->tv);
    }

    public function render($value, array $params = [])
    {
        // $this->modx->log(\modX::LOG_LEVEL_ERROR, "\r\n modTemplateVarInputRenderText render(), \$params: \r\n" . print_r($params, true), '', __CLASS__, __FILE__, __LINE__);
        $this->useNewLoader = true;

        $this->componentConfig = [
            'columnWidth' => $params['columnWidth'],
            'groupWith' => $params['groupWith'],
            'config' => [
                'xtype' => 'xdatetime',
                'id' => 'tv' . $params['id'],
                'name' => 'tv' . $params['id'],
                'fieldLabel' => $params['caption'],
                'description' => $this->getComponentDescription($params['name'], $params['description']),
                'allowBlank' => $params['allowBlank'],
                'hideTime' => $params['hideTime'] === 'true' || $params['hideTime'] == 1 ? true : false,
                // 'enableKeyEvents' => true,
                'dateFormat' => $this->modx->getOption('manager_date_format'),
                'timeFormat' => $this->modx->getOption('manager_time_format'),
                'dateWidth' => 198, // make these configurable?
                'timeWidth' => 198, // also, with new loader these are not sizing correctly
                'value' => $value
            ]
        ];

        if (!empty($params['startDay'])) {
            $this->componentConfig['config']['startDay'] = (int)$params['startDay'];
        }
        if (!empty($params['disabledDates'])) {
            // $params['disabledDates'] = $this->modx->toJSON(explode(',', $params['disabledDates']));
            $params['disabledDates'] = explode(',', $params['disabledDates']);
        }
        if (!empty($params['disabledDays'])) {
            // $params['disabledDays'] = $this->modx->toJSON(explode(',', $params['disabledDays']));
            $params['disabledDays'] = explode(',', $params['disabledDays']);
        }
        if (!empty($params['maxTimeValue'])) {
            $params['maxTimeValue'] = date('g:i A', strtotime($params['maxTimeValue']));
        }
        if (!empty($params['minTimeValue'])) {
            $params['minTimeValue'] = date('g:i A', strtotime($params['minTimeValue']));
        }
        if (!empty($params['timeIncrement'])) {
            $this->componentConfig['config']['timeIncrement'] = (int)$params['timeIncrement'];
        }
        if (!empty($params['minDateValue'])) {
            $this->componentConfig['config']['minDateValue'] = $params['minDateValue'];
        }
        if (!empty($params['maxDateValue'])) {
            $this->componentConfig['config']['maxDateValue'] = $params['maxDateValue'];
        }

        return $this->componentConfig;
    }
    // public function getTemplate() {
    //     return 'element/tv/renders/input/date.tpl';
    // }
}

return 'modTemplateVarInputRenderDate';
