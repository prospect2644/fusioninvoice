<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Expenses\Models;

use FI\Modules\Attachments\Events\CheckAttachment;
use FI\Modules\Clients\Models\Client;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\CustomFields\Models\ExpenseCustom;
use FI\Modules\Expenses\Events\AddTransition;
use FI\Modules\Mru\Models\Mru;

class ExpenseObserver
{
    public function created(Expense $expense)
    {
        $expense->custom()->save(new ExpenseCustom());
        if (!empty($expense->client_id))
        {
            event(new AddTransition($expense, 'created'));
        }
    }

    public function deleting(Expense $expense)
    {
        foreach ($expense->attachments as $attachment)
        {
            $attachment->delete();
        }

        if ($expense->custom)
        {
            $expense->custom->delete();
        }
        Mru::whereUserId(auth()->user()->id)->whereModule('expenses')->whereElementId($expense->id)->delete();
    }

    public function saved(Expense $expense)
    {
        event(new CheckAttachment($expense));
    }

    public function updated(Expense $expense)
    {
        if (!empty($expense->client_id))
        {
            event(new AddTransition($expense, 'updated'));
        }
    }

    public function saving(Expense $expense)
    {
        if (!$expense->id)
        {
            $expense->user_id = auth()->user()->id;
        }

        if ($expense->category_name && $expense->category_id == null)
        {
            $expense->category_id = ExpenseCategory::firstOrCreate(['name' => $expense->category_name])->id;
        }

        if (isset($expense->vendor_name) and $expense->vendor_name && $expense->vendor_id == null)
        {
            $expense->vendor_id = ExpenseVendor::firstOrCreate(['name' => $expense->vendor_name])->id;
        }
        else if (isset($expense->vendor_name) && $expense->vendor_id == null)
        {
            $expense->vendor_id = 0;
        }

        if ($expense->company_profile)
        {
            if (!CompanyProfile::where('company', $expense->company_profile)->count())
            {
                $expense->company_profile_id = config('fi.defaultCompanyProfile');
            }
        }

        if (isset($expense->client_name) and $expense->client_name)
        {
            $client = Client::firstOrCreateByUniqueName($expense->client_name);
            if (false === $client)
            {
                return response()->json(['errors' => [[trans('fi.no_auth_to_create_client')]]], 403);
            }
            else
            {
                $expense->client_id = $client->id;
            }
        }
        else if (isset($expense->client_name))
        {
            $expense->client_id = 0;
        }

        unset($expense->company_profile, $expense->client_name, $expense->vendor_name, $expense->category_name);
    }
}
