<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Support;

class TranslationOverride
{
    public static function override($originalPath, $originalTranslations)
    {
        $overrides = [];
        $ds = DIRECTORY_SEPARATOR;
        $arrOriginalPath = explode($ds, $originalPath);
        $overridesRelPath = implode($ds, array_slice($arrOriginalPath, -3));
        $originalPath = implode($ds, array_slice($arrOriginalPath, 0, -1));
        $overridesPath = $originalPath . $ds . '..' . $ds . '..' . $ds . '..'
            . $ds . 'custom' . $ds . 'overrides' . $ds . $overridesRelPath;

        if (file_exists($overridesPath))
        {
            $overrides = (array) include_once($overridesPath);
        }

        return array_merge($originalTranslations, $overrides);
    }
}