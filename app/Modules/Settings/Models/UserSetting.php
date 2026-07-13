<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Settings\Models;

use FI\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use PDOException;

class UserSetting extends Model
{
    protected $table = 'user_settings';

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public static function allByUser($user)
    {
        return self::where('user_id', $user->id)->get();
    }

    public static function deleteByKey($key, $user)
    {
        self::where('setting_key', $key)->where('user_id', $user->id)->delete();
    }

    public static function getByKey($key, $user)
    {
        $setting = self::where('setting_key', $key)->where('user_id', $user->id)->first();

        if ($setting)
        {
            return $setting->setting_value;
        }

        return null;
    }

    public static function saveByKey($key, $value, $user)
    {
        $setting = self::firstOrNew(['setting_key' => $key, 'user_id' => $user->id]);

        $setting->setting_value = $value;

        config(['fi.' . $key => $value]);

        $setting->save();

        return $setting;
    }

    public static function setAll($user = null)
    {
        if (!$user)
        {
            return true;
        }

        try
        {
            $settings = self::allByUser($user);

            foreach ($settings as $setting)
            {
                config(['fi.' . $setting->setting_key => $setting->setting_value]);
            }

            return true;
        }
        catch (QueryException $e)
        {
            return false;
        }
        catch (PDOException $e)
        {
            return false;
        }
    }

    public static function writeEmailTemplates($user)
    {
        $emailTemplates = [
            'invoiceEmailBody',
            'quoteEmailBody',
            'overdueInvoiceEmailBody',
            'upcomingPaymentNoticeEmailBody',
            'quoteApprovedEmailBody',
            'quoteRejectedEmailBody',
            'paymentReceiptBody',
            'quoteEmailSubject',
            'invoiceEmailSubject',
            'overdueInvoiceEmailSubject',
            'upcomingPaymentNoticeEmailSubject',
            'paymentReceiptEmailSubject',
        ];

        foreach ($emailTemplates as $template)
        {
            $templateContents = self::getByKey($template, $user);
            $templateContents = str_replace('{{', '{!!', $templateContents);
            $templateContents = str_replace('}}', '!!}', $templateContents);

            Storage::put('email_templates/' . $template . '.blade.php', $templateContents);
        }
    }

    public function mailQueue()
    {
        return $this->morphMany('FI\Modules\MailQueue\Models\MailQueue', 'mailable');
    }
}