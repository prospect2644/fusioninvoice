<?php
/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use FI\Modules\Settings\Models\Setting;

class EntityWiseTransitionHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Setting::saveByKey('clientTransitionHistoryCreated', 0);
        Setting::saveByKey('expenseTransitionHistoryCreated', 0);
        Setting::saveByKey('invoiceTransitionHistoryCreated', 0);
        Setting::saveByKey('paymentInvoiceTransitionHistoryCreated', 0);
        Setting::saveByKey('paymentTransitionHistoryCreated', 0);
        Setting::saveByKey('quoteTransitionHistoryCreated', 0);
        Setting::saveByKey('noteTransitionHistoryCreated', 0);
        Setting::saveByKey('taskTransitionHistoryCreated', 0);
    }

}
