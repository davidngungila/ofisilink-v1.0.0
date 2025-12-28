@props([
    'class' => '',
    'width' => null,
    'height' => null,
    'alt' => 'OfisiLink'
])

<img
    src="{{ asset('assets/img/office_link_logo.png') }}"
    alt="{{ $alt }}"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    {{ $attributes->merge(['class' => $class]) }}
>


