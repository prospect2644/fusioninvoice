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

trait ApiCustomFieldValidator
{

    public function withValidator($validator)
    {

        $validator->after(function ($validator) {

            $input = $this->except('invoice_id');

            foreach ($input as $key => $item) {

                $field = CustomFieldsParser::getFieldByColumnName($this->customFieldType, $key);

                if ($field) {
                    switch ($field->field_type) {
                        case "image":
                            $validator->errors()->add('custom.' . $key, trans('fi.custom-image-not-support', ['label' => $field->field_label]));
                            break;
                        case "dropdown":
                        case "radio":
                            $meta = json_decode($field->field_meta, true);
                            $options = isset($meta['options']) ? $meta['options'] : [];
                            if (!array_key_exists($item, $options)) {
                                $validator->errors()->add('custom.' . $key, trans('fi.custom_dropdown_validate', ['label' => $field->field_label]));
                            }
                            break;
                        case "date":
                            $validator->errors()->add('custom.' . $key, trans('fi.custom-date-not-support', ['label' => $field->field_label]));
                            break;
                        case "datetime":
                            $validator->errors()->add('custom.' . $key, trans('fi.custom-datetime-not-support', ['label' => $field->field_label]));
                            break;
                        case "integer":
                            if (!empty($item) && filter_var($item, FILTER_VALIDATE_INT) == false) {
                                $validator->errors()->add('custom.' . $key, trans('fi.custom_integer_validate', ['label' => $field->field_label]));
                            }
                            break;
                        case "currency":
                        case "phone":
                            if (!empty($item) && !is_numeric($item)) {
                                $validator->errors()->add('custom.' . $key, trans('fi.custom_text_validate', ['label' => $field->field_label]));
                            }
                            break;
                        case "url":
                            if (!empty($item)) {
                                $scheme = parse_url($item, PHP_URL_SCHEME);
                                if (empty($scheme)) {
                                    $item = 'http://' . ltrim($item, '/');
                                    $input['custom'][$key] = $item;
                                    $this->request->replace($input);
                                }
                                if (!preg_match('/^(http|https):\\/\\/[a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.[a-z]{2,5}' . '((:[0-9]{1,5})?\\/.*)?$/i', $item)) {
                                    $validator->errors()->add('custom.' . $key, trans('fi.custom_text_validate', ['label' => $field->field_label]));
                                }
                            }
                            break;
                        case "email":
                            if (!empty($item) && filter_var($item, FILTER_VALIDATE_EMAIL) == false) {
                                $validator->errors()->add('custom.' . $key, trans('fi.custom_text_validate', ['label' => $field->field_label]));
                            }
                            break;
                        case "decimal":
                            if (!empty($item) && filter_var($item, FILTER_VALIDATE_FLOAT) == false) {
                                $validator->errors()->add('custom.' . $key, trans('fi.custom_text_validate', ['label' => $field->field_label]));
                            }
                            break;
                        case "tagselection":
                            $validator->errors()->add('custom.' . $key, trans('fi.custom-tag-not-support', ['label' => $field->field_label]));
                            break;
                        case "checkbox":
                            $validator->errors()->add('custom.' . $key, trans('fi.custom-checkbox-not-support', ['label' => $field->field_label]));
                            break;
                        default:
                    }
                }
            }

        });
    }
}