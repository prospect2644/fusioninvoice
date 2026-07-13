<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\DocumentNumberSchemes\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\DocumentNumberSchemes\DocumentNumberSchemeOptions;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\DocumentNumberSchemes\Requests\DocumentNumberSchemeRequest;
use FI\Traits\ReturnUrl;

class DocumentNumberSchemeController extends Controller
{
    use ReturnUrl;

    private $documentNumberSchemeOptions;

    public function __construct(DocumentNumberSchemeOptions $documentNumberSchemeOptions)
    {
        $this->documentNumberSchemeOptions = $documentNumberSchemeOptions;
    }

    public function index()
    {
        $this->setReturnUrl();

        $documentNumberSchemes = DocumentNumberScheme::sortable(['name' => 'asc'])->paginate(config('fi.resultsPerPage'));

        return view('document_number_schemes.index')
            ->with('documentNumberSchemes', $documentNumberSchemes)
            ->with('resetNumberOptions', $this->documentNumberSchemeOptions->resetNumberOptions());
    }

    public function create()
    {
        return view('document_number_schemes.form')
            ->with('editMode', false)
            ->with('types', ['' => trans('fi.select_type'), 'invoice' => trans('fi.invoice_default'), 'quote' => trans('fi.quote_default'), 'credit_memo' => trans('fi.credit_memo_default')])
            ->with('resetNumberOptions', $this->documentNumberSchemeOptions->resetNumberOptions());
    }

    public function store(DocumentNumberSchemeRequest $request)
    {
        DocumentNumberScheme::create($request->all());

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_created'));
    }

    public function edit($id)
    {
        $documentNumberScheme = DocumentNumberScheme::find($id);

        return view('document_number_schemes.form')
            ->with('editMode', true)
            ->with('documentNumberScheme', $documentNumberScheme)
            ->with('types', ['' => trans('fi.select_type'), 'invoice' => trans('fi.invoice_default'), 'quote' => trans('fi.quote_default'), 'credit_memo' => trans('fi.credit_memo_default')])
            ->with('resetNumberOptions', $this->documentNumberSchemeOptions->resetNumberOptions());
    }

    public function update(DocumentNumberSchemeRequest $request, $id)
    {
        $documentNumberScheme = DocumentNumberScheme::find($id);

        $documentNumberScheme->fill($request->all());

        $documentNumberScheme->save();

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

    public function delete($id)
    {
        DocumentNumberScheme::destroy($id);

        return redirect()->route('documentNumberSchemes.index')
            ->with('alert', trans('fi.record_successfully_deleted'));
    }
}