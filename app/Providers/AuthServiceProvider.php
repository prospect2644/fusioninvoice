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

use Addons\Containers\Policies\Container;
use Addons\TimeTracking\Policies\TimeTrackingPolicy;
use FI\Modules\Addons\Policies\AddonPolicy;
use FI\Modules\Attachments\Policies\AttachmentPolicy;
use FI\Modules\Clients\Policies\ClientPolicy;
use FI\Modules\Clients\Policies\ContactPolicy;
use FI\Modules\CompanyProfiles\Policies\CompanyProfilePolicy;
use FI\Modules\Currencies\Policies\CurrenciesPolicy;
use FI\Modules\CustomFields\Policies\CustomFieldPolicy;
use FI\Modules\Dashboard\Policies\AllowTimePeriodChangePolicy;
use FI\Modules\Dashboard\Policies\ClientTimeLinePolicy;
use FI\Modules\Dashboard\Policies\InvoiceSummaryPolicy;
use FI\Modules\Dashboard\Policies\QuoteSummaryPolicy;
use FI\Modules\Dashboard\Policies\RecentClientActivityPolicy;
use FI\Modules\DocumentNumberSchemes\Policies\DocumentNumberSchemePolicy;
use FI\Modules\Expenses\Policies\ExpenseCategoriesPolicy;
use FI\Modules\Expenses\Policies\ExpensesPolicy;
use FI\Modules\Expenses\Policies\ExpenseVendorPolicy;
use FI\Modules\Exports\Policies\ExportPolicy;
use FI\Modules\Import\Policies\ImportPolicy;
use FI\Modules\Invoices\Policies\InvoicePolicy;
use FI\Modules\ItemLookups\Policies\ItemCategoriesPolicy;
use FI\Modules\ItemLookups\Policies\ItemLookupPolicy;
use FI\Modules\MailQueue\Policies\MailQueuePolicy;
use FI\Modules\Notes\Policies\NotePolicy;
use FI\Modules\PaymentMethods\Policies\PaymentMethodPolicy;
use FI\Modules\Payments\Policies\PaymentsPolicy;
use FI\Modules\Quotes\Policies\QuotePolicy;
use FI\Modules\RecurringInvoices\Policies\RecurringInvoicePolicy;
use FI\Modules\Reports\Policies\ClientInvoicePolicy;
use FI\Modules\Reports\Policies\ClientStatementPolicy;
use FI\Modules\Reports\Policies\ExpenseListPolicy;
use FI\Modules\Reports\Policies\ItemSalesPolicy;
use FI\Modules\Reports\Policies\PaymentsCollectedPolicy;
use FI\Modules\Reports\Policies\ProfitAndLossPolicy;
use FI\Modules\Reports\Policies\RecurringInvoiceListPolicy;
use FI\Modules\Reports\Policies\RevenueByClientPolicy;
use FI\Modules\Reports\Policies\TaxSummaryPolicy;
use FI\Modules\Settings\Policies\SettingPolicy;
use FI\Modules\SystemLog\Policies\SystemLogPolicy;
use FI\Modules\Tags\Policies\TagPolicy;
use FI\Modules\TaxRates\Policies\TaxRatePolicy;
use FI\Modules\Users\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;


