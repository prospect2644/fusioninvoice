<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Users\Controllers;

use Exception;
use FI\Http\Controllers\Controller;
use FI\Modules\Addons\Models\Addon;
use FI\Modules\Clients\Models\Client;
use FI\Modules\CustomFields\Models\UserCustom;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\CustomFields\Support\CustomFieldsTransformer;
use FI\Modules\Settings\Models\UserSetting;
use FI\Modules\Users\Models\User;
use FI\Modules\Users\Models\UserPermissions;
use FI\Modules\Users\Requests\UserStoreRequest;
use FI\Modules\Users\Requests\UserUpdateRequest;
use FI\Support\DashboardWidgets;
use FI\Traits\ReturnUrl;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        $users = User::sortable(['name' => 'asc'])
            ->userType(request('userType'))
            ->where('user_type', '!=', 'system')
            ->leftJoin('users_custom', 'users_custom.user_id', '=', 'users.id')
            ->paginate(config('fi.resultsPerPage'));

        return view('users.index')
            ->with('users', $users)
            ->with('userTypes', User::getUserTypes())
            ->with('allUserTypes', User::getAllUserTypes());
    }

    public function create($userType)
    {
        $permissionsCopiedFrom = User::where('user_type', 'standard_user')->get()->pluck('name', 'id')->toArray();

        $view = view('users.admin_form')
            ->with('editMode', false)
            ->with('customFields', CustomFieldsParser::getFields('users'))
            ->with('userTypes', User::getAllUserTypes())
            ->with('enabledAddons', Addon::getEnabledAddons())
            ->with('userType', $userType)
            ->with('permissibleItems', UserPermissions::getAllPermissibleItems())
            ->with('permissionsCopiedFrom', ['' => trans('fi.select_user')] + $permissionsCopiedFrom)
            ->with('returnUrl', $this->getReturnUrl())
            ->with('yesNoArray', ['0' => trans('fi.no'), '1' => trans('fi.yes')])
            ->with('displayOrderArray', array_combine(range(1, 24), range(1, 24)))
            ->with('colWidthArray', array_combine(range(1, 12), range(1, 12)))
            ->with('status', User::getStatus())
            ->with('dashboardWidgets', DashboardWidgets::listsByOrder());

        return $view;
    }

    public function store(UserStoreRequest $request)
    {
        $user = new User($request->except('custom', 'permissions', 'check-all', 'permissions_copied_from', 'setting'));

        $user->password = $request->input('password');

        $user->save();

        $permissions = $request->post('permissions', []);
        if (!empty($permissions))
        {
            foreach ($permissions as $module => $permission)
            {
                UserPermissions::create(array_merge(['user_id' => $user->id, 'module' => $module], $permission));
            }
        }

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'users', $user);
        $user->custom->update($customFieldData);

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_created'));
    }

    public function edit($id, $userType)
    {
        $role = $userType;
        if ('client' !== $userType)
        {
            $userType = 'admin';
        }
        $user = User::find($id);

        $permissions = $user->permissions->keyBy('module')->toArray();

        $permissionsCopiedFrom = User::where('user_type', 'standard_user')->where('id', '!=', $id)->get()->pluck('name', 'id')->toArray();

        return view('users.' . $userType . '_form')
            ->with(['editMode' => true, 'user' => $user])
            ->with('customFields', CustomFieldsParser::getFields('users'))
            ->with('userTypes', User::getUserTypes())
            ->with('userType', $role)
            ->with('permissibleItems', UserPermissions::getAllPermissibleItems())
            ->with('permissions', $permissions)
            ->with('enabledAddons', Addon::getEnabledAddons())
            ->with('returnUrl', $this->getReturnUrl())
            ->with('permissionsCopiedFrom', ['' => trans('fi.select_user')] + $permissionsCopiedFrom)
            ->with('dashboardWidgets', DashboardWidgets::listsByOrder())
            ->with('yesNoArray', ['0' => trans('fi.no'), '1' => trans('fi.yes')])
            ->with('displayOrderArray', array_combine(range(1, 24), range(1, 24)))
            ->with('status', User::getStatus())
            ->with('colWidthArray', array_combine(range(1, 12), range(1, 12)));
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::find($id);

        $adminCount = User::whereUserType('admin')->get()->count();
        if ($adminCount == 1 && $user->user_type == 'admin' && $request->user_type != 'admin')
        {
            return redirect()->route('users.index')
                ->with('alert', trans('fi.can-not-change-default-user-role'));
        }

        if ($adminCount == 1 && $user->user_type == 'admin' && isset($request->status) && $request->status != 1)
        {
            return redirect()->route('users.index')
                ->with('alert', trans('fi.can-not-change-default-user-status'));
        }

        if (isset($request->status) && $request->status != 1 && auth()->user()->id == $id)
        {
            return redirect()->route('users.index')
                ->with('alert', trans('fi.can-not-inactive-your-own-status'));
        }

        $userSettings = $request->post('setting', []);
        if (!empty($userSettings))
        {
            foreach ($userSettings as $key => $userSetting)
            {
                UserSetting::saveByKey($key, $userSetting, $user);
            }
        }
        $user->fill($request->except('custom', 'permissions', 'check-all', 'permissions_copied_from', 'setting'));

        $user->save();

        $defaultPermissions = ['is_view' => 0, 'is_create' => 0, 'is_update' => 0, 'is_delete' => 0];
        $permissions        = $request->post('permissions', []);

        if (!empty($permissions))
        {
            foreach ($user->permissions as $userPermission)
            {

                $permission     = $permissions[$userPermission->module] ?? $defaultPermissions;
                $permissionData = array_merge($defaultPermissions, $permission);
                unset($permissions[$userPermission->module]);
                $userPermission->update($permissionData);

                if ($userPermission->module == 'allow_time_period_change' && $userPermission->is_view == 0)
                {
                    UserSetting::deleteByKey('dashboardWidgetsDateOption', $user);
                }

            }

            foreach ($permissions as $module => $permission)
            {
                UserPermissions::create(array_merge(['user_id' => $user->id, 'module' => $module], $permission));
            }
        }
        else
        {
            $user->permissions->each(function ($userPermission) use ($defaultPermissions)
            {
                $userPermission->update($defaultPermissions);
            });
        }

        //If user type is changed from standard users to another type then we have to delete all permissions
        if ($user->user_type != 'standard_user')
        {
            UserPermissions::whereUserId($user->id)->delete();
        }

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'users', $user);
        $user->custom->update($customFieldData);

        return redirect()->route('users.index')
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

    public function delete($id)
    {
        $adminCount = User::whereUserType('admin')->get()->count();
        $isAdmin    = User::whereId($id)->whereUserType('admin')->first();
        if ($adminCount == 1 && $isAdmin)
        {
            return redirect()->route('users.index')
                ->with('alert', trans('fi.can-not-delete-all-users'));
        }

        User::destroy($id);

        return redirect()->route('users.index')
            ->with('alert', trans('fi.record_successfully_deleted'));
    }

    public function getClientInfo()
    {
        return Client::find(request('id'));
    }

    public function deleteImage($id, $columnName)
    {
        $customFields = UserCustom::whereCompanyProfileId($id)->first();

        $existingFile = 'users' . DIRECTORY_SEPARATOR . $customFields->{$columnName};
        if (Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->exists($existingFile))
        {
            try
            {
                Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->delete($existingFile);
                $customFields->{$columnName} = null;
                $customFields->save();
            }
            catch (Exception $e)
            {

            }
        }
    }

    public function getPermissions($id)
    {
        $permissions = User::find($id)->permissions->toArray();

        return response()->json($permissions);
    }

    public function updateStatus($id)
    {
        $user = User::find($id);

        $adminCount = User::whereUserType('admin')->get()->count();

        if ($adminCount == 1 && $user->user_type == 'admin')
        {
            return redirect()->route('users.index')
                ->with('alert', trans('fi.can-not-change-default-user-status'));
        }

        if (auth()->user()->id == $id)
        {
            return redirect()->route('users.index')
                ->with('alert', trans('fi.can-not-inactive-your-own-status'));
        }

        $user->status = 0;

        $user->save();

        return redirect()->route('users.index')
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }
}