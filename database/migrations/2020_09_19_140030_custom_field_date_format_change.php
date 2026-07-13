<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class CustomFieldDateFormatChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dateFormat     = config('fi.dateFormat');
        $dateTimeFormat = config('fi.dateFormat') . (!config('fi.use24HourTimeFormat') ? ' g:i A' : ' H:i');
        $data           = DB::table('custom_fields')->whereIn('field_type', ['date', 'datetime'])->get();
        foreach ($data as $row)
        {
            $fieldType       = $row->field_type;
            $tableName       = ($row->tbl_name . '_custom');
            $columnName      = $row->column_name;
            $primaryKeyField = $this->mapPrimaryKey($tableName);

            $existingTimestampRecords = DB::table($tableName)
                ->whereNotNull($columnName)
                ->where($columnName, '!=', '')
                ->get();
            foreach ($existingTimestampRecords as $existingTimestampRecord)
            {
                $currentFieldValue    = $existingTimestampRecord->{$columnName};
                $primaryKeyFieldValue = $existingTimestampRecord->{$primaryKeyField};
                if ($fieldType == 'date')
                {
                    try
                    {
                        if (Carbon::createFromFormat($dateFormat, $currentFieldValue) !== false)
                        {
                            $updatedFieldValue = Carbon::createFromFormat($dateFormat, $currentFieldValue)->format('Y-m-d');
                        }
                        else
                        {
                            $updatedFieldValue = null;
                        }
                    }
                    catch (\Exception $exception)
                    {
                        $updatedFieldValue = null;
                    }
                }
                elseif ($fieldType == 'datetime')
                {
                    try
                    {
                        if (Carbon::createFromFormat($dateTimeFormat, $currentFieldValue) !== false)
                        {
                            $updatedFieldValue = Carbon::createFromFormat($dateTimeFormat, $currentFieldValue)->format('Y-m-d H:i:s');
                        }
                        else
                        {
                            $updatedFieldValue = null;
                        }
                    }
                    catch (\Exception $exception)
                    {
                        $updatedFieldValue = null;
                    }
                }
                DB::table($tableName)->where($primaryKeyField, $primaryKeyFieldValue)->update([$columnName => $updatedFieldValue]);
            }
        }
    }

    public function mapPrimaryKey($tableName)
    {
        switch ($tableName)
        {
            case 'clients_custom':
                $primaryKeyField = 'client_id';
                break;
            case 'company_profiles_custom':
                $primaryKeyField = 'company_profile_id';
                break;
            case 'expenses_custom':
                $primaryKeyField = 'expense_id';
                break;
            case 'invoices_custom':
                $primaryKeyField = 'invoice_id';
                break;
            case 'payments_custom':
                $primaryKeyField = 'payment_id';
                break;
            case 'quotes_custom':
                $primaryKeyField = 'quote_id';
                break;
            case 'recurring_invoices_custom':
                $primaryKeyField = 'recurring_invoice_id';
                break;
            case 'users_custom':
                $primaryKeyField = 'user_id';
                break;
            default:
                $primaryKeyField = '';
        }
        return $primaryKeyField;

    }
}
