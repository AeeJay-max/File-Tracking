@props([
    'user',
    'size'   => 34,      // px
    'class'  => '',
    'radius' => '50%',
])
@php
    $style  = "width:{$size}px;height:{$size}px;border-radius:{$radius};";
    $fs     = max(10, (int)($size * 0.42));
@endphp

@if($user->photo_url)
<img src="{{ $user->photo_url }}"
     alt="{{ $user->name }}"
     class="{{ $class }}"
     style="{{ $style }}object-fit:cover;flex-shrink:0;"
     title="{{ $user->name }}">
@else
<div class="{{ $class }}"
     style="{{ $style }}background:linear-gradient(135deg,#005a2b,#00421f);color:#fff;
            display:flex;align-items:center;justify-content:center;
            font-weight:700;font-size:{{ $fs }}px;flex-shrink:0;"
     title="{{ $user->name }}">
    {{ $user->initials }}
</div>
@endif
