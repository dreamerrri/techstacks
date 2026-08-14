{{-- resources/views/components/table-card.blade.php

    Card wrapper for filterable list pages: sticky header (title + optional
    action buttons) + GET filter form, with the table body (desktop
    <x-data-table>, mobile cards, pagination) passed as the default slot.

    Props:
        action  (string, required)  Filter form's "action" URL,
                                     e.g. route('payroll.index').

    Slots:
        title    (named)  Heading content (icon + label + tooltip if any).
        actions  (named, optional)  Buttons top-right of header. Omit if none.
        filters  (named)  Filter form fields (inputs/selects).
        (default) (slot)  Table body content.

    Example:
        < action="{{ route('payroll.index') }}">
            <x-slot:title>
                <x-dot-loader /> Payroll Summary
                <x-info-tooltip>
                    @if($isAdmin || $isHR)
                        View payroll calculations for all employees.
                    @else
                        View your payroll calculation.
                    @endif
                </x-info-tooltip>
            </x-slot:title>

            @if($isAdmin || $isHR)
                <x-slot:actions>
                    <button onclick="openDeptModal()" class="btn  btn-error btn-sm">
                        <i class="icon-[ph--stack-fill]"></i> Breakdown
                    </button>
                </x-slot:actions>
            @endif

            <x-slot:filters>
                @if($isAdmin || $isHR)
                    <div class="join flex-none w-64 min-w-40">
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="input input-bordered input-sm join-item w-full border-base-300">
                        <button type="submit" class="btn btn-outline btn-sm join-item border-base-300">
                            <i class="icon-[tabler--search]"></i>
                        </button>
                    </div>
                @endif

                <select name="payroll_period_id" onchange="this.closest('form').submit()"
                        class="select select-bordered select-sm w-40">
                    ...
                </select>
            </x-slot:filters>

            <x-data-table>
                ...
            </x-data-table>

        () mobile cards, pagination, etc. ()
     
   < / x-table-card>
   --}}
{{-- resources/views/components/table-card.blade.php --}}
@props(['action' => null])
{{-- -on one hand bg-base-200 looks good on dark mode, on the other hand, it sucks on light mode --}}
<div class="card w-full min-w-0 border border-base-300 flex flex-col p-0">

<div class="sticky top-0 px-4 sm:px-7 pt-5 rounded-t-2xl">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-widest text-faint flex items-center gap-2 m-0">
                {{ $title ?? '' }}
            </h2>

            @isset($actions)
                {{ $actions }}
            @endisset
        </div>

        @isset($filters)
            <form method="GET" action="{{ $action }}"
                  class="flex flex-col md:flex-row md:items-center gap-3 pb-4">
                {{ $filters }}
            </form>
        @endisset
    </div>

    {{ $slot }}

</div>