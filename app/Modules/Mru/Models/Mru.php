<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Mru\Models;

use Illuminate\Database\Eloquent\Model;

class Mru extends Model
{
    protected $table = 'mru';

    protected $guarded = ['id'];
}