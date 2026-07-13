<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\CustomFields\Support;

use Carbon\Carbon;
use FI\Modules\CustomFields\Models\CustomField;
use Form;

class CustomFieldsParser
{
    /**
     * Provide an array of custom fields with it's attribute
     *
     * @param $type
     * @return array
     */
    public static function getFields($type)
    {
        $customField = [];
        $fields      = CustomField::forTable($type)->orderBy('display_order')->get();

        foreach ($fields as $key => $field)
        {
            $type                             = $field->field_type;
            $customField[$key]['field_type']  = $type;
            $customField[$key]['field_label'] = $field->field_label;
            $customField[$key]['tbl_name']    = $field->tbl_name;
            $customField[$key]['column_name'] = $field->column_name;
            $meta                             = json_decode($field->field_meta, true);

            switch ($type)
            {
                case "dropdown":
                case "radio":
                case "tagselection":
                    $customField[$key]['options'] = isset($meta['options']) ? $meta['options'] : [];
                    $customField[$key]['default'] = isset($meta['default']) ? $meta['default'] : null;
                    break;
                case "textarea":
                    $customField[$key]['rows'] = isset($meta['rows']) ? $meta['rows'] : null;
                    $customField[$key]['cols'] = isset($meta['cols']) ? $meta['cols'] : null;
                    break;
                case "currency":
                    $customField[$key]['symbol']  = isset($meta['symbol']) ? $meta['symbol'] : null;
                    $customField[$key]['default'] = isset($meta['default']) ? $meta['default'] : null;
                    break;
                default:
                    $customField[$key]['field_type'] = $type;
                    $customField[$key]['default']    = isset($meta['default']) ? $meta['default'] : null;
            }
        }

        return json_decode(json_encode($customField), false);
    }

    /**
     * Get the field by it's column name
     *
     * @param string $type
     * @param string $columnName
     * @return CustomField
     */
    public static function getFieldByColumnName($type, $columnName)
    {
        $field = CustomField::forTable($type)->forColumnName($columnName)->first();

        return $field;
    }

    /**
     * Get the field by it's type
     *
     * @param string $type
     * @param string $fieldType
     * @return CustomField
     */
    public static function getFieldByType($type, $fieldType)
    {
        $field = CustomField::forTable($type)->forFieldType($fieldType)->first();

        return $field;
    }

    /**
     * @param $object
     * @param $customField
     * @param $rawHtml
     * @return string
     */
    public static function getFieldValue($object, $customField, $rawHtml)
    {
        if ($object && $customField)
        {
            $field_type  = $customField->field_type;
            $field_value = $object->{$customField->column_name};
            $meta        = json_decode($customField->field_meta, true);
            $html        = '';
            if ($rawHtml == false)
            {
                return $field_value;
            }
            else
            {
                switch ($field_type)
                {
                    case "date":
                        $html .= (($field_value) ? Carbon::createFromFormat('Y-m-d', $field_value)->format(config('fi.dateFormat')) : "");
                        break;
                    case "datetime":
                        $html .= (($field_value) ? Carbon::createFromFormat('Y-m-d H:i:s', $field_value)->format(config('fi.dateFormat') . (!config('fi.use24HourTimeFormat') ? ' g:i A' : ' H:i')) : "");
                        break;
                    case "email":
                        $html .= '<a href="mailto:' . $field_value . '">' . $field_value . '</a>';
                        break;
                    case "url":
                        $html .= '<a href="' . $field_value . '" target="_blank">' . $field_value . '</a>';
                        break;
                    case "checkbox":
                        $html .= Form::checkbox('checkbox', 1, $field_value == 1 ? true : false, ['class' => 'custom-form-field', 'disabled']);
                        break;
                    case "radio":
                    case "dropdown":
                        $html .= $field_value != '' && isset($meta['options'][$field_value]) ? $meta['options'][$field_value] : '';
                        break;
                    case "tagselection":
                        $tags = json_decode($field_value);
                        if (!empty($tags) && count($tags) > 0)
                        {
                            foreach ($tags as $tag)
                            {
                                $html .= '<span class="label label-default" style="font-size: 85%;">' . $meta['options'][$tag] . '</span> ';
                            }
                        }
                        break;
                    case "image":
                        if ($field_value)
                        {
                            $html .= '<div class="custom_img">' . $object->imageView($customField->column_name, 100) . '</div>';
                        }
                        break;
                    default:
                        $html .= nl2br($field_value);
                        break;

                }
                return $html;
            }
        }
    }

    /**
     * Get the field by it's field label
     *
     * @param string $type
     * @param string $fieldLabel
     * @return CustomField
     */
    public static function getFieldByFieldLabel($type, $fieldLabel)
    {
        return CustomField::forTable($type)->forFieldLabel($fieldLabel)->first();
    }

}
