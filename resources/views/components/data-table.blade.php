{{-- resources/views/components/data-table.blade.php

    Generic scrollable data table skeleton for desktop list pages.

    Props:
        maxHeight  (string, optional)  Max height for scroll container. Default: '55vh'.

    Slots:
        head       (named)  <th> cells. Set column widths here directly
                             (e.g. <th class="w-40">), not via colgroup —
                             table-fixed reads widths from the first row.
        (default)  (slot)   <tr> rows for <tbody>.

    Example:
        <x-data-table>
            <x-slot:head>
                <th class="w-40">Name</th>
                <th class="w-24">Slug</th>
                <th>Description</th> {{-- no width = auto-fills remaining space --}}
 {{--               <th class="w-36 text-right">Actions</th>
            </x-slot:head>

            @forelse($roles as $role)
                <tr>...</tr>
            @empty
                <tr><td colspan="4">No data found.</td></tr>
            @endforelse
        </x-data-table>
--}}

@props(['maxHeight' => '55vh'])

<div class="overflow-x-auto overflow-y-auto max-h-[{{ $maxHeight }}] hidden md:block">
    <table class="table table-hover table-fixed w-full text-sm table-borderless">
        <thead class="sticky top-0 z-5" style="background: white">
            <tr class="bg-success/67 shadow-md text-white text-xs">
                {{ $head }}
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>