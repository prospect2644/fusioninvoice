<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\CompanyProfiles\Controllers;

use Exception;
use FI\Http\Controllers\Controller;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\CompanyProfiles\Requests\CompanyProfileStoreRequest;
use FI\Modules\CompanyProfiles\Requests\CompanyProfileUpdateRequest;
use FI\Modules\Countries\Models\Country;
use FI\Modules\CustomFields\Models\CompanyProfileCustom;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\CustomFields\Support\CustomFieldsTransformer;
use FI\Modules\Invoices\Support\InvoiceTemplates;
use FI\Modules\Quotes\Support\QuoteTemplates;
use FI\Traits\ReturnUrl;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CompanyProfileController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        return view('company_profiles.index')
            ->with('companyProfiles', CompanyProfile::orderBy('company')->paginate(config('fi.resultsPerPage')));
    }

    public function create()
    {
        return view('company_profiles.form')
            ->with('editMode', false)
            ->with('invoiceTemplates', InvoiceTemplates::lists())
            ->with('quoteTemplates', QuoteTemplates::lists())
            ->with('countries', Country::getAll())
            ->with('customFields', CustomFieldsParser::getFields('company_profiles'));
    }

    public function store(CompanyProfileStoreRequest $request)
    {
        $input = $request->except('custom');

        if ($request->hasFile('logo'))
        {
            $logoFileName = $request->file('logo')->getClientOriginalName();
            $request->file('logo')->move(company_profile_logo_path(), $logoFileName);
            if (file_exists(company_profile_logo_path() . DIRECTORY_SEPARATOR . $logoFileName))
            {
                ini_set('memory_limit', '256M');
                $resizedLogo = Image::make(company_profile_logo_path() . DIRECTORY_SEPARATOR . $logoFileName)->resize(1000, 1000, function ($constraint)
                {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $resizedLogo->save(company_profile_logo_path() . DIRECTORY_SEPARATOR . $logoFileName);
            }
            $input['logo'] = $logoFileName;
        }

        $companyProfile = CompanyProfile::create($input);

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform($request->get('custom', []), 'company_profiles', $companyProfile);
        $companyProfile->custom->update($customFieldData);

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_created'));
    }

    public function edit($id)
    {
        $companyProfile = CompanyProfile::find($id);

        return view('company_profiles.form')
            ->with('editMode', true)
            ->with('companyProfile', $companyProfile)
            ->with('companyProfileInUse', CompanyProfile::inUse($id))
            ->with('invoiceTemplates', InvoiceTemplates::lists())
            ->with('quoteTemplates', QuoteTemplates::lists())
            ->with('countries', Country::getAll())
            ->with('customFields', CustomFieldsParser::getFields('company_profiles'));
    }

    public function update(CompanyProfileUpdateRequest $request, $id)
    {
        $input = $request->except('custom');

        if ($request->hasFile('logo'))
        {
            $logoFileName = $request->file('logo')->getClientOriginalName();
            $request->file('logo')->move(company_profile_logo_path(), $logoFileName);
            if (file_exists(company_profile_logo_path() . DIRECTORY_SEPARATOR . $logoFileName))
            {
                ini_set('memory_limit', '256M');
                $resizedLogo = Image::make(company_profile_logo_path() . DIRECTORY_SEPARATOR . $logoFileName)->resize(1000, 1000, function ($constraint)
                {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $resizedLogo->save(company_profile_logo_path() . DIRECTORY_SEPARATOR . $logoFileName);
            }
            $input['logo'] = $logoFileName;
        }

        $companyProfile = CompanyProfile::find($id);
        $companyProfile->fill($input);
        $companyProfile->save();

        // Save the custom fields.
        if ($companyProfile->custom)
        {
            $customFieldData = CustomFieldsTransformer::transform($request->get('custom', []), 'company_profiles', $companyProfile);
            $companyProfile->custom->update($customFieldData);
        }

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

    public function delete($id)
    {
        if (CompanyProfile::inUse($id))
        {
            $alert = trans('fi.cannot_delete_record_in_use');
        }
        else
        {
            if (CompanyProfile::whereId($id)->whereIsDefault(0)->first())
            {
                CompanyProfile::destroy($id);
                $alert = trans('fi.record_successfully_deleted');
            }
            else
            {
                $alert = trans('fi.cannot_delete_default_company');
            }

        }

        return redirect()->route('companyProfiles.index')
            ->with('alert', $alert);
    }

    public function ajaxModalLookup()
    {
        return view('company_profiles._modal_lookup')
            ->with('id', request('id'))
            ->with('companyProfiles', CompanyProfile::getList())
            ->with('refreshFromRoute', request('refresh_from_route'))
            ->with('updateCompanyProfileRoute', request('update_company_profile_route'));
    }

    public function deleteLogo($id)
    {
        $companyProfile = CompanyProfile::find($id);

        $companyProfile->logo = null;

        $companyProfile->save();

        if (file_exists(company_profile_logo_path($companyProfile->logo)))
        {
            try
            {
                unlink(company_profile_logo_path($companyProfile->logo));
            }
            catch (Exception $e)
            {

            }
        }
    }

    public function deleteImage($id, $columnName)
    {
        $customFields = CompanyProfileCustom::whereCompanyProfileId($id)->first();

        $existingFile = 'company_profiles' . DIRECTORY_SEPARATOR . $customFields->{$columnName};
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
}
