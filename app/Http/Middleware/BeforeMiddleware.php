<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Http\Middleware;

use Closure;
use Exception;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\Currencies\Models\Currency;
use FI\Modules\CustomFields\Models\CustomField;
use FI\Modules\Settings\Models\Setting;
use FI\Modules\Settings\Models\UserSetting;
use FI\Support\DateFormatter;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class BeforeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('app.debug'))
        {
            DB::enableQueryLog();
        }

        // Set the application specific settings under fi. prefix (fi.settingName)
        if (Setting::setAll() && UserSetting::setAll(auth()->user()))
        {
            if (config('fi.forceHttps') and !$request->secure())
            {
                return redirect()->secure($request->getRequestUri());
            }

            // This one needs a little special attention
            $dateFormats = DateFormatter::formats();
            config(['fi.datepickerFormat' => $dateFormats[config('fi.dateFormat')]['datepicker']]);

            // Set the environment timezone to the application specific timezone, if available, otherwise UTC
            date_default_timezone_set((config('fi.timezone') ?: config('app.timezone')));

            $mailPassword = '';

            try
            {
                $mailPassword = (config('fi.mailPassword')) ? Crypt::decrypt(config('fi.mailPassword')) : '';
            }
            catch (Exception $e)
            {
                if (config('fi.mailDriver') == 'smtp')
                {
                    session()->flash('error', trans('fi.error') . ' - ' . trans('fi.mail_hash_error'));
                }
            }

            // Override the framework mail configuration with the values provided by the application
            config(['mail.driver' => (config('fi.mailDriver')) ? config('fi.mailDriver') : 'smtp']);
            config(['mail.host' => config('fi.mailHost')]);
            config(['mail.port' => config('fi.mailPort')]);
            config(['mail.encryption' => config('fi.mailEncryption')]);
            config(['mail.username' => config('fi.mailUsername')]);
            config(['mail.password' => $mailPassword]);
            config(['mail.sendmail' => config('fi.mailSendmail')]);

            if ('sendgrid' == config('fi.mailDriver'))
            {
                config([
                    'services.sendgrid.api_key' => config('fi.mailSendgridKey'),
                ]);
            }

            if (config('fi.mailAllowSelfSignedCertificate'))
            {
                config([
                    'mail.stream.ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                    ],
                ]);
            }

            // Force the mailer to use these settings
            (new MailServiceProvider(app()))->register();

            // Set the base currency to a config value
            config(['fi.currency' => Currency::where('code', config('fi.baseCurrency'))->first()]);

            config(['fi.customFields' => CustomField::get()]);
            if (!config('fi.defaultCompanyProfile'))
            {
                $companyProfile = CompanyProfile::query()->first();
                if($companyProfile){
                    Setting::saveByKey('defaultCompanyProfile', $companyProfile->id);
                    request()->session()->flash('alertInfo', trans('fi.default_company_profile_set'));
                }
            }
        }

        config(['fi.clientCenterRequest' => (($request->segment(1) == 'client_center') ? true : false)]);

        if (!config('fi.clientCenterRequest'))
        {
            app()->setLocale((config('fi.language')) ?: 'en');
        }

        config(['fi.mailConfigured' => (config('fi.mailDriver') ? true : false)]);

        config(['fi.merchant' => json_decode(config('fi.merchant'), true)]);

        config(['filesystems.disks.custom_field_upload.driver' => 'local']);
        config(['filesystems.disks.custom_field_upload.root' => media_path('custom_fields')]);

        return $next($request);
    }
}
