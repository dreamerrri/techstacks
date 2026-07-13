{{-- resources/views/components/panel-header.blade.php

    Icon-badge heading — replaces the repeated
    "<span> icon-square </span> Label" header pattern used at the top
    of every panel.

    Props:
        icon  (string, required) Iconify class, e.g. 'icon-[ph--briefcase-fill]'
        color (string, optional) Icon text color class. Default: 'text-gray-500'
        bg    (string, optional) Icon square background class. Default: 'bg-gray-100'

    Slot: heading text.

    Example:
        <x-panel-header icon="icon-[ph--briefcase-fill]" color="text-emerald-600" bg="bg-emerald-100">
            Employment Information
        </x-panel-header>
--}}
@props([
    'icon',
    'color' => 'text-gray-500',
    'bg'    => 'bg-gray-100',
])

<h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
    <span class="w-7 h-7 rounded-md {{ $bg }} flex items-center justify-center {{ $color }} text-xs flex-shrink-0">
        <i class="{{ $icon }}"></i>
    </span>
    {{ $slot }}
</h2>