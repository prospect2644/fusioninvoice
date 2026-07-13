<header class="main-header">
    @include('layouts._js_notifications')
    <a href="{{ route('dashboard.index')}}" class="logo">
        <span class="logo-lg">{{ config('fi.headerTitleText') }}</span>
    </a>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>

        <div class="navbar-custom-menu ui-content" data-role="collapsible">

            <ul class="nav navbar-nav">

                <li>
                    <a href="https://www.fusioninvoice.com/docs" title="{{ trans('fi.documentation') }}" target="_blank">
                        <i class="fa fa-question-circle"></i>
                    </a>
                </li>
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                        <i class="fa fa-bell-o"></i>
                        @if(count($notifications))
                            <span class="label label-warning">{{count($notifications)}}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu main-menu">
                        @if(count($notifications))
                            <li class="header">{{trans('fi.total_notifications',['total' => count($notifications)])}}</li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu">
                                    @foreach($notifications as $notification)
                                        <li>
                                            <a href="{{ route('task.show',$notification->notifiable_id) }}"
                                               class="notification-item"
                                               data-url="{{$notification->notification_detail['url']}}"
                                               data-notification-id="{{$notification->id}}">
                                                <i class="fa {{$notification->notification_detail['icon']}} text-yellow"></i>
                                                {{$notification->notification_detail['title']}}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="header text-center">{{trans('fi.no_notifications')}}</li>
                        @endif
                    </ul>
                </li>
                @if (in_array(auth()->user()->user_type, ['admin']))

                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ trans('fi.system') }}">
                            <i class="fa fa-cog"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdown2" style="min-width:210px;">

                            <li class="dropdown-item dropdown">
                                <a class="dropdown-toggle" id="dropdown2-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                    {{ trans('fi.configuration') }}
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                                <ul class="dropdown-menu main-menu" aria-labelledby="dropdown2-1">
                                    <li><a href="{{ route('settings.index') }}">{{ trans('fi.system_settings') }}</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{{ route('companyProfiles.index') }}">{{ trans('fi.company_profiles') }}</a></li>
                                    <li><a href="{{ route('documentNumberSchemes.index') }}">{{ trans('fi.document_number_schemes')}}</a></li>
                                    <li><a href="{{ route('paymentMethods.index') }}">{{ trans('fi.payment_methods') }}</a></li>
                                    <li><a href="{{ route('taxRates.index') }}">{{ trans('fi.tax_rates') }}</a></li>
                                    <li class="divider"></li>        
                                    <li><a href="{{ route('itemLookups.index') }}">{{ trans('fi.item_lookups') }}</a></li>
                                    <li><a href="{{ route('item.categories.index') }}">{{ trans('fi.item_categories') }}</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{{ route('expenses.vendors.index') }}">{{ trans('fi.expense_vendors') }}</a></li>
                                    <li><a href="{{ route('expenses.categories.index') }}">{{ trans('fi.expense_categories') }}</a></li>
                                    <li class="divider"></li>
                                    <li><a href="{{ route('currencies.index') }}">{{ trans('fi.currencies') }}</a></li>
                                </ul>
                            </li>

                            <li class="divider"></li>
                            <li><a href="{{ route('users.index') }}">{{ trans('fi.user_accounts') }}</a></li>
                            <li class="divider"></li>
                            <li class="dropdown-item dropdown">
                                <a class="dropdown-toggle" id="dropdown3-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                    {{ trans('fi.customizations') }}
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                                <ul class="dropdown-menu main-menu" aria-labelledby="dropdown3-1">
                                    <li><a href="{{ route('customFields.index') }}">{{ trans('fi.custom_fields') }}</a></li>
                                    <li><a href="{{ route('addons.index') }}">{{ trans('fi.addons') }}</a></li>
                                </ul>
                            </li>

                            <li class="divider"></li>

                            <li class="dropdown-item dropdown">
                                <a class="dropdown-toggle" id="dropdown4-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                    {{ trans('fi.utilities_and_logs') }}
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                                <ul class="dropdown-menu main-menu" aria-labelledby="dropdown4-1">
                                    <li><a href="{{ route('import.index') }}">{{ trans('fi.import_data') }}</a></li>
                                    <li><a href="{{ route('export.index') }}">{{ trans('fi.export_data') }}</a></li>
                                    <li><a href="{{ route('mailLog.index') }}">{{ trans('fi.mail_log') }}</a></li>
                                    <li><a href="{{ route('systemLog.index') }}">{{ trans('fi.system_log') }}</a></li>
                                </ul>
                            </li>
                            <li class="divider"></li>
                            @foreach (config('fi.menus.system') as $menu)
                                @if (view()->exists($menu))
                                    @include($menu)
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif

                <li>
                    <a href="{{ route('session.logout') }}" title="{{ trans('fi.sign_out') }}"><i class="fa fa-power-off"></i></a>
                </li>

            </ul>

        </div>
    </nav>
</header>