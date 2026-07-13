<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Widgets\Dashboard\ClientTimeline\Providers;

use Illuminate\Support\ServiceProvider;

class WidgetServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register the view path.
        view()->addLocation(app_path('Widgets/Dashboard/ClientTimeLine/Views'));

        // Register the widget view composer.
        view()->composer('ClientTimeLineWidget', 'FI\Widgets\Dashboard\ClientTimeLine\Composers\ClientTimeLineWidgetComposer');
    }

    public function register()
    {
        //
    }
}