<div class="max-h-96 overflow-y-auto">
    @if($notifCount > 0)

        @php
            $items = [
                ...$unassigned->map(fn($emp) => [
                    'href'    => route('employees.edit', $emp),
                    'bg'      => 'bg-red-100',
                    'icon'    => 'fa-user-slash',
                    'color'   => 'text-red-600',
                    'title'   => $emp->full_name,
                    'sub'     => 'Needs dept / position assignment',
                ]),
                ...$missingGovIds->map(fn($emp) => [
                    'href'    => route('employees.edit', $emp),
                    'bg'      => 'bg-amber-100',
                    'icon'    => 'fa-id-card',
                    'color'   => 'text-amber-600',
                    'title'   => $emp->full_name,
                    'sub'     => 'Missing: ' . collect(['SSS' => $emp->sss_number, 'PhilHealth' => $emp->philhealth_number, 'Pag-IBIG' => $emp->pagibig_number, 'TIN' => $emp->tin_number])->filter(fn($v) => is_null($v))->keys()->implode(', '),
                ]),
                ...$overduePayrolls->map(fn($period) => [
                    'href'    => route('payroll.index'),
                    'bg'      => 'bg-violet-100',
                    'icon'    => 'fa-file-invoice-dollar',
                    'color'   => 'text-violet-600',
                    'title'   => \Carbon\Carbon::parse($period->cutoff_start)->format('M d') . ' – ' . \Carbon\Carbon::parse($period->cutoff_end)->format('M d, Y'),
                    'sub'     => 'Due ' . \Carbon\Carbon::parse($period->payroll_date)->format('M d, Y') . ' — still draft',
                ]),
                ...$expiringAllowances->map(fn($allowance) => [
                    'href'    => route('employees.show', $allowance->employee),
                    'bg'      => 'bg-sky-100',
                    'icon'    => 'fa-calendar-times',
                    'color'   => 'text-sky-600',
                    'title'   => $allowance->employee->full_name,
                    'sub'     => '"' . $allowance->name . '" expires ' . \Carbon\Carbon::parse($allowance->end_date)->format('M d, Y'),
                ]),
                ...$expiringBenefits->map(fn($benefit) => [
                    'href'    => route('employees.show', $benefit->employee),
                    'bg'      => 'bg-emerald-100',
                    'icon'    => 'fa-gift',
                    'color'   => 'text-emerald-600',
                    'title'   => $benefit->employee->full_name,
                    'sub'     => '"' . $benefit->name . '" expires ' . \Carbon\Carbon::parse($benefit->end_date)->format('M d, Y'),
                ]),
            ];
        @endphp

        @foreach($items as $item)
            <a href="{{ $item['href'] }}"
               class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 no-underline hover:bg-gray-50 transition-colors">
                <div class="w-9 h-9 rounded-lg {{ $item['bg'] }} flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $item['icon'] }} text-sm {{ $item['color'] }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-gray-900 truncate">{{ $item['title'] }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $item['sub'] }}</div>
                </div>
                <i class="fas fa-chevron-right text-[10px] text-gray-300 flex-shrink-0"></i>
            </a>
        @endforeach

    @else
        <div class="py-8 px-4 text-center">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-check text-lg text-emerald-600"></i>
            </div>
            <div class="text-xs font-semibold text-gray-800 mb-1">All caught up</div>
            <div class="text-xs text-gray-400">No pending actions right now</div>
        </div>
    @endif
</div>