<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\CompanyProfiles\Controllers'], function ()
{
    Route::get('company_profiles', ['uses' => 'CompanyProfileController@index', 'as' => 'companyProfiles.index'])->middleware('can:company_profiles.view');
    Route::get('company_profiles/create', ['uses' => 'CompanyProfileController@create', 'as' => 'companyProfiles.create'])->middleware('can:company_profiles.create');
    Route::get('company_profiles/{id}/edit', ['uses' => 'CompanyProfileController@edit', 'as' => 'companyProfiles.edit'])->middleware('can:company_profiles.update');
    Route::get('company_profiles/{id}/delete', ['uses' => 'CompanyProfileController@delete', 'as' => 'companyProfiles.delete'])->middleware('can:company_profiles.delete');

    Route::post('company_profiles', ['uses' => 'CompanyProfileController@store', 'as' => 'companyProfiles.store'])->middleware('can:company_profiles.create');
    Route::post('company_profiles/{id}', ['uses' => 'CompanyProfileController@update', 'as' => 'companyProfiles.update'])->middleware('can:company_profiles.update');

    Route::post('company_profiles/{id}/delete_logo', ['uses' => 'CompanyProfileController@deleteLogo', 'as' => 'companyProfiles.deleteLogo'])->middleware('can:company_profiles.update');
    Route::post('company_profiles/ajax/modal_lookup', ['uses' => 'CompanyProfileController@ajaxModalLookup', 'as' => 'companyProfiles.ajax.modalLookup'])->middleware('can:company_profiles.view');
    Route::post('company_profiles/custom_field/{id?}/delete_image/{field_name?}', ['uses' => 'CompanyProfileController@deleteImage', 'as' => 'companyProfiles.deleteImage'])->middleware('can:company_profiles.update');
});

Route::get('company_profiles/{id}/logo', ['uses' => 'FI\Modules\CompanyProfiles\Controllers\LogoController@logo', 'as' => 'companyProfiles.logo'])->middleware('can:company_profiles.view');