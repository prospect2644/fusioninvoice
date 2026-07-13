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
use FI\Modules\Users\Models\User;
use Illuminate\Database\Migrations\Migration;

class MailFromEmailAndMailFromNameChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mailFromAddress = Setting::getByKey('mailFromAddress');

        if ($mailFromAddress == '')
        {
            $firstAdminUser = User::whereUserType('admin')->whereStatus(1)->orderBy('id', 'desc')->first();
            if ($firstAdminUser)
            {
                Setting::saveByKey('mailFromAddress', $firstAdminUser->email);
                User::whereUserType('system')->update(['email' => $firstAdminUser->email]);
            }
        }
        else
        {
            User::whereUserType('system')->update(['email' => $mailFromAddress]);
        }
    }
}