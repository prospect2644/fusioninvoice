@extends('client_center.layouts.logged_in')

@section('content')

    <section class="content-header">
        <h1>{{ trans('fi.attachments') }}</h1>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">

                <div class="box box-primary">

                    <div class="box-body no-padding">

                        <table class="table table-hover table-striped">
                            <thead>
                            <tr>
                                <th class="col-md-2">{{ trans('fi.date') }}</th>
                                <th class="col-md-2">{{ trans('fi.attached_to') }}</th>
                                <th class="col-md-8">{{ trans('fi.attachment') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($attachments as $attachment)
                                <tr>
                                    <td>{{ $attachment->formatted_created_at }}</td>
                                    <td>
                                        @if ($attachment->attachable instanceof \FI\Modules\Invoices\Models\Invoice)
                                            <a href="{{ route('clientCenter.public.invoice.show', [$attachment->attachable->url_key]) }}">{{ trans('fi.invoice') }} #{{ $attachment->attachable->number }}</a>
                                        @elseif ($attachment->attachable instanceof \FI\Modules\Quotes\Models\Quote)
                                            <a href="">{{ trans('fi.quote') }} #{{ $attachment->attachable->number }}</a>
                                        @elseif ($attachment->attachable instanceof \FI\Modules\Expenses\Models\Expense)
                                            {{ trans('fi.expense') }}
                                        @elseif ($attachment->attachable instanceof \FI\Modules\Clients\Models\Client)
                                            {{ trans('fi.client') }}
                                        @endif
                                    </td>
                                    <td><a href="{{ $attachment->download_url }}">{{ $attachment->filename }}</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>

            </div>

        </div>

    </section>

@stop