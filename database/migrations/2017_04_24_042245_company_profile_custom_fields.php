<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyProfileCustomFields extends Migration
{
    public function up()
    {
        Schema::create('company_profiles_custom', function (Blueprint $table)
        {
            $table->integer('company_profile_id');
            $table->timestamps();

            $table->primary('company_profile_id');
        });

        foreach (CompanyProfile::get() as $companyProfile)
        {
            DB::table('company_profiles_custom')
                ->insert([
                    'created_at'         => $companyProfile->created_at,
                    'updated_at'         => $companyProfile->updated_at,
                    'company_profile_id' => $companyProfile->id,
                ]);
        }
    }
}
