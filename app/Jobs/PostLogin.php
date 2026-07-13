<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Jobs;

use Carbon\Carbon;
use Cookie;
use FI\Modules\Settings\Models\Setting;
use FI\Modules\Users\Models\User;
use FI\Support\DateFormatter;
use FI\Support\UpdateChecker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Session;

class PostLogin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->fiWritableFoldersTest();

        if ($this->user->user_type == 'admin')
        {
            $this->fiVersionCheck();
            $this->fiAgreementCheck();
        }

        try
        {
            $this->user->update(['last_login_at' => Carbon::now()]);
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function fiWritableFoldersTest()
    {
        $error         = [];
        $success       = [];
        $baseStorage   = storage_path();
        $baseMedia     = media_path();
        $baseBootStrap = base_path('bootstrap');

        $aFolders = [$baseStorage,
            $baseStorage . DIRECTORY_SEPARATOR . 'app',
            $baseStorage . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public',
            $baseStorage . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'email_templates',
            $baseStorage . DIRECTORY_SEPARATOR . 'framework',
            $baseStorage . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache',
            $baseStorage . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'data',
            $baseStorage . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions',
            $baseStorage . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views',
            $baseStorage . DIRECTORY_SEPARATOR . 'logs',
            $baseMedia,
            $baseMedia . DIRECTORY_SEPARATOR . 'company_profile',
            $baseBootStrap . DIRECTORY_SEPARATOR . 'cache'];

        foreach ($aFolders as $reqPath)
        {

            if (!is_dir($reqPath))
            {
                if (@mkdir($reqPath, 0777, true))
                {
                    Log::info('Created missing required folder: ' . $reqPath);
                    $success[] = trans('fi.create_missing_folder_success');
                }
                else
                {
                    Log::info('*ERROR* Attempt to create writable folder failed: ' . $reqPath);
                    $error['create'][] = $reqPath;
                }
            }

            if (!is_writable($reqPath))
            {
                $error['permission'][] = $reqPath;
                Log::info('*ERROR* Folder is not writable: ' . $reqPath);
            }
        }

        if (!empty($error))
        {
            Session::flash('errorFolderCreate', collect($error));
        }

        if (!empty($success))
        {
            Session::flash('successFolderCreate', collect($success));
        }

        $emailTemplateDirectory = $baseStorage . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'email_templates';

        if (is_dir($emailTemplateDirectory) && is_writable($emailTemplateDirectory) && count(scandir($emailTemplateDirectory)) <= 3)
        {
            Setting::writeEmailTemplates();
        }

    }

    public function fiVersionCheck()
    {
        // Weekly basis we have to auto check version upgrade
        try
        {
            $lastVersionCheckDate = Cookie::get('versionCheckDate');
            $versionCheck         = Cookie::get('versionCheck');

            if ($lastVersionCheckDate == null && ($versionCheck == null || $versionCheck == 1))
            {
                $this->checkUpdates();
            }
            else
            {
                $lastVersionCheckDays = Carbon::parse($lastVersionCheckDate)->diffInDays(Carbon::now());
                if ($lastVersionCheckDays >= 7 && ($versionCheck == null || $versionCheck == 1))
                {
                    $this->checkUpdates();
                }
            }
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function fiAgreementCheck()
    {
        try
        {
            $agreementCheckDate = Cookie::get('agreementCheckDate');
            $agreementCheck     = Cookie::get('agreementCheck');

            if ($agreementCheckDate == null && ($agreementCheck == null || $agreementCheck == 1))
            {
                $this->checkFiAgreementDate();
            }
            else
            {
                $agreementCheckDate = Carbon::parse($agreementCheckDate)->diffInDays(Carbon::now());
                if ($agreementCheckDate >= 7 && ($agreementCheck == null || $agreementCheck == 1))
                {
                    $this->checkFiAgreementDate();
                }
            }
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function checkUpdates()
    {
        try
        {
            $updateChecker = new UpdateChecker;

            $updateChecker->checkVersion('auto');
            $updateAvailable = $updateChecker->updateAvailable();
            $currentVersion  = $updateChecker->getCurrentVersion();

            if ($updateAvailable)
            {
                session(['versionAlert' => trans('fi.update_available', ['version' => $currentVersion])]);
            }
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function checkFiAgreementDate()
    {
        try
        {
            $updateChecker = new UpdateChecker;

            $updateChecker->checkAgreementDate('auto');
            $date = $updateChecker->getAgreementExpireDate();

            if ($date == null)
            {
                session(['piracyAlert' => trans('fi.piracy_message')]);
            }
            else
            {
                $agreementExpireDays = explode(' ', Carbon::parse($date)->diffForHumans(Carbon::now()));

                if ($agreementExpireDays[0] >= 1 && $agreementExpireDays[0] <= 30 && $agreementExpireDays[2] == 'after' && $agreementExpireDays[1] != 'month' && $agreementExpireDays[1] != 'months' && $agreementExpireDays[1] != 'year' && $agreementExpireDays[1] != 'years')
                {
                    session(['agreementExpireAlert' => trans('fi.agreement_expire', ['date' => DateFormatter::format($date)])]);
                }
                elseif ($agreementExpireDays[2] == 'before')
                {
                    session(['agreementExpiredAlert' => trans('fi.agreement_expired', ['date' => DateFormatter::format($date)])]);
                }
            }
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }
}
