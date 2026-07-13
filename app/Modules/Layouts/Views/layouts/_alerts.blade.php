<script type="text/javascript">
    @foreach ($errors->all() as $error)
        alertify.error('{{ $error }}', 10);
    @endforeach
</script>


@if (session()->has('error'))
    <script type="text/javascript">
        alertify.error('{{ session('error') }}', 10);
    </script>
@endif

@if (session()->has('alert'))
    <script type="text/javascript">
        alertify.error('{{ session('alert') }}', 10);
    </script>
@endif

@if (session()->has('alertSuccess'))
    <script type="text/javascript">
        alertify.success('{!! session('alertSuccess') !!}', 2.5);
    </script>
@endif

@if (session()->has('errorFolderCreate'))
    @if(isset(session()->get('errorFolderCreate')['create']) && !empty(session()->get('errorFolderCreate')['create']))
        @foreach(session()->get('errorFolderCreate')['create'] as $path)
            <script type="text/javascript">
                alertify.notify('{{trans("fi.create_missing_folder_failed",['path' => addslashes($path)])}}', 'error-lg', 10);
            </script>
        @endforeach
    @endif
    @if(isset(session()->get('errorFolderCreate')['permission']) && !empty(session()->get('errorFolderCreate')['permission']))
        @foreach(session()->get('errorFolderCreate')['permission'] as $path)
            <script type="text/javascript">
                alertify.notify('{{trans("fi.folder_is_not_writable",['path' => addslashes($path)])}}', 'error-lg', 10);
            </script>
        @endforeach
    @endif
@elseif (session()->has('successFolderCreate'))
    <script type="text/javascript">
        alertify.success('{!! session()->get('successFolderCreate')->first() !!}', 2.5);
    </script>
@endif

@if (session()->has('alertInfo'))
    <script type="text/javascript">
        alertify.notify('{!! session('alertInfo') !!}', 5);
    </script>
@endif

@if (session()->has('piracyAlert') && session('piracyAlert') != null)
    <div class="box box-danger" style="border-bottom: 3px solid #dd4b39">
        <div class="box-header">
            <h3 class="box-title" style="font-weight: bold">{{ trans('fi.piracy_alert') }}</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool version-check-preference" data-widget="remove"><i
                            class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body" style="font-weight: bold">
            {{ session('piracyAlert') }}
            <a href="https://www.fusioninvoice.com/store" class="btn btn-primary btn-sm pull-right"
               target="_blank">{{ trans('fi.buy-now') }}</a>
        </div>
    </div>
@endif