{{-- resources/views/components/tabs.blade.php

    Tab navigation bar. Generates IDs and the "active" state automatically
    from a plain array — no more hand-typing tabs-basic-filled-item-N on
    every page, and no more risk of every button ending up "active" at once.

    Props:
        tabs (array, required) e.g.:
            [
                ['id' => 'account',  'label' => 'Account Info'],
                ['id' => 'gov',      'label' => 'Government Contributions'],
                ['id' => 'settings', 'label' => 'Settings'],
            ]
        The 'id' becomes the panel ID this tab controls — must match the
        `id` prop on the corresponding <x-tab-panel>.

    Pair with <x-tab-panel> for the actual panel content — this component
    only renders the nav bar.

    Example:
        <x-tabs :tabs="[
            ['id' => 'account',  'label' => 'Account Info'],
            ['id' => 'settings', 'label' => 'Settings'],
        ]" />

        <div class="mt-3">
            <x-tab-panel id="account" :first="true"> ... </x-tab-panel>
            <x-tab-panel id="settings"> ... </x-tab-panel>
        </div>
--}} {{-- [&_.tab:hover]:text-success [&_.tab:hover]:border-success [&_.tab-active]:border-success [&_.tab-active]:text-success --}}
@props(['tabs' => []])

<nav class="tabs tabs-bordered "
     aria-label="Tabs" role="tablist" aria-orientation="horizontal">
    @foreach($tabs as $index => $tab)
        <button type="button"
                class="tab active-tab:tab-active w-full {{ $index === 0 ? 'active' : '' }}"
                id="tabs-{{ $tab['id'] }}-item"
                data-tab="#tabs-{{ $tab['id'] }}"
                aria-controls="tabs-{{ $tab['id'] }}"
                role="tab"
                aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
            {{ $tab['label'] }}
        </button>
    @endforeach
</nav>