<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Controllers;

use Carbon\Carbon;
use FI\Http\Controllers\Controller;
use FI\Modules\Currencies\Models\Currency;
use FI\Modules\CustomFields\Models\InvoiceCustom;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\CustomFields\Support\CustomFieldsTransformer;
use FI\Modules\Invoices\Events\AddTransition;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Invoices\Models\InvoiceItem;
use FI\Modules\Invoices\Requests\InvoiceUpdateRequest;
use FI\Modules\Invoices\Support\InvoiceTemplates;
use FI\Modules\ItemLookups\Models\ItemLookup;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Tags\Models\Tag;
use FI\Modules\TaxRates\Models\TaxRate;
use FI\Support\DateFormatter;
use FI\Support\Statuses\InvoiceStatuses;
use FI\Traits\ReturnUrl;
use Illuminate\Support\Facades\Storage;

class InvoiceEditController extends Controller
{
    use ReturnUrl;

    public function edit($id)
    {
        $invoice         = Invoice::with(['items.amount.item.invoice.currency'])->find($id);
        $creditMemoCount = Invoice::creditMemoListForClient($invoice->client_id);
        $prePayment      = Payment::prePaymentListForClient($invoice->client_id);
        $invoiceList     = Invoice::invoiceListForClient($invoice->client_id);

        event(new MruLog(['module' => 'invoices', 'action' => 'edit', 'id' => $id, 'title' => $invoice->number . ' ' . $invoice->client->name]));
        $selectedTags = [];

        foreach ($invoice->tags as $tagDetail)
        {
            $selectedTags[] = $tagDetail->tag->name;
        }
        return view('invoices.edit')
            ->with('invoice', $invoice)
            ->with('statuses', InvoiceStatuses::lists())
            ->with('currencies', Currency::getList())
            ->with('taxRates', TaxRate::getList())
            ->with('customFields', CustomFieldsParser::getFields('invoices'))
            ->with('returnUrl', $this->getReturnUrl())
            ->with('templates', InvoiceTemplates::lists())
            ->with('creditMemoCount', count($creditMemoCount))
            ->with('prePaymentCount', count($prePayment))
            ->with('invoiceCount', count($invoiceList))
            ->with('itemCount', count($invoice->invoiceItems))
            ->with('tags', Tag::whereTagEntity('sales')->pluck('name', 'name'))
            ->with('selectedTags', $selectedTags);
    }

