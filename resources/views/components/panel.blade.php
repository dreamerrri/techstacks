{{-- resources/views/components/panel.blade.php

    Generic content panel — replaces repeated `<div class="panel p-X">` blocks.

    Props:
        padding (string, optional) Tailwind padding class. Default: 'p-6'.

    Extra classes (e.g. md:col-span-2) pass through normally via class="".

    Example:
        <x-panel class="md:col-span-2">
            ...content...
        </x-panel>

        <x-panel padding="p-4" class="text-center">
            ...content...
        </x-panel>
--}}
@props(['padding' => 'p-6'])

<div {{ $attributes->merge(['class' => "panel {$padding}"]) }}>
    {{ $slot }}
</div>