<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Import\Importers;

use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\PaymentMethods\Models\PaymentMethod;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Payments\Models\PaymentInvoice;
use Illuminate\Support\Facades\Validator;

class PaymentImporter extends AbstractImporter
{
    public function getFields()
    {
        return [
            'paid_at'           => '* ' . trans('fi.date'),
            'invoice_id'        => '* ' . trans('fi.invoice_number'),
            'amount'            => '* ' . trans('fi.amount'),
            'payment_method_id' => '* ' . trans('fi.payment_method'),
            'note'              => trans('fi.note'),
        ];
    }

    public function getMapRules()
    {
        return [
            'paid_at'           => 'required',
            'invoice_id'        => 'required',
            'amount'            => 'required',
            'payment_method_id' => 'required',
        ];
    }

    public function getValidator($input)
    {
        return Validator::make($input, [
            'paid_at'           => 'required',
            'invoice_id'        => 'required',
            'amount'            => 'required|numeric',
            'payment_method_id' => 'required',
        ]);
    }

    public function importData($input)
    {
        $row = 1;

        $fields = [];
        $userId = auth()->user()->id;

        // Assume payment has a one to one relation to the invoice and that the payment was completely used up going toward the invoice. 
        $type              = 'single';
        $remaining_balance = 0.00;

        foreach ($input as $field => $key)
        {
            if (is_numeric($key))
            {
                $fields[$key] = $field;
            }
        }

        $handle = fopen(storage_path('payments.csv'), 'r');

        if (!$handle)
        {
            $this->messages->add('error', 'Could not open the file');

            return false;
        }

        // Completed: user_id (auth user), client_id, type='single', remaining_balance
        // Need to populate payment_invoices table: payment_id, invoice_id, invoice_amount_paid

        while (($data = fgetcsv($handle, 1000, ',')) !== false)
        {
            if ($row !== 1)
            {
                $record    = [];
                $pi_record = [];

                foreach ($fields as $key => $field)
                {
                    $record[$field] = trim($data[$key]);
                }

                // Attempt to format the date, otherwise use today
                if (strtotime($record['paid_at']))
                {
                    $record['paid_at'] = date('Y-m-d', strtotime($record['paid_at']));
                }
                else
                {
                    $record['paid_at'] = date('Y-m-d');
                }

                // Transform the invoice number to the id
                $record['invoice_id'] = Invoice::where('number', $record['invoice_id'])->first()->id;

                // Fetch the client_id from the invoice
                $record['client_id'] = Invoice::where('id', $record['invoice_id'])->first()->client_id;

                // Transform the payment method to the id
                if ($record['payment_method_id'] <> 'NULL')
                {
                    $record['payment_method_id'] = PaymentMethod::firstOrCreate(['name' => $record['payment_method_id']])->id;
                }
                else
                {
                    $record['payment_method_id'] = PaymentMethod::firstOrCreate(['name' => 'Other'])->id;
                }

                if (!isset($record['note']))
                {
                    $record['note'] = '';
                }

                // Assign the invoice to the current logged in user
                $record['user_id']           = $userId;
                $record['type']              = $type;
                $record['remaining_balance'] = $remaining_balance;

                if ($this->validateRecord($record))
                {
                    $payment = Payment::create($record);

                    if ($payment)
                    {
                        // Create the payment_invoice record info
                        $pi_record['payment_id']          = $payment->id;
                        $pi_record['invoice_id']          = $record['invoice_id'];
                        $pi_record['invoice_amount_paid'] = $record['amount'];
                        PaymentInvoice::create($pi_record);
                    }
                }
            }
            $row++;
        }

        fclose($handle);

        return true;
    }
}