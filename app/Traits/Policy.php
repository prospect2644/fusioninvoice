<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Traits;

use FI\Modules\Users\Models\User;

trait Policy
{
    /**
     * @param $user
     * @return bool
     */
    public function before(User $user)
    {
        if (in_array($user->user_type, ['admin']))
        {
            return true;
        }
    }

    /**
     * Determine whether the user can view the module.
     * @param User $user
     * @return bool
     */
    public function view(User $user)
    {
        return 1 == $user->hasPermission(static::$module, 'is_view');
    }

    /**
     * Determine whether the user can create module.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return 1 == $user->hasPermission(static::$module, 'is_create');
    }

    /**
     * Determine whether the user can update the module.
     *
     * @param User $user
     * @return mixed
     */
    public function update(User $user)
    {
        return 1 == $user->hasPermission(static::$module, 'is_update');
    }

    /**
     * Determine whether the user can delete the module.
     *
     * @param User $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return 1 == $user->hasPermission(static::$module, 'is_delete');
    }
}