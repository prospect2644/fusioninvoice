<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Settings\Controllers;

use Exception;
use FI\Http\Controllers\Controller;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\Currencies\Models\Currency;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\Invoices\Support\InvoiceTemplates;
use FI\Modules\MailQueue\Support\MailQueue;
use FI\Modules\MailQueue\Support\MailSettings;
use FI\Modules\Merchant\Support\MerchantFactory;
use FI\Modules\PaymentMethods\Models\PaymentMethod;
use FI\Modules\Quotes\Support\QuoteTemplates;
use FI\Modules\Settings\Models\Setting;
use FI\Modules\Settings\Requests\SettingUpdateRequest;
use FI\Modules\TaxRates\Models\TaxRate;
use FI\Modules\Users\Models\User;
use FI\Support\DashboardWidgets;
use FI\Support\DateFormatter;
use FI\Support\Languages;
use FI\Support\PDF\PDFFactory;
use FI\Support\Skins;
use FI\Support\Statuses\InvoiceStatuses;
use FI\Support\Statuses\QuoteStatuses;
use FI\Support\UpdateChecker;
use Illuminate\Support\Facades\Crypt;
use Session;

class SettingController extends Controller
{

    private $mailQueue;

    public function __construct(MailQueue $mailQueue)
    {
        $this->mailQueue = $mailQueue;
    }

    public function index()
    {
        try
        {
            $mailPassword = Crypt::decrypt(config('fi.mailPassword'));
            session()->forget('error');
        }
        catch (Exception $e)
        {
            $mailPassword = '';
        }

        return view('settings.index')
            ->with([
                'languages'                   => Languages::listLanguages(),
                'dateFormats'                 => DateFormatter::dropdownArray(),
                'invoiceTemplates'            => InvoiceTemplates::lists(),
                'quoteTemplates'              => QuoteTemplates::lists(),
                'documentNumberSchemes'       => DocumentNumberScheme::getList(),
                'taxRates'                    => TaxRate::getList(),
                'paymentMethods'              => PaymentMethod::getList(),
                'emailSendMethods'            => MailSettings::listSendMethods(),
                'emailEncryptions'            => MailSettings::listEncryptions(),
                'yesNoArray'                  => ['0' => trans('fi.no'), '1' => trans('fi.yes')],
                'timezones'                   => array_combine(timezone_identifiers_list(), timezone_identifiers_list()),
                'paperSizes'                  => ['letter' => trans('fi.letter'), 'A4' => trans('fi.a4'), 'legal' => trans('fi.legal')],
                'paperOrientations'           => ['portrait' => trans('fi.portrait'), 'landscape' => trans('fi.landscape')],
                'currencies'                  => Currency::getList(),
                'exchangeRateModes'           => ['automatic' => trans('fi.automatic'), 'manual' => trans('fi.manual')],
                'pdfDrivers'                  => PDFFactory::getDrivers(),
                'convertQuoteOptions'         => ['quote' => trans('fi.convert_quote_option1'), 'invoice' => trans('fi.convert_quote_option2')],
                'clientUniqueNameOptions'     => ['0' => trans('fi.client_unique_name_option_1'), '1' => trans('fi.client_unique_name_option_2')],
                'dashboardWidgets'            => DashboardWidgets::listsByOrder(),
                'colWidthArray'               => array_combine(range(1, 12), range(1, 12)),
                'displayOrderArray'           => array_combine(range(1, 24), range(1, 24)),
                'merchant'                    => config('fi.merchant'),
                'skins'                       => Skins::lists(),
                'resultsPerPage'              => array_combine(range(15, 100, 5), range(15, 100, 5)),
                'amountDecimalOptions'        => ['0' => '0', '2' => '2', '3' => '3', '4' => '4'],
                'roundTaxDecimalOptions'      => ['2' => '2', '3' => '3', '4' => '4'],
                'companyProfiles'             => CompanyProfile::getList(),
                'merchantDrivers'             => MerchantFactory::getDrivers(),
                'invoiceStatuses'             => ['all' => trans('fi.all_statuses')] + InvoiceStatuses::lists() + ['overdue' => trans('fi.overdue')],
                'quoteStatuses'               => ['all' => trans('fi.all_statuses')] + QuoteStatuses::lists(),
                'invoiceWhenDraftOptions'     => [0 => trans('fi.keep_invoice_date_as_is'), 1 => trans('fi.change_invoice_date_to_todays_date')],
                'quoteWhenDraftOptions'       => [0 => trans('fi.keep_quote_date_as_is'), 1 => trans('fi.change_quote_date_to_todays_date')],
                'taskResultsPerPage'          => array_combine(range(5, 20), range(5, 20)),
                'mailPassword'                => $mailPassword,
                'customFieldColWidthArray'    => ['12' => '1', '6' => '2', '4' => '3'],
                'settings'                    => Setting::getSettingKeyValuePair(),
                'dashboardWidgetsDateOptions' => periods(),
                'numberOfTaxFieldsArray'      => [1 => trans('fi.tax_1_entry'), 2 => trans('fi.tax_2_entries')],
            ]);
    }

    public function update(SettingUpdateRequest $request)
    {
        $settings                                       = $request->input('setting');
        $settings['overdueAttachInvoice']               = $settings['overdueAttachInvoice'] ?? 0;
        $settings['upcomingPaymentNoticeAttachInvoice'] = $settings['upcomingPaymentNoticeAttachInvoice'] ?? 0;
        $settings['paymentAttachInvoice']               = $settings['paymentAttachInvoice'] ?? 0;
        foreach ($settings as $key => $value)
        {
            $skipSave = false;

            if ($key == 'mailPassword' and $value)
            {
                $value = Crypt::encrypt($value);
            }

            if ($key == 'merchant')
            {
                $value = json_encode($value);
            }

            if ($key == 'mailFromAddress' && $value)
            {
                $userId = User::whereUserType('system')->first()->id;
                $user   = User::find($userId);

                if ($user && $user->email !== $value)
                {
                    $user->email = $value;
                    $user->save();
                }
            }

            if (!$skipSave)
            {
                Setting::saveByKey($key, $value);
            }
        }

        Setting::writeEmailTemplates();
        session()->forget('error');

        return redirect()->route('settings.index')
            ->with('alertSuccess', trans('fi.settings_successfully_saved'));
    }

    public function updateCheck()
    {
        $updateChecker = new UpdateChecker;

        $updateChecker->checkVersion('manual');
        $updateAvailable = $updateChecker->updateAvailable();
        $currentVersion  = $updateChecker->getCurrentVersion();

        if ($updateAvailable)
        {
            $message = trans('fi.update_available', ['version' => $currentVersion]);
        }
        else
        {
            $message = trans('fi.update_not_available');
        }

        return response()->json(
            [
                'success' => true,
                'message' => $message,
            ], 200
        );
    }

    public function saveTab()
    {
        session(['settingTabId' => request('settingTabId')]);
    }

    public function generatePassportKey()
    {
        exec('php artisan passport:install');
        exec('php artisan passport:keys --force');
    }

    public function pdfCleanup()
    {
        $files = glob(storage_path('app/public/') . "*.pdf");
        foreach ($files as $file)
        {
            if (is_file($file))
            {
                if (time() - filemtime($file) >= 60 * 60 * 24 * 7)
                {
                    unlink($file);
                }
            }
        }

        return response()->json(
            [
                'success' => true,
                'message' => trans('fi.pdf_cleanup_success'),
            ], 200
        );
    }
}
