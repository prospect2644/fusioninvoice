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
use Illuminate\Support\Facades\DB;

class CreateAvatarForExistingUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = DB::table('users')->whereNull('initials')->get();
        foreach ($users as $user)
        {
            $name = $user->name;
            preg_replace("[^a-zA-Z ]", '', $name);
            $nameParts  = explode(' ', $name);
            $initials1  = substr($nameParts[0], 0, 1);
            $initials   = $initials1;
            $partsCount = count($nameParts);
            if (2 > $partsCount)
            {
                $initials2 = substr($nameParts[0], 1, 1);
                $initials .= $initials2;
            }
            else
            {
                $initials2 = substr($nameParts[$partsCount - 1], 0, 1);
                $initials .= $initials2;
            }

            $initials   = strtoupper($initials);
            $colors     = [
                "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
                "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#95a5a6", "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d",
            ];
            $charIndex  = ord($initials1) + (!empty($initials2) ? ord($initials2) : 0);
            $colorIndex = $charIndex % 20;
            $colorIndex = 0 >= $colorIndex ? 1 : $colorIndex;
            $color      = $colors[$colorIndex - 1];

            DB::table('users')->where('id', $user->id)->update(['initials' => $initials, 'initials_bg_color' => $color]);
        }
    }
}
