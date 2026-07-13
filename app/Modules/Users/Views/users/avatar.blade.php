<div title="{{ $user->name }}" style="background-color: {{ $user->initials_bg_color }};
        width: {{ $size }}px;
        height: {{ $size }}px;
        font-size: {{ $size / 2 }}px;
        font-family: 'Karla', sans-serif;
        cursor: pointer;
        color: #FFFFFF;
        text-align: center;
        line-height: {{ $size }}px;
        @if($isRounded)
        border-radius: 50%;
        @endif">
    {!! $user->deleted_at != '' ? '<strike>'.$user->initials.'</strike>' : $user->initials !!}
</div>