<?php

/*
 * This file is part of the MODX Revolution package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MODX\Revolution\Utilities\Converters;

use MODX\Revolution\modX;

/**
 * Provides utility methods to processors and controllers for converting text content from one format to another
 *
 * @package MODX\Revolution
 */
class modUtilsStringConverters
{
    /**
     * A reference to the modX object.
     * @var modX $modx
     */
    protected ?modX $modx = null;

    public function __construct(modX $modx)
    {
        $this->modx =& $modx;
    }

    /**
     * Converts an html-formatted string to JSON, configured such that it can be
     * reliably decoded and consumed as a value in a javascript config object
     *
     * @param string $string The unencoded html string
     * @return string The JSON-encoded string
     */
    public function htmlToJSON(string $string): string
    {
        $string = trim($string);
        if (empty($string)) {
            return '';
        }

        // Collapse presentational (code formatting) space
        $regex = '/(?<=>)[\s]*(?=<)/';

        return json_encode(
            preg_replace($regex, '', $string),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
        );
    }
}
