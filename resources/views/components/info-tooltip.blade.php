{{-- resources/views/components/info-tooltip.blade.php

    Icon-triggered hover tooltip, for contextual help text next to headings/labels.

    Props:
        placement (string, optional)  Tooltip position. Default: 'right'.

    Slots:
        (default)  Tooltip body content.

    Example:
        <x-info-tooltip>
            View payroll calculations for all employees.
        </x-info-tooltip>
--}}
@props(['placement' => 'right'])

<div class="tooltip [--placement:{{ $placement }}]">
    <span class="tooltip-toggle cursor-pointer text-gray-400 hover:text-gray-600" aria-label="More info">
        <i class="icon-[ph--info-fill]"></i>
    </span>
    <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
        <span class="tooltip-body bg-success/67 shadow-md rounded-lg px-3 py-2 text-xs normal-case">
            {{ $slot }}
        </span>
    </span>
</div>