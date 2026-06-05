<div style="max-height:380px; overflow-y:auto;">
    @if($notifCount > 0)

        {{-- Unassigned --}}
        @foreach($unassigned as $emp)
            <a href="{{ route('employees.edit', $emp) }}"
               style="display:flex; align-items:center; gap:12px; padding:11px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; background:white; transition:background 0.15s;"
               onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
                <div style="width:34px; height:34px; border-radius:9px; background:#fee2e2; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fas fa-user-slash" style="font-size:14px; color:#dc2626;"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $emp->full_name }}</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:1px;">Needs dept / position assignment</div>
                </div>
                <i class="fas fa-chevron-right" style="font-size:10px; color:#d1d5db; flex-shrink:0;"></i>
            </a>
        @endforeach

        {{-- Missing Gov IDs --}}
        @foreach($missingGovIds as $emp)
            <a href="{{ route('employees.edit', $emp) }}"
               style="display:flex; align-items:center; gap:12px; padding:11px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; background:white; transition:background 0.15s;"
               onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
                <div style="width:34px; height:34px; border-radius:9px; background:#fef3c7; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fas fa-id-card" style="font-size:14px; color:#d97706;"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $emp->full_name }}</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:1px;">
                        Missing: {{ collect(['SSS' => $emp->sss_number, 'PhilHealth' => $emp->philhealth_number, 'Pag-IBIG' => $emp->pagibig_number, 'TIN' => $emp->tin_number])->filter(fn($v) => is_null($v))->keys()->implode(', ') }}
                    </div>
                </div>
                <i class="fas fa-chevron-right" style="font-size:10px; color:#d1d5db; flex-shrink:0;"></i>
            </a>
        @endforeach

        {{-- Overdue Payrolls --}}
        @foreach($overduePayrolls as $period)
            <a href="{{ route('payroll.index') }}"
               style="display:flex; align-items:center; gap:12px; padding:11px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; background:white; transition:background 0.15s;"
               onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
                <div style="width:34px; height:34px; border-radius:9px; background:#ede9fe; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fas fa-file-invoice-dollar" style="font-size:14px; color:#7c3aed;"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ \Carbon\Carbon::parse($period->cutoff_start)->format('M d') }} – {{ \Carbon\Carbon::parse($period->cutoff_end)->format('M d, Y') }}
                    </div>
                    <div style="font-size:11px; color:#6b7280; margin-top:1px;">Due {{ \Carbon\Carbon::parse($period->payroll_date)->format('M d, Y') }} — still draft</div>
                </div>
                <i class="fas fa-chevron-right" style="font-size:10px; color:#d1d5db; flex-shrink:0;"></i>
            </a>
        @endforeach

        {{-- Expiring Allowances --}}
        @foreach($expiringAllowances as $allowance)
            <a href="{{ route('employees.show', $allowance->employee) }}"
               style="display:flex; align-items:center; gap:12px; padding:11px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; background:white; transition:background 0.15s;"
               onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
                <div style="width:34px; height:34px; border-radius:9px; background:#e0f2fe; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fas fa-calendar-times" style="font-size:14px; color:#0891b2;"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $allowance->employee->full_name }}</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:1px;">"{{ $allowance->name }}" expires {{ \Carbon\Carbon::parse($allowance->end_date)->format('M d, Y') }}</div>
                </div>
                <i class="fas fa-chevron-right" style="font-size:10px; color:#d1d5db; flex-shrink:0;"></i>
            </a>
        @endforeach

        {{-- Expiring Benefits --}}
        @foreach($expiringBenefits as $benefit)
            <a href="{{ route('employees.show', $benefit->employee) }}"
               style="display:flex; align-items:center; gap:12px; padding:11px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; background:white; transition:background 0.15s;"
               onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
                <div style="width:34px; height:34px; border-radius:9px; background:#d1fae5; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fas fa-gift" style="font-size:14px; color:#059669;"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $benefit->employee->full_name }}</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:1px;">"{{ $benefit->name }}" expires {{ \Carbon\Carbon::parse($benefit->end_date)->format('M d, Y') }}</div>
                </div>
                <i class="fas fa-chevron-right" style="font-size:10px; color:#d1d5db; flex-shrink:0;"></i>
            </a>
        @endforeach

    @else
        <div style="padding:32px 16px; text-align:center;">
            <div style="width:44px; height:44px; border-radius:12px; background:#d1fae5; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                <i class="fas fa-check" style="font-size:18px; color:#059669;"></i>
            </div>
            <div style="font-size:13px; font-weight:600; color:#111827; margin-bottom:4px;">All caught up</div>
            <div style="font-size:12px; color:#9ca3af;">No pending actions right now</div>
        </div>
    @endif
</div>