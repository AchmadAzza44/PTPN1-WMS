@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'group flex items-center px-3 py-2.5 text-sm font-medium bg-blue-800 text-white rounded-lg transition-colors shadow-sm'
        : 'group flex items-center px-3 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>