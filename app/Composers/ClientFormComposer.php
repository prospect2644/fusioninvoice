<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Composers;

use FI\Modules\Clients\Models\Client;
use FI\Modules\Currencies\Models\Currency;
use FI\Modules\Invoices\Support\InvoiceTemplates;
use FI\Modules\Quotes\Support\QuoteTemplates;
use FI\Support\Languages;

class ClientFormComposer
{
    public function compose($view)
    {
        $view->with('currencies', Currency::getList())
            ->with('invoiceTemplates', InvoiceTemplates::lists())
            ->with('quoteTemplates', QuoteTemplates::lists())
            ->with('languages', Languages::listLanguages())
            ->with('timezones', ['' => ''] + array_combine(timezone_identifiers_list(), timezone_identifiers_list()))
            ->with('types', Client::getTypesList());
    }
}