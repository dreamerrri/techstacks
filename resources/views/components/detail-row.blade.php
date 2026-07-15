{{-- resources/views/components/detail-row.blade.php

    A single label/value row for read-only detail lists.

    Props:
        label  (string, required) Left-side label.
        border (bool, optional)   Show bottom divider. Default: true.
                                   Set false on the last row in a list.

    Slot: the value — plain text, a badge, whatever fits.

    Example:
        <x-detail-row label="Role">
            <span class="badge {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
        </x-detail-row>

        <x-detail-row label="Last Login" :border="false">
            {{ $user->last_login_at?->format('M d, Y h:i A') ?? '—' }}
        </x-detail-row>
--}}
@props([
    'label',
    'border' => true,
])

<div class="flex justify-between items-center py-2.5 {{ $border ? 'border-b border-base-300' : '' }}">
    <span class="text-base-content/40">{{ $label }}</span>
    <span class="font-medium text-base-content">{{ $slot }}</span>
</div>