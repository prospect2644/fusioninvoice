<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\CustomFields\Models;

use FI\Support\DateFormatter;
use Illuminate\Database\Eloquent\Model;

class ClientCustom extends Model
{
    protected $table = 'clients_custom';

    protected $primaryKey = 'client_id';

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function image($field_name, $width = null, $height = null)
    {
        $path = custom_field_path('clients/' . $this->$field_name);

        if ($this->$field_name and file_exists($path))
        {
            $logo = base64_encode(file_get_contents($path));

            $style = '';

            if ($width and !$height)
            {
                $style = 'width: ' . $width . 'px;';
            }
            elseif ($width and $height)
            {
                $style = 'width: ' . $width . 'px; height: ' . $height . 'px;';
            }

            return '<p><img id="cp-logo" src="data:image/png;base64,' . $logo . '" style="' . $style . '"></p><p><a href="javascript:void(0)" data-field-name="' . $field_name . '" id="btn-delete-custom-img">' . trans('fi.remove_image') . '</a></p>';
        }

        return null;
    }

    public function imageView($field_name, $width = null, $height = null)
    {
        $path = custom_field_path('clients/' . $this->$field_name);

        if ($this->$field_name and file_exists($path))
        {
            $logo = base64_encode(file_get_contents($path));

            $style = '';

            if ($width and !$height)
            {
                $style = 'width: ' . $width . 'px;';
            }
            elseif ($width and $height)
            {
                $style = 'width: ' . $width . 'px; height: ' . $height . 'px;';
            }

            return '<p><img id="cp-logo" src="data:image/png;base64,' . $logo . '" style="' . $style . '"></p>';
        }

        return null;
    }

    public function getDatePickerFormat()
    {
        return DateFormatter::getDatepickerFormat();
    }

    public function getDateTimePickerFormat()
    {
        return DateFormatter::getDateTimePickerFormat();
    }
}
