<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Support;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\Log;

class DateFormatter
{
    /**
     * Returns an array of date format options.
     *
     * @return array
     */
    static function formats()
    {
        return [
            'm/d/Y' => [
                'setting'        => 'm/d/Y',
                'datepicker'     => 'mm/dd/yyyy',
                'mysql'          => '%m/%d/%Y',
                'datetimepicker' => 'MM/DD/YYYY',
            ],
            'm-d-Y' => [
                'setting'        => 'm-d-Y',
                'datepicker'     => 'mm-dd-yyyy',
                'mysql'          => '%m-%d-%Y',
                'datetimepicker' => 'MM-DD-YYYY',
            ],
            'm.d.Y' => [
                'setting'        => 'm.d.Y',
                'datepicker'     => 'mm.dd.yyyy',
                'mysql'          => '%m.%d.%Y',
                'datetimepicker' => 'MM.DD.YYYY',
            ],
            'Y/m/d' => [
                'setting'        => 'Y/m/d',
                'datepicker'     => 'yyyy/mm/dd',
                'mysql'          => '%Y/%m/%d',
                'datetimepicker' => 'YYYY/MM/DD',
            ],
            'Y-m-d' => [
                'setting'        => 'Y-m-d',
                'datepicker'     => 'yyyy-mm-dd',
                'mysql'          => '%Y-%m-%d',
                'datetimepicker' => 'YYYY-MM-DD',
            ],
            'Y.m.d' => [
                'setting'        => 'Y.m.d',
                'datepicker'     => 'yyyy.mm.dd',
                'mysql'          => '%Y.%m.%d',
                'datetimepicker' => 'YYYY.MM.DD',
            ],
            'd/m/Y' => [
                'setting'        => 'd/m/Y',
                'datepicker'     => 'dd/mm/yyyy',
                'mysql'          => '%d/%m/%Y',
                'datetimepicker' => 'DD/MM/YYYY',
            ],
            'd-m-Y' => [
                'setting'        => 'd-m-Y',
                'datepicker'     => 'dd-mm-yyyy',
                'mysql'          => '%d-%m-%Y',
                'datetimepicker' => 'DD-MM-YYYY',
            ],
            'd.m.Y' => [
                'setting'        => 'd.m.Y',
                'datepicker'     => 'dd.mm.yyyy',
                'mysql'          => '%d.%m.%Y',
                'datetimepicker' => 'DD.MM.YYYY',
            ],
        ];
    }

    /**
     * Returns a flattened version of the format() method array to display as dropdown options.
     *
     * @return array
     */
    public static function dropdownArray()
    {
        $formats = self::formats();

        $return = [];

        foreach ($formats as $format)
        {
            $return[$format['setting']] = $format['setting'];
        }

        return $return;
    }

    /**
     * Converts a stored date to the user formatted date.
     *
     * @param string $date The yyyy-mm-dd standardized date
     * @param bool $includeTime Whether or not to include the time
     * @return string             The user formatted date
     */
    public static function format($date = null, $includeTime = false, $timezone = null)
    {
        try
        {
            $date = new DateTime($date, new DateTimeZone(($timezone) ?? config('fi.timezone')));

            if (!$includeTime)
            {
                return $date->format(config('fi.dateFormat'));
            }

            return $date->format(config('fi.dateFormat') . (!config('fi.use24HourTimeFormat') ? ' g:i A' : ' H:i'));
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }
    }

    /**
     * Converts a stored date to the user formatted date.
     *
     * @param string $date The yyyy-mm-dd standardized date
     * @param bool $includeTime Whether or not to include the time
     * @return string             The user formatted date
     */
    public static function formatTimeAgo($date = null, $includeTime = false, $timezone = null)
    {
        try
        {
            $date = Carbon::parse($date, ($timezone) ?? config('fi.timezone'));

            if (!$includeTime)
            {
                if ($date->isToday())
                {
                    return trans('fi.today');
                }
                elseif ($date->isYesterday())
                {
                    return trans('fi.yesterday');
                }
                elseif ($date->isTomorrow())
                {
                    return trans('fi.tomorrow');
                }

                return $date->format(config('fi.dateFormat'));
            }

            if ($date->isToday())
            {
                return trans('fi.today') . ' ' . $date->format((!config('fi.use24HourTimeFormat') ? ' g:i A' : ' H:i'));
            }
            elseif ($date->isYesterday())
            {
                return trans('fi.yesterday') . ' ' . $date->format((!config('fi.use24HourTimeFormat') ? ' g:i A' : ' H:i'));
            }
            elseif ($date->isTomorrow())
            {
                return trans('fi.tomorrow') . ' ' . $date->format((!config('fi.use24HourTimeFormat') ? ' g:i A' : ' H:i'));
            }

            return $date->format(config('fi.dateFormat') . (!config('fi.use24HourTimeFormat') ? ' g:i A' : ' H:i'));
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }
    }

