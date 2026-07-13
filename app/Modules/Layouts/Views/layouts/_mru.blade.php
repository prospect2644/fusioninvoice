@if($mruList->count() > 0)
    <h5 class="mru-menu"><span>{{ trans('fi.recently_viewed') }}</span></h5>

    <ul class="sidebar-menu mru-items">
        @foreach($mruList as $mru)
            <li>
                <a href="{{ $mru->url }}">
                    <i class="fa {!! $moduleIconMapping[$mru->module] !!}"></i> <span>{{ $mru->title }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif