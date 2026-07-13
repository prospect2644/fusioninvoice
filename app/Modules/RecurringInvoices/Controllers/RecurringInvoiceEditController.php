<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\RecurringInvoices\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Currencies\Models\Currency;
use FI\Modules\CustomFields\Models\RecurringInvoiceCustom;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\CustomFields\Support\CustomFieldsTransformer;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\Invoices\Support\InvoiceTemplates;
use FI\Modules\ItemLookups\Models\ItemLookup;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\Tags\Models\Tag;
use FI\Modules\RecurringInvoices\Events\AddTransition;
use FI\Modules\RecurringInvoices\Models\RecurringInvoice;
use FI\Modules\RecurringInvoices\Models\RecurringInvoiceItem;
use FI\Modules\RecurringInvoices\Requests\RecurringInvoiceUpdateRequest;
use FI\Modules\TaxRates\Models\TaxRate;
use FI\Support\DateFormatter;
use FI\Support\Frequency;
use FI\Traits\ReturnUrl;
use Illuminate\Support\Facades\Storage;

class RecurringInvoiceEditController extends Controller
{
    use ReturnUrl;

    public function edit($id)
    {
        $recurringInvoice = RecurringInvoice::with(['items.amount.item.recurringInvoice.currency'])->find($id);

        event(new MruLog(['module' => 'recurring_invoices', 'action' => 'edit', 'id' => $id, 'title' => $recurringInvoice->id . ' ' . $recurringInvoice->client->name]));
        $selectedTags = [];

        foreach ($recurringInvoice->tags as $tagDetail)
        {
            $selectedTags[] = $tagDetail->tag->name;
        }
        $customFields = CustomFieldsParser::getFields('recurring_invoices');

        return view('recurring_invoices.edit')
            ->with('recurringInvoice', $recurringInvoice)
            ->with('currencies', Currency::getList())
            ->with('taxRates', TaxRate::getList())
            ->with('customFields', $customFields)
            ->with('returnUrl', $this->getReturnUrl())
            ->with('templates', InvoiceTemplates::lists())
            ->with('itemCount', count($recurringInvoice->recurringInvoiceItems))
            ->with('frequencies', Frequency::lists())
            ->with('documentNumberSchemes', DocumentNumberScheme::getList())
            ->with('tags', Tag::whereTagEntity('sales')->pluck('name', 'name'))
            ->with('selectedTags', $selectedTags);
    }

    public function update(RecurringInvoiceUpdateRequest $request, $id)
    {
        $input              = $request->except(['items', 'custom', 'apply_exchange_rate', 'tags']);
        $input['next_date'] = DateFormatter::unformat($input['next_date']);
        $input['stop_date'] = DateFormatter::unformat($input['stop_date']);

        // Save the recurring invoice.
        $recurringInvoice = RecurringInvoice::find($id);
        $recurringInvoice->fill($input);
        $recurringInvoice->save();

        event(new AddTransition($recurringInvoice, 'updated'));

        $tags    = request('tags', []);
        $tag_ids = [];

        $recurringInvoice->deleteTags($recurringInvoice);

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
                $recurringInvoice->tags()->create(['recurring_invoice_id' => $recurringInvoice->id, 'tag_id' => $tag_id]);
            }
        }
        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'recurring_invoices', $recurringInvoice);
        $recurringInvoice->custom->update($customFieldData);

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

                RecurringInvoiceItem::create($item);

                if ($saveItemAsLookup)
                {
                    if (ItemLookup::all()->count() < 5000)
                    {
                        ItemLookup::firstOrCreate(['name' => $item['name']], [
                            'name'          => $item['name'],
                            'description'   => $item['description'],
                            'price'         => $item['price'],
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
                $recurringInvoiceItem = RecurringInvoiceItem::find($item['id']);
                $recurringInvoiceItem->fill($item);
                $recurringInvoiceItem->save();
            }
        }

        return response()->json($response);
    }

    public function refreshEdit($id)
    {
        $recurringInvoice = RecurringInvoice::with(['items.amount.item.recurringInvoice.currency'])->find($id);
        $customFields     = CustomFieldsParser::getFields('recurring_invoices');
        $selectedTags     = [];

        foreach ($recurringInvoice->tags as $tagDetail)
        {
            $selectedTags[] = $tagDetail->tag->name;
        }
        return view('recurring_invoices._edit')
            ->with('recurringInvoice', $recurringInvoice)
            ->with('currencies', Currency::getList())
            ->with('taxRates', TaxRate::getList())
            ->with('customFields', $customFields)
            ->with('returnUrl', $this->getReturnUrl())
            ->with('templates', InvoiceTemplates::lists())
            ->with('itemCount', count($recurringInvoice->recurringInvoiceItems))
            ->with('frequencies', Frequency::lists())
            ->with('documentNumberSchemes', DocumentNumberScheme::getList())
            ->with('tags', Tag::whereTagEntity('sales')->pluck('name', 'name'))
            ->with('selectedTags', $selectedTags);
    }

    public function refreshTotals()
    {
        return view('recurring_invoices._edit_totals')
            ->with('recurringInvoice', RecurringInvoice::with(['items.amount.item.recurringInvoice.currency'])->find(request('id')));
    }

    public function refreshTo()
    {
        return view('recurring_invoices._edit_to')
            ->with('recurringInvoice', RecurringInvoice::find(request('id')));
    }

    public function refreshFrom()
    {
        return view('recurring_invoices._edit_from')
            ->with('recurringInvoice', RecurringInvoice::find(request('id')));
    }

    public function updateClient()
    {
        RecurringInvoice::where('id', request('id'))->update(['client_id' => request('client_id')]);
    }

    public function updateCompanyProfile()
    {
        RecurringInvoice::where('id', request('id'))->update(['company_profile_id' => request('company_profile_id')]);
    }

    public function deleteImage($id, $columnName)
    {
        $customFields = RecurringInvoiceCustom::whereRecurringInvoiceId($id)->first();

        $existingFile = 'recurring_invoices' . DIRECTORY_SEPARATOR . $customFields->{$columnName};
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