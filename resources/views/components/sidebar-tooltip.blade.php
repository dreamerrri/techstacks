{{-- resources/views/components/info-tooltip.blade.php --}}

@props([
    'placement' => 'right'
])

<div class="tooltip [--placement:{{ $placement }}] nav-tooltip">

    <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible"
          role="tooltip">

        <span class="
            tooltip-body
            rounded-lg
            shadow-md
            px-3
            py-2
            text-xs
            font-medium
            normal-case
            bg-success/90
            text-success-content">

            {{ $slot }}

        </span>

    </span>

</div>