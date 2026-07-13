<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\ClientCenter\Controllers;

use FI\Modules\Attachments\Models\Attachment;

class ClientCenterAttachmentController
{
    public function index()
    {
        $clientId = auth()->user()->client->id;

        app()->setLocale(auth()->user()->client->language);

        $attachments = Attachment::select('id', 'created_at', 'attachable_id', 'attachable_type', 'filename', 'url_key')
            ->with('attachable')->where('client_visibility', 1)
            ->whereHas('client', function ($query) use ($clientId)
            {
                $query->where('id', $clientId);
            })->get();

        $attachments = $attachments->merge(Attachment::select('id', 'created_at', 'attachable_id', 'attachable_type', 'filename', 'url_key')
            ->with('attachable')->where('client_visibility', 1)
            ->whereHas('quote', function ($query) use ($clientId)
            {
                $query->where('client_id', $clientId);
            })->get());

        $attachments = $attachments->merge(Attachment::select('id', 'created_at', 'attachable_id', 'attachable_type', 'filename', 'url_key')
            ->with('attachable')->where('client_visibility', 1)
            ->whereHas('invoice', function ($query) use ($clientId)
            {
                $query->where('client_id', $clientId);
            })->get());

        $attachments = $attachments->merge(Attachment::select('id', 'created_at', 'attachable_id', 'attachable_type', 'filename', 'url_key')
            ->with('attachable')->where('client_visibility', 2)
            ->whereHas('invoice', function ($query) use ($clientId)
            {
                $query->where('client_id', $clientId)->where('status', 'paid');
            })->get());

        $attachments = $attachments->merge(Attachment::select('id', 'created_at', 'attachable_id', 'attachable_type', 'filename', 'url_key')
            ->with('attachable')->where('client_visibility', 1)
            ->whereHas('expense', function ($query) use ($clientId)
            {
                $query->where('client_id', $clientId);
            })->get());

        return view('client_center.attachments.index')
            ->with('attachments', $attachments->sortByDesc('created_at'));
    }
}