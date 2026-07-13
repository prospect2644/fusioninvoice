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

use FI\Modules\CustomFields\Models\UserCustom;

class UserObserver
{
    public function created(User $user)
    {
        $user->custom()->save(new UserCustom());

        if (!$user->initials)
        {
            $user->initials = $this->getUserInitials($user->name);
        }

        if (!$user->initials_bg_color)
        {
            $user->initials_bg_color = $this->getUserInitialsBgColor();
        }

        $user->save();
    }

    public function deleted(User $user)
    {
        foreach ($user->tasks as $task)
        {
            $task->delete();
        }

        foreach ($user->tasksByAssignee as $task)
        {
            $task->delete();
        }

        foreach ($user->permissions as $permission)
        {
            $permission->delete();
        }

        $user->custom()->delete();
    }

    public function getUserInitials(string $name) : string
    {
        $words = explode(' ', $name);
        if (count($words) >= 2)
        {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        return $this->makeInitialsFromSingleWord($name);
    }

    protected function makeInitialsFromSingleWord(string $name) : string
    {
        preg_match_all('#([A-Z]+)#', $name, $capitals);
        if (count($capitals[1]) >= 2)
        {
            return substr(implode('', $capitals[1]), 0, 2);
        }
        return strtoupper(substr($name, 0, 2));
    }

    public function getUserInitialsBgColor() : string
    {
        $colors          = ["#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
                            "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#95a5a6", "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"];
        $userRandomColor = $colors[array_rand($colors)];
        return $userRandomColor;
    }

}