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

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Class CustomFieldsTransformer
 * @package FI\Modules\CustomFields\Support
 */
class CustomFieldsTransformer
{
    const STORAGE_DISK_NAME = 'custom_field_upload';

    /**
     * @param $fieldData
     * @param $type
     * @param $object
     * @return mixed
     */
    public static function transform($fieldData, $type, $object)
    {
        foreach ($fieldData as $key => $item)
        {
            $field = CustomFieldsParser::getFieldByColumnName($type, $key);

            if ('image' === $field->field_type)
            {
                if ($item instanceof UploadedFile)
                {
                    $fileNameParts = explode('.', $item->getClientOriginalName());

                    array_pop($fileNameParts);

                    $fileNamePartsSansExtension = $fileNameParts;
                    $fileNameSansExtension      = implode('', $fileNamePartsSansExtension);
                    $timestamp                  = Carbon::now()->getTimestamp();
                    $fileName                   = $fileNameSansExtension . '_' . $timestamp . '.' . $item->getClientOriginalExtension();

                    $item->storeAs($type, $fileName, self::STORAGE_DISK_NAME);

                    $existingFile = $type . DIRECTORY_SEPARATOR . $object->custom->{$key};

                    if (Storage::disk(self::STORAGE_DISK_NAME)->exists($existingFile))
                    {
                        Storage::disk(self::STORAGE_DISK_NAME)->delete($existingFile);
                    }

                    $fieldData[$key] = $fileName;
                }
                else
                {
                    unset($fieldData[$key]);
                }

                continue;
            }

            if ('url' === $field->field_type && $item != '')
            {
                $scheme = parse_url($item, PHP_URL_SCHEME);
                if (empty($scheme))
                {
                    $fieldData[$field->column_name] = 'http://' . ltrim($item, '/');
                }
                continue;
            }

            if ('date' === $field->field_type)
            {
                $fieldData[$key] = (!empty($item)) ? Carbon::createFromFormat(config('fi.dateFormat'), $item)->format('Y-m-d') : null;
            }
            if ('datetime' === $field->field_type)
            {
                $fieldData[$key] = (!empty($item)) ? Carbon::createFromFormat((config('fi.dateFormat') . (!config('fi.use24HourTimeFormat') ? ' g:i A' : ' H:i')), $item)->format('Y-m-d H:i:s') : null;
            }
            if (!is_array($item))
            {
                continue;
            }

            $fieldData[$key] = json_encode($item);
        }

        // Let's check if user have checkbox field and it's unchecked
        $field = CustomFieldsParser::getFieldByType($type, 'checkbox');

        if ($field)
        {
            if (!isset($fieldData[$field->column_name]))
            {
                $fieldData[$field->column_name] = '';
            }
        }

        // Let's check if user has unselect all tags
        $field = CustomFieldsParser::getFieldByType($type, 'tagselection');

        if ($field)
        {
            if (!isset($fieldData[$field->column_name]))
            {
                $fieldData[$field->column_name] = '';
            }
        }

        return $fieldData;
    }
}