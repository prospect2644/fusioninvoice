<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Clients\Models;

use FI\Modules\Clients\Events\AddTransition;
use FI\Modules\Clients\Support\ClientInvoicePrefixGenerator;
use FI\Modules\CustomFields\Models\ClientCustom;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Mru\Models\Mru;
use FI\Modules\RecurringInvoices\Models\RecurringInvoice;
use Illuminate\Support\Str;

class ClientObserver
{
    public function created(Client $client)
    {
        $client->custom()->save(new ClientCustom());

        event(new AddTransition($client, 'client_created'));
    }

    public function creating(Client $client)
    {
        $client->url_key = Str::random(32);

        if (!$client->currency_code)
        {
            $client->currency_code = config('fi.baseCurrency');
        }

        if (!$client->language)
        {
            $client->language = config('fi.language');
        }

        if (!$client->invoice_prefix)
        {
            $clientInvoicePrefixGenerator = new ClientInvoicePrefixGenerator();
            do
            {
                $invoicePrefix = $clientInvoicePrefixGenerator->invoicePrefixGenerator();
            } while ($clientInvoicePrefixGenerator->isUnique($invoicePrefix));

            $client->invoice_prefix = $invoicePrefix;
        }

    }

    public function deleted(Client $client)
    {
        foreach ($client->payments as $payment)
        {
            $payment->delete();
        }

        foreach ($client->notes as $note)
        {
            $note->delete();
        }

        foreach ($client->expenses as $expense)
        {
            $expense->delete();
        }

        foreach ($client->contacts as $contact)
        {
            $contact->delete();
        }

        if ($client->user)
        {
            $client->user->delete();
        }

        if ($client->custom)
        {
            $client->custom->delete();
        }

        if ($client->merchant)
        {
            $client->merchant->delete();
        }

        foreach ($client->tags as $tag)
        {
            $tag->delete();
        }

        foreach ($client->tasks as $task)
        {
            $task->delete();
        }

        foreach ($client->quotes as $quote)
        {
            $quote->delete();
        }

        foreach ($client->recurringInvoices as $recurringInvoice)
        {
            $recurringInvoice->delete();
        }

        foreach ($client->invoices as $invoice)
        {
            $invoice->delete();
        }

        Mru::whereUserId(auth()->user()->id)->whereModule('clients')->whereElementId($client->id)->delete();

        $client->where('parent_client_id', $client->id)->update(['parent_client_id' => null]);
        event(new AddTransition($client, 'deleted'));
    }

    public function saving(Client $client)
    {
        $client->name    = strip_tags($client->name);
        $client->address = strip_tags($client->address);

        if (!$client->unique_name)
        {
            $client->unique_name = $client->name;
        }

        if ($client->type !== 'customer' && $client->type !== 'affiliate')
        {
            if (Invoice::where('client_id', $client->id)->count() > 0
                or RecurringInvoice::where('client_id', $client->id)->count() > 0
            )
            {
                $client->type = 'customer';
            }
        }

        if ($client->parent_client_id == 0)
        {
            $client->parent_client_id = null;
        }
    }

    public function updating(Client $client)
    {
        if ($client->isDirty('type'))
        {
            event(new AddTransition($client, 'type_changed', $client->getOriginal('type'), $client->type));
        }
        elseif ($client->isDirty('active'))
        {
            event(new AddTransition($client, 'status_changed', $client->getOriginal('active'), $client->active));
        }
        else
        {
            event(new AddTransition($client, 'updated'));
        }
    }
}