<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\DocumentNumberSchemes;

class DocumentNumberSchemeOptions
{
    public function resetNumberOptions()
    {
        return [
            '0' => trans('fi.never'),
            '1' => trans('fi.yearly'),
            '2' => trans('fi.monthly'),
            '3' => trans('fi.weekly'),
        ];
    }
}