class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        'FI\Model' => 'FI\Policies\ModelPolicy',
    ];

    protected static $methods = ['view' => 'view', 'create' => 'create', 'update' => 'update', 'delete' => 'delete'];

    public function boot()
    {
        // Modules Policies
        $this->registerPolicies();
        $this->registerInvoicePolicies();
        $this->registerClientPolicies();
        $this->registerContactPolicies();
        $this->registerQuotePolicies();
        $this->registerPaymentPolicies();
        $this->registerRecurringInvoicePolicies();
        $this->registerExpensesPolicies();
        $this->registerAttachmentPolicies();
        $this->registerNotePolicies();
        $this->registerTagPolicies();

        // Reports Policies
        $this->registerClientInvoicePolicies();
        $this->registerClientStatementPolicies();
        $this->registerExpenseListPolicies();
        $this->registerItemSalesPolicies();
        $this->registerPaymentsCollectedPolicies();
        $this->registerProfitAndLossPolicies();
        $this->registerRecurringInvoiceListPolicies();
        $this->registerRevenueByClientPolicies();
        $this->registerTaxSummaryPolicies();

        // Dashboard Policies
        $this->registerAllowTimePeriodChangePolicies();
        $this->registerInvoiceSummaryPolicies();
        $this->registerQuoteSummaryPolicies();
        $this->registerRecentClientActivityPolicies();
        $this->registerClientTimeLinePolicies();

        //Admin Policies
        $this->registerAddonPolicies();
        $this->registerCurrenciePolicies();
        $this->registerCustomFieldPolicies();
        $this->registerExpenseCategoriesPolicies();
        $this->registerExpenseVendorPolicies();
        $this->registerCompanyProfilePolicies();
        $this->registerExportPolicies();
        $this->registerDocumentNumberSchemePolicies();
        $this->registerImportPolicies();
        $this->registerItemCategoriesPolicies();
        $this->registerItemLookupPolicies();
        $this->registerMailQueuePolicies();
        $this->registerPaymentMethodPolicies();
        $this->registerTaxRatePolicies();
        $this->registerUserPolicies();
        $this->registerSettingPolicies();
        $this->registerSystemLogPolicies();

        //Addons policies
        $this->registerTimeTrackingPolicy();
        $this->registerContainerPolicy();

        //Passport
        Passport::routes();
        Passport::ignoreMigrations();

    }

    protected function registerInvoicePolicies()
    {
        Gate::resource('invoices', InvoicePolicy::class, self::$methods);
    }

    private function registerClientPolicies()
    {
        Gate::resource('clients', ClientPolicy::class, self::$methods);
    }

    private function registerContactPolicies()
    {
        Gate::resource('contacts', ContactPolicy::class, self::$methods);
    }

    private function registerQuotePolicies()
    {
        Gate::resource('quotes', QuotePolicy::class, self::$methods);
    }

    private function registerPaymentPolicies()
    {
        Gate::resource('payments', PaymentsPolicy::class, self::$methods);
    }

    private function registerRecurringInvoicePolicies()
    {
        Gate::resource('recurring_invoices', RecurringInvoicePolicy::class, self::$methods);
    }

    private function registerExpensesPolicies()
    {
        Gate::resource('expenses', ExpensesPolicy::class, self::$methods);
    }

    private function registerAttachmentPolicies()
    {
        Gate::resource('attachments', AttachmentPolicy::class, self::$methods);
    }

    private function registerNotePolicies()
    {
        Gate::resource('notes', NotePolicy::class, self::$methods);
    }

    private function registerClientStatementPolicies()
    {
        Gate::resource('client_statement', ClientStatementPolicy::class, self::$methods);
    }

    private function registerExpenseListPolicies()
    {
        Gate::resource('expense_list', ExpenseListPolicy::class, self::$methods);
    }

    private function registerItemSalesPolicies()
    {
        Gate::resource('item_sales', ItemSalesPolicy::class, self::$methods);
    }

    private function registerPaymentsCollectedPolicies()
    {
        Gate::resource('payments_collected', PaymentsCollectedPolicy::class, self::$methods);
    }

    private function registerProfitAndLossPolicies()
    {
        Gate::resource('profit_and_loss', ProfitAndLossPolicy::class, self::$methods);
    }

    private function registerRecurringInvoiceListPolicies()
    {
        Gate::resource('recurring_invoice_list', RecurringInvoiceListPolicy::class, self::$methods);
    }

    private function registerRevenueByClientPolicies()
    {
        Gate::resource('revenue_by_client', RevenueByClientPolicy::class, self::$methods);
    }

    private function registerTaxSummaryPolicies()
    {
        Gate::resource('tax_summary', TaxSummaryPolicy::class, self::$methods);
    }

    private function registerAllowTimePeriodChangePolicies()
    {
        Gate::resource('allow_time_period_change', AllowTimePeriodChangePolicy::class, self::$methods);
    }

    private function registerInvoiceSummaryPolicies()
    {
        Gate::resource('invoice_summary', InvoiceSummaryPolicy::class, self::$methods);
    }

    private function registerQuoteSummaryPolicies()
    {
        Gate::resource('quote_summary', QuoteSummaryPolicy::class, self::$methods);
    }

    private function registerRecentClientActivityPolicies()
    {
        Gate::resource('recent_client_activity', RecentClientActivityPolicy::class, self::$methods);
    }

    private function registerAddonPolicies()
    {
        Gate::resource('addons', AddonPolicy::class, self::$methods);
    }

    private function registerCurrenciePolicies()
    {
        Gate::resource('currencies', CurrenciesPolicy::class, self::$methods);
    }

    private function registerCustomFieldPolicies()
    {
        Gate::resource('custom_fields', CustomFieldPolicy::class, self::$methods);
    }

    private function registerExpenseCategoriesPolicies()
    {
        Gate::resource('expense_categories', ExpenseCategoriesPolicy::class, self::$methods);
    }

    private function registerExpenseVendorPolicies()
    {
        Gate::resource('expense_vendors', ExpenseVendorPolicy::class, self::$methods);
    }

    private function registerCompanyProfilePolicies()
    {
        Gate::resource('company_profiles', CompanyProfilePolicy::class, self::$methods);
    }

    private function registerExportPolicies()
    {
        Gate::resource('exports', ExportPolicy::class, self::$methods);
    }

    private function registerDocumentNumberSchemePolicies()
    {
        Gate::resource('document_number_schemes', DocumentNumberSchemePolicy::class, self::$methods);
    }

    private function registerImportPolicies()
    {
        Gate::resource('import', ImportPolicy::class, self::$methods);
    }

    private function registerItemCategoriesPolicies()
    {
        Gate::resource('item_categories', ItemCategoriesPolicy::class, self::$methods);
    }

    private function registerItemLookupPolicies()
    {
        Gate::resource('item_lookup', ItemLookupPolicy::class, self::$methods);
    }

    private function registerMailQueuePolicies()
    {
        Gate::resource('mail_queue', MailQueuePolicy::class, self::$methods);
    }

    private function registerPaymentMethodPolicies()
    {
        Gate::resource('payment_methods', PaymentMethodPolicy::class, self::$methods);
    }

    private function registerTaxRatePolicies()
    {
        Gate::resource('tax_rates', TaxRatePolicy::class, self::$methods);
    }

    private function registerUserPolicies()
    {
        Gate::resource('users', UserPolicy::class, self::$methods);
    }

    private function registerSettingPolicies()
    {
        Gate::resource('settings', SettingPolicy::class, self::$methods);
    }

    private function registerTagPolicies()
    {
        Gate::resource('tags', TagPolicy::class, self::$methods);
    }

    private function registerTimeTrackingPolicy()
    {
        Gate::resource('time_tracking', TimeTrackingPolicy::class, self::$methods);
    }

    private function registerClientInvoicePolicies()
    {
        Gate::resource('client_invoice', ClientInvoicePolicy::class, self::$methods);
    }

    private function registerSystemLogPolicies()
    {
        Gate::resource('system_logs', SystemLogPolicy::class, self::$methods);
    }

    private function registerClientTimeLinePolicies()
    {
        Gate::resource('client_timeline', ClientTimeLinePolicy::class, self::$methods);
    }

    private function registerContainerPolicy()
    {
        Gate::resource('containers', Container::class, self::$methods);
    }
}
