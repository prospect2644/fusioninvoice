<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        view()->composer('layouts.master', 'FI\Composers\LayoutComposer');
        view()->composer(['client_center.layouts.master', 'client_center.layouts.public', 'layouts.master', 'setup.master'], 'FI\Composers\SkinComposer');
        view()->composer(['clients._form', 'clients._settings'], 'FI\Composers\ClientFormComposer');
        view()->composer('reports.options.*', 'FI\Composers\ReportComposer');
        view()->composer('layouts.master', 'FI\Composers\MruComposer');
        view()->composer('layouts._datetimepicker', 'FI\Composers\DateTimePickerComposer');
    }

    public function register()
    {
        //
    }
}
