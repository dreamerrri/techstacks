<div style="max-height:360px; overflow-y:auto;">
    @if($notifCount > 0)

        {{-- Unassigned --}}
        @if($unassigned->count())
            <div style="padding:6px 16px; background:#f9fafb; font-size:10px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em;">
                Unassigned
            </div>
            @foreach($unassigned as $emp)
                <a href="{{ route('employees.edit', $emp) }}"
                   style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; transition:background 0.15s;"
                   onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:12px; font-weight:700; flex-shrink:0;">
                        {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#1f2937;">{{ $emp->full_name }}</div>
                        <div style="font-size:11px; color:#dc2626;"><i class="fas fa-exclamation-circle"></i> Needs department/position assignment</div>
                    </div>
                </a>
            @endforeach
        @endif

        {{-- Missing Gov IDs --}}
        @if($missingGovIds->count())
            <div style="padding:6px 16px; background:#f9fafb; font-size:10px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em;">
                Missing Government IDs
            </div>
            @foreach($missingGovIds as $emp)
                <a href="{{ route('employees.edit', $emp) }}"
                   style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; transition:background 0.15s;"
                   onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#f59e0b,#d97706); display:flex; align-items:center; justify-content:center; color:white; font-size:12px; font-weight:700; flex-shrink:0;">
                        {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#1f2937;">{{ $emp->full_name }}</div>
                        <div style="font-size:11px; color:#d97706;">
                            <i class="fas fa-id-card"></i>
                            Missing:
                            {{ collect(['SSS' => $emp->sss_number, 'PhilHealth' => $emp->philhealth_number, 'Pag-IBIG' => $emp->pagibig_number, 'TIN' => $emp->tin_number])->filter(fn($v) => is_null($v))->keys()->implode(', ') }}
                        </div>
                    </div>
                </a>
            @endforeach
        @endif

        {{-- Overdue Payrolls --}}
        @if($overduePayrolls->count())
            <div style="padding:6px 16px; background:#f9fafb; font-size:10px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em;">
                Overdue Payroll
            </div>
            @foreach($overduePayrolls as $period)
                <a href="{{ route('payroll.index') }}"
                   style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; transition:background 0.15s;"
                   onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#7c3aed,#5b21b6); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; flex-shrink:0;">
                        <i class="fas fa-money-bill"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#1f2937;">
                            {{ \Carbon\Carbon::parse($period->cutoff_start)->format('M d') }} – {{ \Carbon\Carbon::parse($period->cutoff_end)->format('M d, Y') }}
                        </div>
                        <div style="font-size:11px; color:#7c3aed;"><i class="fas fa-clock"></i> Payroll due {{ \Carbon\Carbon::parse($period->payroll_date)->format('M d, Y') }} — still draft</div>
                    </div>
                </a>
            @endforeach
        @endif

        {{-- Expiring Allowances --}}
        @if($expiringAllowances->count())
            <div style="padding:6px 16px; background:#f9fafb; font-size:10px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em;">
                Expiring Allowances
            </div>
            @foreach($expiringAllowances as $allowance)
                <a href="{{ route('employees.show', $allowance->employee) }}"
                   style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; transition:background 0.15s;"
                   onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#0891b2,#0e7490); display:flex; align-items:center; justify-content:center; color:white; font-size:12px; font-weight:700; flex-shrink:0;">
                        {{ strtoupper(substr($allowance->employee->full_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#1f2937;">{{ $allowance->employee->full_name }}</div>
                        <div style="font-size:11px; color:#0891b2;"><i class="fas fa-calendar-times"></i> "{{ $allowance->name }}" expires {{ \Carbon\Carbon::parse($allowance->end_date)->format('M d, Y') }}</div>
                    </div>
                </a>
            @endforeach
        @endif

        {{-- Expiring Benefits --}}
        @if($expiringBenefits->count())
            <div style="padding:6px 16px; background:#f9fafb; font-size:10px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em;">
                Expiring Benefits
            </div>
            @foreach($expiringBenefits as $benefit)
                <a href="{{ route('employees.show', $benefit->employee) }}"
                   style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; transition:background 0.15s;"
                   onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#059669,#047857); display:flex; align-items:center; justify-content:center; color:white; font-size:12px; font-weight:700; flex-shrink:0;">
                        {{ strtoupper(substr($benefit->employee->full_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#1f2937;">{{ $benefit->employee->full_name }}</div>
                        <div style="font-size:11px; color:#059669;"><i class="fas fa-calendar-times"></i> "{{ $benefit->name }}" expires {{ \Carbon\Carbon::parse($benefit->end_date)->format('M d, Y') }}</div>
                    </div>
                </a>
            @endforeach
        @endif

    @else
        <div style="padding:24px; text-align:center; color:#9ca3af; font-size:13px;">
            <i class="fas fa-check-circle" style="font-size:24px; color:#10b981; display:block; margin-bottom:8px;"></i>
            No pending actions
        </div>
    @endif
</div>