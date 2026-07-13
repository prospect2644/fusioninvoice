<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Traits;

use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Support\DateFormatter;
use Illuminate\Support\Facades\DB;

trait Sortable
{
    public function scopeSortable($query, $defaultSort = [])
    {
        if (request()->has('s') and request()->has('o') and isset($this->sortable) and $this->sortIsAllowed())
        {
            if (in_array(request('s'), $this->sortable) or in_array('custom', $this->sortable) and starts_with(request('s'), 'column_'))
            {
                return $query->orderBy(request('s'), request('o'));
            }
            elseif (array_key_exists(request('s'), $this->sortable))
            {
                foreach ($this->sortable[request('s')] as $col)
                {
                    if (str_contains($col, '('))
                    {
                        $query->orderBy(DB::raw($col), request('o'));
                    }
                    else
                    {
                        $query->orderBy($col, request('o'));
                    }
                }

                return $query;
            }
        }
        elseif ($defaultSort)
        {
            //Before goes with default, let's check for custom field label
            if (request('s'))
            {
                $field = $this->getCustomFieldDataTypeByLabel(request()->path(), request('s'));

                if ($field)
                {
                    $customFieldTablePrefix = $field->tbl_name . '_custom';
                    switch ($field->field_type)
                    {
                        case "date":
                            return $query->orderByRaw(DB::raw('STR_TO_DATE(' . $customFieldTablePrefix . '.' . $field->column_name . ', "' . DateFormatter::getMySqlDateFormat() . '") ' . request('o')));
                            break;
                        case "datetime":
                            return $query->orderByRaw(DB::raw('STR_TO_DATE(' . $customFieldTablePrefix . '.' . $field->column_name . ', "' . DateFormatter::getMySqlDateTimeFormat() . '") ' . request('o')));
                            break;
                        case "integer":
                        case "currency":
                        case "phone":
                        case "decimal":
                            return $query->orderByRaw(DB::raw('CAST(' . $customFieldTablePrefix . '.' . $field->column_name . ' as UNSIGNED) ' . request('o')));
                            break;
                        default:
                            return $query->orderBy($customFieldTablePrefix . '.' . $field->column_name, request('o'));
                            break;
                    }
                }
            }

            foreach ($defaultSort as $col => $sort)
            {
                if (str_contains($col, '('))
                {
                    $query->orderBy(DB::raw($col), $sort);
                }
                else
                {
                    $query->orderBy($col, $sort);
                }
            }

            return $query;
        }

        return $query;
    }

    public static function link($col, $title = null, $requestMatches = null)
    {
        if ($requestMatches and !request()->is($requestMatches))
        {
            return $title;
        }

        if (is_null($title))
        {
            $title = str_replace('_', ' ', $col);
            $title = ucfirst($title);
        }

        $indicator  = (request('s') == $col ? (request('o') === 'asc' ? '&uarr;' : '&darr;') : null);
        $parameters = array_merge(request()->all(), ['s' => $col, 'o' => (request('o') === 'asc' ? 'desc' : 'asc')]);

        return link_to_route(request()->route()->getName(), "$title $indicator", $parameters);
    }

    private function sortIsAllowed()
    {
        // Sortable must be an array.
        if (!is_array($this->sortable))
        {
            return false;
        }

        // If it's contained in sortable, it's allowed.
        if (array_key_exists(request('s'), $this->sortable) or in_array(request('s'), $this->sortable))
        {
            return true;
        }

        // If sortable contains "custom" and s=custom_*, it's allowed.
        if ((array_key_exists('custom', $this->sortable) or in_array('custom', $this->sortable)) and substr(request('s'), 0, 7) == 'column_')
        {
            return true;
        }

        return false;
    }

    private function getCustomFieldDataType($type, $columnName)
    {
        return CustomFieldsParser::getFieldByColumnName($type, $columnName);
    }

    private function getCustomFieldDataTypeByLabel($type, $fieldLabel)
    {
        return CustomFieldsParser::getFieldByFieldLabel($type, $fieldLabel);
    }
}