    public function update(InvoiceUpdateRequest $request, $id)
    {
        // Unformat the invoice dates.
        $invoiceInput                 = $request->except(['items', 'custom', 'apply_exchange_rate', 'tags']);
        $invoiceInput['invoice_date'] = DateFormatter::unformat($invoiceInput['invoice_date']);
        $invoiceInput['due_at']       = DateFormatter::unformat($invoiceInput['due_at']);

        // Save the invoice.
        $invoice = Invoice::find($id);
        $invoice->fill($invoiceInput);
        $updatedFields = $invoice->getDirty();
        if (isset($updatedFields['status']))
        {
            event(new AddTransition($invoice, 'status_changed', $invoice->getOriginal('status'), $invoice->status));
        }
        $invoice->save();

        $tags    = request('tags', []);
        $tag_ids = [];

        $invoice->deleteTags($invoice);

        if (is_array($tags))
        {
            foreach ($tags as $tag)
            {
                $tag = Tag::firstOrNew(['name' => $tag], ['tag_entity' => 'sales'])->fill(['name' => $tag, 'tag_entity' => 'sales']);

                $tag->save();

                $tag_ids[] = $tag->id;
            }
            foreach ($tag_ids as $tag_id)
            {
                $invoice->tags()->insert(['invoice_id' => $invoice->id, 'tag_id' => $tag_id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            }
        }
        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'invoices', $invoice);
        $invoice->custom->update($customFieldData);

        $response = '';

        // Save the items.
        foreach ($request->input('items') as $item)
        {
            $item['apply_exchange_rate'] = request('apply_exchange_rate');

            if ($item['name'] == '' and $item['price'] == '')
            {
                continue;
            }

            if (!isset($item['id']) or (!$item['id']))
            {
                $saveItemAsLookup = $item['save_item_as_lookup'];
                unset($item['save_item_as_lookup']);
                $item['price'] = ($invoice->type == 'credit_memo') ? (-1 * abs($item['price'])) : abs($item['price']);
                InvoiceItem::create($item);

                if ($saveItemAsLookup)
                {
                    if (ItemLookup::all()->count() < 5000)
                    {
                        ItemLookup::updateOrCreate(['name' => $item['name']], [
                            'name'          => $item['name'],
                            'description'   => $item['description'],
                            'price'         => abs($item['price']),
                            'tax_rate_id'   => $item['tax_rate_id'],
                            'tax_rate_2_id' => $item['tax_rate_2_id'],
                        ]);
                    }
                    else
                    {
                        $response = ['error' => trans('fi.item-lookup-overload')];
                    }
                }
            }
            else
            {
                $invoiceItem = InvoiceItem::find($item['id']);
                $invoiceItem->fill($item);
                $invoiceItem->save();

                $saveItemAsLookup = $item['save_item_as_lookup'];
                $item['price']    = ($invoice->type == 'credit_memo') ? (-1 * abs($item['price'])) : abs($item['price']);
                if ($saveItemAsLookup)
                {
                    if (ItemLookup::all()->count() < 5000)
                    {
                        ItemLookup::updateOrCreate(['name' => $item['name']], [
                            'name'          => $item['name'],
                            'description'   => $item['description'],
                            'price'         => abs($item['price']),
                            'tax_rate_id'   => $item['tax_rate_id'],
                            'tax_rate_2_id' => $item['tax_rate_2_id'],
                        ]);
                    }
                    else
                    {
                        $response = ['error' => trans('fi.item-lookup-overload')];
                    }
                }
            }
        }

        if (!isset($updatedFields['status']))
        {
            if ($invoice->type == 'credit_memo')
            {
                event(new AddTransition($invoice, 'credit_memo_updated'));
            }
            else
            {
                event(new AddTransition($invoice, 'updated'));
            }
        }
        return response()->json($response);
    }

    public function refreshEdit($id)
    {
        $invoice         = Invoice::with(['items.amount.item.invoice.currency'])->find($id);
        $creditMemoCount = Invoice::creditMemoListForClient($invoice->client_id);
        $prePayment      = Invoice::creditMemoListForClient($invoice->client_id);
        $invoiceList     = Invoice::invoiceListForClient($invoice->client_id);
        $selectedTags    = [];

        foreach ($invoice->tags as $tagDetail)
        {
            $selectedTags[] = $tagDetail->tag->name;
        }
        return view('invoices._edit')
            ->with('invoice', $invoice)
            ->with('statuses', InvoiceStatuses::lists())
            ->with('currencies', Currency::getList())
            ->with('taxRates', TaxRate::getList())
            ->with('customFields', CustomFieldsParser::getFields('invoices'))
            ->with('returnUrl', $this->getReturnUrl())
            ->with('templates', InvoiceTemplates::lists())
            ->with('creditMemoCount', count($creditMemoCount))
            ->with('prePaymentCount', count($prePayment))
            ->with('invoiceCount', count($invoiceList))
            ->with('itemCount', count($invoice->invoiceItems))
            ->with('tags', Tag::whereTagEntity('sales')->pluck('name', 'name'))
            ->with('selectedTags', $selectedTags);
    }

    public function refreshTotals()
    {
        return view('invoices._edit_totals')
            ->with('invoice', Invoice::with(['items.amount.item.invoice.currency'])->find(request('id')));
    }

    public function refreshTo()
    {
        return view('invoices._edit_to')
            ->with('invoice', Invoice::find(request('id')));
    }

    public function refreshFrom()
    {
        return view('invoices._edit_from')
            ->with('invoice', Invoice::find(request('id')));
    }

    public function updateClient()
    {
        Invoice::where('id', request('id'))->update(['client_id' => request('client_id')]);
    }

    public function updateCompanyProfile()
    {
        Invoice::where('id', request('id'))->update(['company_profile_id' => request('company_profile_id')]);
    }

    public function deleteImage($id, $columnName)
    {
        $customFields = InvoiceCustom::whereInvoiceId($id)->first();

        $existingFile = 'invoices' . DIRECTORY_SEPARATOR . $customFields->{$columnName};
        if (Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->exists($existingFile))
        {
            try
            {
                Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->delete($existingFile);
                $customFields->{$columnName} = null;
                $customFields->save();
            }
            catch (Exception $e)
            {

            }
        }
    }
}
