<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Attachments\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Attachments\Events\AddTransition;
use FI\Modules\Attachments\Events\CheckAttachment;
use FI\Modules\Attachments\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttachmentController extends Controller
{
    public function download($urlKey)
    {
        $attachment = Attachment::where('url_key', $urlKey)->firstOrFail();

        $headers = [
            'Content-Type'        => $attachment->mimetype,
            'Content-Length'      => $attachment->size,
            'Content-Disposition' => 'attachment; filename=' . $attachment->filename,
        ];

        return response($attachment->content, 200, $headers);
    }

    public function ajaxList()
    {
        $model = request('model');

        $object = $model::find(request('model_id'));

        return view('attachments._table')
            ->with('model', request('model'))
            ->with('object', $object);
    }

    public function ajaxDelete()
    {
        $attachment = Attachment::find(request('attachment_id'));
        event(new AddTransition($attachment, 'deleted'));
        $attachment->delete();
    }

    public function ajaxModal()
    {
        return view('attachments._modal_attach_files')
            ->with('model', request('model'))
            ->with('modelId', request('model_id'));
    }

    public function ajaxUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attachments.*' => 'file|max:8000',
        ]);
        if ($validator->fails())
        {
            return response()->json(['success' => false, 'message' => trans('fi.attachment_error', ['size' => '8MB'])], 422);
        }
        $model = request('model');

        $object = $model::find(request('model_id'));

        event(new CheckAttachment($object));
    }

    public function ajaxAccessUpdate()
    {
        $attachment = Attachment::find(request('attachment_id'));

        $attachment->client_visibility = request('client_visibility');

        $attachment->save();
    }
}