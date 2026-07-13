<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Composers;

use FI\Support\DateFormatter;

class DateTimePickerComposer
{
    public function compose($view)
    {
        $dateFormats = DateFormatter::formats();
        $dateTimeFormat = $dateFormats[config('fi.dateFormat')]['datetimepicker'] . (!config('fi.use24HourTimeFormat') ? ' hh:mm A' : ' H:mm');

        $view->with('dateTimeFormat', $dateTimeFormat);
    }
}