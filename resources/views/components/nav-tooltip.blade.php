{{-- resources/views/components/nav-tooltip.blade.php

    Wraps nav-link content and shows a tooltip only when the sidebar
    is in its minified (icon-only) state.

    Props:
        placement (string, optional) Default: 'right'.

    Slots:
        (default)  The link's normal content (icon + text span).
        text       The tooltip label.
--}}
{{-- resources/views/components/nav-tooltip.blade.php --}}
@props(['text'])

<span class="tooltip-content overlay-minified:tooltip-shown:opacity-100 overlay-minified:tooltip-shown:visible" role="tooltip">
    <span class="tooltip-body bg-neutral/90 shadow-md rounded-lg px-3 py-2 text-xs normal-case text-neutral-content font-medium whitespace-nowrap">
        {{ $text }}
    </span>
</span>