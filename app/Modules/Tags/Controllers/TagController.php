<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Tags\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Tags\Models\Tag;

class TagController extends Controller
{

    public function delete()
    {
        try
        {
            Tag::whereTagEntity('client')->doesntHave('clientTags')->delete();

            Tag::whereTagEntity('note')->doesntHave('noteTags')->delete();
        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json(['success' => true, 'message' => trans('fi.orphan_tags_deleted')], 200);

    }
}