    public static function extractTime($date, $timezone = null)
    {
        $date = Carbon::parse($date, ($timezone) ?? config('fi.timezone'));
        return $date->format(config('fi.use24HourTimeFormat') ? ' H:i' : ' g:i A');
    }

    /**
     * Converts a user submitted date back to standard yyyy-mm-dd format.
     *
     * @param string $userDate The user submitted date
     * @return string             The yyyy-mm-dd standardized date
     */
    public static function unformat($userDate = null)
    {
        if ($userDate)
        {
            $date = DateTime::createFromFormat(config('fi.dateFormat'), $userDate);

            return $date->format('Y-m-d');
        }

        return null;
    }

    /**
     * @param null $userDateTime
     * @return null|string
     */
    public static function unFormatDateTime($userDateTime = null)
    {
        if ($userDateTime)
        {
            $timeFormat = config('fi.use24HourTimeFormat') ? ' H:i' : ' g:i A';
            $dateTime   = DateTime::createFromFormat(config('fi.dateFormat') . $timeFormat, $userDateTime);

            return $dateTime->format('Y-m-d H:i:s');
        }

        return null;
    }

    /**
     * Adds a specified number of days to a yyyy-mm-dd formatted date.
     *
     * @param string $date The date
     * @param int $numDays The number of days to increment
     * @return string The yyyy-mm-dd standardized incremented date
     */
    public static function incrementDateByDays($date, $numDays)
    {
        $date = DateTime::createFromFormat('Y-m-d', $date);

        try
        {
            $date->add(new DateInterval('P' . $numDays . 'D'));

            return $date->format('Y-m-d');
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }
    }

    /**
     * Adds a specified number of periods to a yyyy-mm-dd formatted date.
     *
     * @param string $date The date
     * @param int $period 1 = Days, 2 = Weeks, 3 = Months, 4 = Years
     * @param int $numPeriods The number of periods to increment
     * @return string The yyyy-mm-dd standardized incremented date
     */
    public static function incrementDate($date, $period, $numPeriods)
    {
        $date = DateTime::createFromFormat('Y-m-d', $date);

        try
        {
            switch ($period)
            {
                case 1:
                    $date->add(new DateInterval('P' . $numPeriods . 'D'));
                    break;
                case 2:
                    $date->add(new DateInterval('P' . $numPeriods . 'W'));
                    break;
                case 3:
                    $date       = $date->format('Y-m-d');
                    $actualDate = Carbon::createFromFormat('Y-m-d', $date);
                    //New logic for add month to consider leap year and end of the month scenario
                    if ($actualDate->endOfMonth()->format('Y-m-d') == $date)
                    {
                        if ($actualDate->day > 28 && $actualDate->month == 1)
                        {
                            $date = Carbon::createFromFormat('Y-m-d', $date)->addMonthNoOverflow($numPeriods);
                        }
                        else
                        {
                            $date = Carbon::createFromFormat('Y-m-d', $date)->addMonthNoOverflow($numPeriods)->endOfMonth();
                        }
                    }
                    else
                    {
                        if ($actualDate->day > 28 && $actualDate->month == 1)
                        {
                            $date = Carbon::createFromFormat('Y-m-d', $date)->addMonthNoOverflow($numPeriods);
                        }
                        else
                        {
                            $date = Carbon::createFromFormat('Y-m-d', $date)->addMonths($numPeriods);
                        }
                    }
                    break;
                case 4:
                    $date->add(new DateInterval('P' . $numPeriods . 'Y'));
                    break;
            }

            return $date->format('Y-m-d');
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }
    }

    /**
     * Returns the short name of the month from a numeric representation.
     *
     * @param int $monthNumber
     * @return string
     */
    public static function getMonthShortName($monthNumber)
    {
        return date('M', mktime(0, 0, 0, $monthNumber, 1, date('Y')));
    }

    /**
     * Returns the format required to initialize the datepicker.
     *
     * @return string
     */
    public static function getDatepickerFormat()
    {
        $formats = self::formats();

        return $formats[config('fi.dateFormat')]['datepicker'];
    }

    /**
     * Returns the format required to initialize the datetimepicker.
     *
     * @return string
     */
    public static function getDateTimePickerFormat()
    {
        $formats = self::formats();

        return $formats[config('fi.dateFormat')]['datetimepicker'] . (!config('fi.use24HourTimeFormat') ? ' hh:mm:ss' : ' HH:mm:ss');
    }

    /**
     * Returns the format required to initialize the datepicker.
     *
     * @return string
     */
    public static function getMySqlDateFormat()
    {
        $formats = self::formats();

        return $formats[config('fi.dateFormat')]['mysql'];
    }

    /**
     * Returns the format required to initialize the datetimepicker.
     *
     * @return string
     */
    public static function getMySqlDateTimeFormat()
    {
        $formats = self::formats();

        return $formats[config('fi.dateFormat')]['mysql'] . (!config('fi.use24HourTimeFormat') ? ' %h:%i:%s' : ' %H:%i:%s');
    }
}
