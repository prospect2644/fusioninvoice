<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\ItemLookups\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\ItemLookups\Models\ItemCategory;
use FI\Modules\ItemLookups\Models\ItemLookup;
use FI\Modules\ItemLookups\Requests\ItemLookupRequest;
use FI\Modules\ItemLookups\Requests\ItemLookupUpdateRequest;
use FI\Modules\TaxRates\Models\TaxRate;

class ItemLookupController extends Controller
{
    public function index()
    {
        $itemLookups = ItemLookup::defaultQuery()
            ->keywords(request('search'))
            ->categoryId(request('category'))
            ->sortable(['name' => 'asc'])
            ->paginate(config('fi.resultsPerPage'));

        return view('item_lookups.index')
            ->with('itemLookups', $itemLookups)
            ->with('searchPlaceholder', trans('fi.search_items'))
            ->with('categories', ['' => trans('fi.all_categories')] + ItemCategory::getList());
    }

    public function create()
    {
        if (ItemLookup::all()->count() < 5000)
        {
            return view('item_lookups.form')
                ->with('editMode', false)
                ->with('itemCategory', ItemCategory::getDropDownList())
                ->with('taxRates', TaxRate::getList());
        }
        else
        {
            return redirect()->route('itemLookups.index')
                ->with('error', trans('fi.item-lookup-overload'));
        }

    }

    public function store(ItemLookupRequest $request)
    {
        if (ItemLookup::all()->count() < 5000)
        {
            ItemLookup::create($request->all());

            return redirect()->route('itemLookups.index')
                ->with('alertSuccess', trans('fi.record_successfully_created'));
        }
        else
        {
            return redirect()->route('itemLookups.index')
                ->with('error', trans('fi.item-lookup-overload'));
        }

    }

    public function edit($id)
    {
        $itemLookup = ItemLookup::find($id);

        if ($itemLookup->category_id)
        {
            $itemLookup->category_name = ItemCategory::find($itemLookup->category_id)->name;
        }

        return view('item_lookups.form')
            ->with('editMode', true)
            ->with('itemLookup', $itemLookup)
            ->with('itemCategory', ItemCategory::getDropDownList())
            ->with('taxRates', TaxRate::getList());
    }

    public function getDetail()
    {
        $itemLookup = ItemLookup::find(request('id'));

        return $itemLookup;
    }

    public function update(ItemLookupUpdateRequest $request, $id)
    {
        $itemLookup = ItemLookup::find($id);

        $itemLookup->fill($request->all());

        $itemLookup->save();

        return redirect()->route('itemLookups.index')
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

    public function delete($id)
    {
        ItemLookup::destroy($id);

        return redirect()->route('itemLookups.index')
            ->with('alert', trans('fi.record_successfully_deleted'));
    }

}
