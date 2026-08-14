{{-- resources/views/components/panel-header.blade.php

    Icon-badge heading — replaces the repeated
    "<span> icon-square </span> Label" header pattern used at the top
    of every panel.

    Props:
        icon  (string, required) Iconify class, e.g. 'icon-[ph--briefcase-fill]'
        color (string, optional) Icon text color class. Default: 'text-subtle'
        bg    (string, optional) Icon square background class. Default: 'bg-base-200'

    Slot: heading text.

    Example:
        <x-panel-header icon="icon-[ph--briefcase-fill]" color="text-success" bg="bg-success/10">
            Employment Information
        </x-panel-header>
--}}
@props([
    'icon',
    'color' => 'text-subtle',
    'bg'    => 'bg-base-200',
])

<h2 class="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
    <span class="w-7 h-7 rounded-md {{ $bg }} flex items-center justify-center {{ $color }} text-xs flex-shrink-0">
        <i class="{{ $icon }}"></i>
    </span>
    {{ $slot }}
</h2>