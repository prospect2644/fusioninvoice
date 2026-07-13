<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Providers;

use FI\Modules\Attachments\Models\Attachment;
use FI\Modules\Attachments\Models\AttachmentObserver;
use FI\Modules\Clients\Models\Client;
use FI\Modules\Clients\Models\ClientObserver;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\CompanyProfiles\Models\CompanyProfileObserver;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\Expenses\Models\ExpenseCategory;
use FI\Modules\Expenses\Models\ExpenseCategoryObserver;
use FI\Modules\Expenses\Models\ExpenseObserver;
use FI\Modules\Expenses\Models\ExpenseVendor;
use FI\Modules\Expenses\Models\ExpenseVendorObserver;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Invoices\Models\InvoiceItem;
use FI\Modules\Invoices\Models\InvoiceItemObserver;
use FI\Modules\Invoices\Models\InvoiceObserver;
use FI\Modules\ItemLookups\Models\ItemLookup;
use FI\Modules\ItemLookups\Models\ItemLookupObserver;
use FI\Modules\Notes\Models\Note;
use FI\Modules\Notes\Models\NoteObserver;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Payments\Models\PaymentInvoice;
use FI\Modules\Payments\Models\PaymentObserver;
use FI\Modules\Payments\Models\PaymentInvoiceObserver;
use FI\Modules\Quotes\Models\Quote;
use FI\Modules\Quotes\Models\QuoteItem;
use FI\Modules\Quotes\Models\QuoteItemObserver;
use FI\Modules\Quotes\Models\QuoteObserver;
use FI\Modules\RecurringInvoices\Models\RecurringInvoice;
use FI\Modules\RecurringInvoices\Models\RecurringInvoiceItem;
use FI\Modules\RecurringInvoices\Models\RecurringInvoiceItemObserver;
use FI\Modules\RecurringInvoices\Models\RecurringInvoiceObserver;
use FI\Modules\Settings\Models\Setting;
use FI\Modules\Settings\Models\SettingObserver;
use FI\Modules\TaskList\Models\Task;
use FI\Modules\TaskList\Models\TaskObserver;
use FI\Modules\Users\Models\User;
use FI\Modules\Users\Models\UserObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        'FI\Modules\Attachments\EventSubscriber',
        'FI\Modules\Clients\EventSubscriber',
        'FI\Modules\Invoices\EventSubscriber',
        'FI\Modules\Quotes\EventSubscriber',
        'FI\Modules\RecurringInvoices\EventSubscriber',
        'FI\Modules\Mru\EventSubscriber',
        'FI\Modules\Payments\EventSubscriber',
        'FI\Modules\Notes\EventSubscriber',
        'FI\Modules\TaskList\EventSubscriber',
        'FI\Modules\Expenses\EventSubscriber',
    ];

    public function boot()
    {
        parent::boot();

        Attachment::observe(AttachmentObserver::class);
        Client::observe(ClientObserver::class);
        CompanyProfile::observe(CompanyProfileObserver::class);
        Expense::observe(ExpenseObserver::class);
        ExpenseCategory::observe(ExpenseCategoryObserver::class);
        ExpenseVendor::observe(ExpenseVendorObserver::class);
        Invoice::observe(InvoiceObserver::class);
        InvoiceItem::observe(InvoiceItemObserver::class);
        Payment::observe(PaymentObserver::class);
        PaymentInvoice::observe(PaymentInvoiceObserver::class);
        Quote::observe(QuoteObserver::class);
        QuoteItem::observe(QuoteItemObserver::class);
        RecurringInvoice::observe(RecurringInvoiceObserver::class);
        RecurringInvoiceItem::observe(RecurringInvoiceItemObserver::class);
        Setting::observe(SettingObserver::class);
        User::observe(UserObserver::class);
        Note::observe(NoteObserver::class);
        Task::observe(TaskObserver::class);
        ItemLookup::observe(ItemLookupObserver::class);
    }
}
