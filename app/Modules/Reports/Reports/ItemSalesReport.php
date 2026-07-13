<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Reports\Reports;

use FI\Modules\Invoices\Models\InvoiceItem;
use FI\Modules\ItemLookups\Models\ItemCategory;
use FI\Modules\ItemLookups\Models\ItemLookup;
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Support\NumberFormatter;

class ItemSalesReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null, $categoryId = null)
    {
        $results  = [
            'from_date'   => DateFormatter::format($fromDate),
            'to_date'     => DateFormatter::format($toDate),
            'grand_total' => 0,
            'records'     => [],
        ];
        $itemName = [];
        if ($categoryId)
        {
            $itemName = ItemLookup::whereCategoryId($categoryId)->get()->pluck('name')->toArray();
        }

        $items = InvoiceItem::byDateRange($fromDate, $toDate)
            ->select('invoice_items.name AS item_name', 'invoice_items.quantity AS item_quantity',
                'invoice_items.price AS item_price', 'clients.name AS client_name', 'invoices.number AS invoice_number',
                'invoices.invoice_date AS invoice_date', 'invoices.exchange_rate AS invoice_exchange_rate',
                'invoice_item_amounts.subtotal', 'invoice_item_amounts.tax', 'invoice_item_amounts.total',
                'invoice_amounts.discount')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('invoice_item_amounts', 'invoice_item_amounts.item_id', '=', 'invoice_items.id')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->join('invoice_amounts', 'invoice_items.invoice_id', '=', 'invoice_amounts.invoice_id')
            ->where('invoices.status', '<>', 'canceled')
            ->where(function ($q) use ($itemName)
            {
                if (!empty($itemName))
                {
                    $q->whereIn('invoice_items.name', $itemName);
                }
            })
            ->orderBy('invoice_items.name');

        if ($companyProfileId)
        {
            $items->where('invoices.company_profile_id', $companyProfileId);
        }

        $items = $items->get();

        foreach ($items as $item)
        {
            $results['records'][$item->item_name]['items'][] = [
                'client_name'    => $item->client_name,
                'invoice_number' => $item->invoice_number,
                'date'           => DateFormatter::format($item->invoice_date),
                'price'          => CurrencyFormatter::format($item->item_price / $item->invoice_exchange_rate),
                'quantity'       => NumberFormatter::format($item->item_quantity),
                'subtotal'       => CurrencyFormatter::format($item->subtotal / $item->invoice_exchange_rate),
                'discount'       => CurrencyFormatter::format($item->discount),
                'tax'            => CurrencyFormatter::format($item->tax / $item->invoice_exchange_rate),
                'total'          => CurrencyFormatter::format($item->total / $item->invoice_exchange_rate),
            ];

            if (isset($results['records'][$item->item_name]['totals']))
            {
                $results['records'][$item->item_name]['totals']['quantity'] += $item->quantity;
                $results['records'][$item->item_name]['totals']['subtotal'] += round($item->subtotal / $item->invoice_exchange_rate, 2);
                $results['records'][$item->item_name]['totals']['discount'] += round($item->discount, 2);
                $results['records'][$item->item_name]['totals']['tax'] += round($item->tax / $item->invoice_exchange_rate, 2);
                $results['records'][$item->item_name]['totals']['total'] += round($item->total / $item->invoice_exchange_rate, 2);
            }
            else
            {
                $results['records'][$item->item_name]['totals']['quantity'] = $item->quantity;
                $results['records'][$item->item_name]['totals']['subtotal'] = round($item->subtotal / $item->invoice_exchange_rate, 2);
                $results['records'][$item->item_name]['totals']['discount'] = round($item->discount, 2);
                $results['records'][$item->item_name]['totals']['tax']      = round($item->tax / $item->invoice_exchange_rate, 2);
                $results['records'][$item->item_name]['totals']['total']    = round($item->total / $item->invoice_exchange_rate, 2);
            }
        }
        foreach ($results['records'] as $key => $result)
        {
            $results['grand_total']                         = $results['grand_total'] + $results['records'][$key]['totals']['total'];
            $results['records'][$key]['totals']['quantity'] = NumberFormatter::format($results['records'][$key]['totals']['quantity']);
            $results['records'][$key]['totals']['subtotal'] = CurrencyFormatter::format($results['records'][$key]['totals']['subtotal']);
            $results['records'][$key]['totals']['discount'] = CurrencyFormatter::format($results['records'][$key]['totals']['discount']);
            $results['records'][$key]['totals']['tax']      = CurrencyFormatter::format($results['records'][$key]['totals']['tax']);
            $results['records'][$key]['totals']['total']    = CurrencyFormatter::format($results['records'][$key]['totals']['total']);
        }

        $results['grand_total'] = CurrencyFormatter::format($results['grand_total']);

        return $results;
    }
}