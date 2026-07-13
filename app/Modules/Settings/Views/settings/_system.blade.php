<div class="row">
    @if (!config('app.demo'))
        <div class="col-md-12">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ trans('fi.item') }}</th>
                    <th>{{ trans('fi.value') }}</th>
                </tr>
                </thead>
                <tr>
                    <td class="view-field-label">{{ trans('fi.version') }}</td>
                    <td>
                        <div class="input-group input-group-sm col-md-4">
                            {!! Form::text('version', config('fi.version'), ['class' => 'form-control input-sm', 'disabled' => 'disabled']) !!}
                            <span class="input-group-btn">
                                <button class="btn btn-sm btn-info btn-check-update" type="button">
                                    {{ trans('fi.check_for_update') }}
                                </button>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">PHP Version</td>
                    <td>
                        @if(version_compare(PHP_VERSION, '7.2.5') >= 0)
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                        {{ PHP_VERSION }}
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">Database Version</td>
                    <td>
                        @php
                        $results = DB::select( DB::raw("select version()") );
                        $mysql_version = isset($results[0]->{'version()'}) ? $results[0]->{'version()'} : 'UNKNOWN';
                        echo $mysql_version;
                        @endphp
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">Webserver</td>
                    <td>
                        {{ $_SERVER["SERVER_SOFTWARE"] }}
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">PHP FileInfo Extension Enabled</td>
                    <td>
                        @if (extension_loaded('fileinfo'))
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">PHP OpenSSL Extension Enabled</td>
                    <td>
                        @if (extension_loaded('openssl'))
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">PHP PDO Extension Enabled</td>
                    <td>
                        @if (extension_loaded('pdo'))
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">PHP MySQL Extension Enabled</td>
                    <td>
                        @if (extension_loaded('pdo_mysql'))
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">PHP MBString Extension Enabled</td>
                    <td>
                        @if (extension_loaded('mbstring'))
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">PHP Tokenizer Extension Enabled</td>
                    <td>
                        @if (extension_loaded('tokenizer'))
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">Graphics Drawing Extension Enabled</td>
                    <td>
                        @if (extension_loaded('gd') or extension_loaded('gd2'))
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="view-field-label">Storage Folder Writable</td>
                    <td>
                        @if (is_writable(storage_path()))
                            <button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>
                        @else
                            <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i></button>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif
</div>