<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermissions extends Model
{
    protected $table = 'user_permissions';

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Static data
    |--------------------------------------------------------------------------
    */
    public static function getAllPermissibleItems()
    {
        $actions       = ['is_view'];
        $moduleActions = ['is_view', 'is_create', 'is_update', 'is_delete'];
        return [
            'modules'    => [
                [
                    'name'    => trans('fi.clients'),
                    'slug'    => 'clients',
                    'actions' => $moduleActions,
                ],
                [
                    'name'    => trans('fi.contacts'),
                    'slug'    => 'contacts',
                    'actions' => $moduleActions,
                ],
                [
                    'name'    => trans('fi.invoices'),
                    'slug'    => 'invoices',
                    'actions' => $moduleActions,
                ],
                [
                    'name'    => trans('fi.recurring_invoices'),
                    'slug'    => 'recurring_invoices',
                    'actions' => $moduleActions,
                ],
                [
                    'name'    => trans('fi.quotes'),
                    'slug'    => 'quotes',
                    'actions' => $moduleActions,
                ],
                [
                    'name'    => trans('fi.payments'),
                    'slug'    => 'payments',
                    'actions' => $moduleActions,
                ],
                [
                    'name'    => trans('fi.expenses'),
                    'slug'    => 'expenses',
                    'actions' => $moduleActions,
                ],
                [
                    'name'    => trans('fi.notes'),
                    'slug'    => 'notes',
                    'actions' => $moduleActions,
                ],
                [
                    'name'    => trans('fi.attachments'),
                    'slug'    => 'attachments',
                    'actions' => $moduleActions,
                ],
            ],
            'reports'    => [
                [
                    'name'    => trans('fi.client_statement'),
                    'slug'    => 'client_statement',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.expense_list'),
                    'slug'    => 'expense_list',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.item_sales'),
                    'slug'    => 'item_sales',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.payments_collected'),
                    'slug'    => 'payments_collected',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.profit_and_loss'),
                    'slug'    => 'profit_and_loss',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.revenue_by_client'),
                    'slug'    => 'revenue_by_client',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.tax_summary'),
                    'slug'    => 'tax_summary',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.recurring_invoice_list'),
                    'slug'    => 'recurring_invoice_list',
                    'actions' => $actions,
                ],
            ],
            'dashboards' => [
                [
                    'name'    => trans('fi.invoice_summary'),
                    'slug'    => 'invoice_summary',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.quote_summary'),
                    'slug'    => 'quote_summary',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.recent_client_activity'),
                    'slug'    => 'recent_client_activity',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.allow_time_period_change'),
                    'slug'    => 'allow_time_period_change',
                    'actions' => $actions,
                ],
                [
                    'name'    => trans('fi.client_timeline'),
                    'slug'    => 'client_timeline',
                    'actions' => $actions,
                ],
            ],
            'addons'     => [
                trans('TimeTracking::lang.time_tracking') => [
                    'name'    => trans('TimeTracking::lang.time_tracking'),
                    'slug'    => 'time_tracking',
                    'actions' => $moduleActions,
                ],
                trans('Containers::lang.containers')      => [
                    'name'    => trans('Containers::lang.containers'),
                    'slug'    => 'containers',
                    'actions' => $moduleActions,
                ],
            ],
        ];
    }
}
