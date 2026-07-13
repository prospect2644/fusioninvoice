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

use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Support\DateFormatter;
use FI\Traits\Sortable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasApiTokens, Authenticatable, CanResetPassword, Sortable, SoftDeletes;

    protected $table = 'users';

    protected $guarded = ['id', 'password', 'password_confirmation'];

    protected $hidden = ['password', 'remember_token'];

    protected $sortable = ['name', 'email'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function client()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client');
    }

    public function custom()
    {
        return $this->hasOne('FI\Modules\CustomFields\Models\UserCustom');
    }

    public function expenses()
    {
        return $this->hasMany('FI\Modules\Expenses\Models\Expense');
    }

    public function invoices()
    {
        return $this->hasMany('FI\Modules\Invoices\Models\Invoice');
    }

    public function quotes()
    {
        return $this->hasMany('FI\Modules\Quotes\Models\Quote');
    }

    public function permissions()
    {
        return $this->hasMany(UserPermissions::class);
    }

    public function tasks()
    {
        return $this->hasMany('FI\Modules\TaskList\Models\Task');
    }

    public function tasksByAssignee()
    {
        return $this->hasMany('FI\Modules\TaskList\Models\Task', 'assignee_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedLastLoginAtAttribute()
    {
        return $this->attributes['last_login_at'] ? DateFormatter::format($this->attributes['last_login_at'], true) : null;
    }

    public function getFormattedStatusAttribute()
    {
        return $this->attributes['status'] == 1 ? '<span title="Active"> <i class="fa fa-check-circle btn btn-xs btn-success" style="margin-left: 9px;"></i></span>' : '<span title="Inactive"> <i class="fa fa-times-circle btn btn-xs btn-danger" style="margin-left: 9px;"></i></span>';
    }

    public function getFormattedNameAttribute()
    {
        return $this->attributes['deleted_at'] != '' ? '<strike>' . $this->attributes['name'] . '</strike>' : $this->attributes['name'];
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUserType($query, $userType)
    {
        if (!empty($userType))
        {
            $query->where('user_type', $userType);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Other
    |--------------------------------------------------------------------------
    */

    public function customField($label, $rawHtml = true)
    {
        $customField = config('fi.customFields')->where('tbl_name', 'users')->where('field_label', $label)->first();

        if ($customField)
        {
            return CustomFieldsParser::getFieldValue($this->custom, $customField, $rawHtml);
        }

        return null;

    }

    public static function getAllUserTypes()
    {
        return [
            'admin'         => trans('fi.admin'),
            'standard_user' => trans('fi.standard_user'),
            'client'        => trans('fi.client'),
        ];
    }

    public static function getUserTypes()
    {
        return [
            'admin'         => trans('fi.admin'),
            'standard_user' => trans('fi.standard_user'),
        ];
    }

    public static function getStatus()
    {
        return [
            '1' => trans('fi.active'),
            '0' => trans('fi.inactive'),
        ];
    }

    public function hasPermission($module, $permission)
    {
        $userPermission = $this->permissions()->whereModule($module)->first();
        if (!empty($userPermission))
        {
            return 1 == $userPermission->$permission;
        }

        return false;
    }

    public function getAvatar($size = 40, $isRounded = true)
    {
        return view('users.avatar')
            ->with('user', $this)
            ->with('size', $size)
            ->with('isRounded', $isRounded)->render();
    }
}