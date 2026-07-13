{{-- resources/views/components/tab-panel.blade.php

    Panel content paired with <x-tabs>. Handles the hidden/visible state
    and ARIA attributes so you never have to remember the exact ID pattern.

    Props:
        id    (string, required) Must match the 'id' used in <x-tabs>'s array.
        first (bool, optional)   Set true on exactly one panel — the one
                                  shown by default. Default: false (hidden).

    Example:
        <x-tab-panel id="account" :first="true">
            ...panel content...
        </x-tab-panel>
--}}
@props([
    'id',
    'first' => false,
])

<div id="tabs-{{ $id }}" class="{{ $first ? '' : 'hidden' }}" role="tabpanel" aria-labelledby="tabs-{{ $id }}-item">
    {{ $slot }}
</div>