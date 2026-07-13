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
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ResizeProfileLogo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ini_set('memory_limit', '256M');
        $companyProfileDirectory = config('filesystems.media.root') . DIRECTORY_SEPARATOR . 'company_profile';
        if (File::exists($companyProfileDirectory))
        {
            $logoFiles = File::allFiles($companyProfileDirectory);
            foreach ($logoFiles as $logoFile)
            {
                $resizedLogo = Image::make($logoFile->getPathName())->resize(1000, 1000, function ($constraint)
                {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $resizedLogo->save($logoFile->getPathName());
            }
        }
    }
}
