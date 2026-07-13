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
use Illuminate\Support\Facades\File;
use Illuminate\Database\Migrations\Migration;

class TransferStorageDirectory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mediaDirectory          = config('filesystems.media.root');
        $companyProfileDirectory = config('filesystems.media.root') . DIRECTORY_SEPARATOR . 'company_profile';
        $customFieldsMediaPath   = config('filesystems.media.root') . DIRECTORY_SEPARATOR . 'custom_fields';
        $customFieldsStoragePath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'custom_fields');
        if (!File::exists($mediaDirectory))
        {
            File::makeDirectory($mediaDirectory, $mode = 0777, true, true);
        }
        if (!File::exists($companyProfileDirectory))
        {
            File::makeDirectory($companyProfileDirectory, $mode = 0777, true, true);
        }
        if (File::exists($customFieldsStoragePath))
        {
            $customFieldFiles = File::allFiles($customFieldsStoragePath);
            foreach ($customFieldFiles as $customFieldFile)
            {
                if (!File::isDirectory($customFieldsMediaPath . DIRECTORY_SEPARATOR . $customFieldFile->getRelativePath()))
                {
                    File::makeDirectory($customFieldsMediaPath . DIRECTORY_SEPARATOR . $customFieldFile->getRelativePath(), 0777, true, true);
                }
                File::move($customFieldFile->getPathName(), $customFieldsMediaPath . DIRECTORY_SEPARATOR . $customFieldFile->getRelativePathname());
            }
        }

        $companyProfiles = CompanyProfile::all();
        foreach ($companyProfiles as $companyProfile)
        {
            if ($companyProfile->logo and file_exists(storage_path($companyProfile->logo)))
            {
                File::move(storage_path($companyProfile->logo), $companyProfileDirectory . DIRECTORY_SEPARATOR . $companyProfile->logo);
            }
        }
    }
}
