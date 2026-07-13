<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Settings\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyProfileLogoTemplates extends Migration
{
    public function up()
    {
        Schema::table('company_profiles', function (Blueprint $table)
        {
            $table->string('logo')->nullable();
            $table->string('quote_template');
            $table->string('invoice_template');
        });

        DB::table('company_profiles')->where('invoice_template', '')->update(['invoice_template' => Setting::getByKey('invoiceTemplate')]);
        DB::table('company_profiles')->where('quote_template', '')->update(['quote_template' => Setting::getByKey('quoteTemplate')]);
        DB::table('company_profiles')->where('logo', null)->update(['logo' => Setting::getByKey('logo')]);

        DB::table('settings')->where('setting_key', 'logo')->delete();
    }
}
