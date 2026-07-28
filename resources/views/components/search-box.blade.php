@props([
    'id' => 'search-modal',
    'placeholder' => 'Search or type a command',
    'isAdmin' => false,
    'isHr' => false,
])

@php
    // Client-side navigation list — filtered instantly, no network call.
    // Kept deliberately separate from the sidebar's menu array since this
    // list is flat (no dropdown groups) and only needs title/icon/url.
    $commands = [
        ['title' => 'Dashboard', 'icon' => 'tabler--home', 'url' => route('dashboard'), 'keywords' => 'home'],
    ];

    if ($isAdmin) {
        $commands = array_merge($commands, [
            ['title' => 'Users', 'icon' => 'tabler--users', 'url' => route('users.index'), 'keywords' => 'accounts'],
            ['title' => 'Roles', 'icon' => 'tabler--shield', 'url' => route('roles.index'), 'keywords' => ''],
            ['title' => 'Permissions', 'icon' => 'tabler--shield-check', 'url' => route('permissions.index'), 'keywords' => ''],
            ['title' => 'Employees', 'icon' => 'tabler--id', 'url' => route('employees.index'), 'keywords' => 'staff'],
            ['title' => 'Attendance', 'icon' => 'tabler--calendar-check', 'url' => route('manual-payroll-attendance.index'), 'keywords' => ''],
            ['title' => 'Work Requests', 'icon' => 'tabler--notes', 'url' => route('work-requests.index'), 'keywords' => ''],
            ['title' => 'Payroll', 'icon' => 'tabler--cash', 'url' => route('payroll.index'), 'keywords' => ''],
            ['title' => 'Contributions', 'icon' => 'tabler--id-badge', 'url' => route('government-contributions.index'), 'keywords' => 'sss philhealth pagibig'],
            ['title' => 'Audit Logs', 'icon' => 'tabler--file-text', 'url' => route('audit-logs.index'), 'keywords' => ''],
        ]);
    } elseif ($isHr) {
        $commands = array_merge($commands, [
            ['title' => 'Employees', 'icon' => 'tabler--id', 'url' => route('employees.index'), 'keywords' => 'staff'],
            ['title' => 'Attendance', 'icon' => 'tabler--calendar-check', 'url' => route('manual-payroll-attendance.index'), 'keywords' => ''],
            ['title' => 'Work Requests', 'icon' => 'tabler--notes', 'url' => route('work-requests.index'), 'keywords' => ''],
            ['title' => 'Payroll', 'icon' => 'tabler--cash', 'url' => route('payroll.index'), 'keywords' => ''],
            ['title' => 'Contributions', 'icon' => 'tabler--id-badge', 'url' => route('government-contributions.index'), 'keywords' => ''],
        ]);
    } else {
        $commands = array_merge($commands, [
            ['title' => 'My Profile', 'icon' => 'tabler--user', 'url' => route('profile.show'), 'keywords' => ''],
            ['title' => 'My Payslip', 'icon' => 'tabler--receipt', 'url' => route('payroll.index'), 'keywords' => ''],
            ['title' => 'Attendance', 'icon' => 'tabler--clock', 'url' => route('employee-attendance.index'), 'keywords' => ''],
        ]);
    }
@endphp

<!-- Search Trigger -->
<button
    type="button"
    class="input input-bordered btn-outline bg-base-200 flex w-full max-w-xs input-sm items-center gap-2 text-start text-base-content/50 cursor-pointer"
    aria-haspopup="dialog"
    aria-expanded="false"
    aria-controls="{{ $id }}"
    data-overlay="#{{ $id }}"
>
    <span class="icon-[tabler--search] size-4 shrink-0"></span>
    <span class="truncate flex-1">{{ $placeholder }}</span>
    <kbd class="kbd kbd-sm hidden sm:inline-flex">Ctrl K</kbd>
</button>

@push('modals')
<!-- Search Modal -->
<div id="{{ $id }}" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog overflow-x-hidden">
        <div class="modal-content max-h-full"
             x-data="{
                query: '',
                activeIndex: 0,
                loading: false,
                debounceTimer: null,
                pages: {!! \Illuminate\Support\Js::from($commands) !!},
                recordGroups: [],
                get pageResults() {
                    const q = this.query.trim().toLowerCase();
                    if (!q) return this.pages.slice(0, 8);
                    return this.pages
                        .filter(p => p.title.toLowerCase().includes(q) || (p.keywords || '').toLowerCase().includes(q))
                        .slice(0, 6);
                },
                get flatResults() {
                    const items = this.pageResults.map(p => ({ ...p, group: 'Pages' }));
                    this.recordGroups.forEach(g => g.items.forEach(i => items.push({ ...i, group: g.label })));
                    return items;
                },
                search() {
                    clearTimeout(this.debounceTimer);
                    this.activeIndex = 0;
                    if (this.query.trim().length < 2) {
                        this.recordGroups = [];
                        return;
                    }
                    this.debounceTimer = setTimeout(() => {
                        this.loading = true;
                        fetch('{{ route('search') }}?q=' + encodeURIComponent(this.query))
                            .then(r => r.json())
                            .then(data => { this.recordGroups = data.groups ?? []; })
                            .catch(() => { this.recordGroups = []; })
                            .finally(() => { this.loading = false; });
                    }, 250);
                },
                move(step) {
                    const max = this.flatResults.length;
                    if (!max) return;
                    this.activeIndex = (this.activeIndex + step + max) % max;
                },
                select(index = null) {
                    const item = this.flatResults[index ?? this.activeIndex];
                    if (item) window.location.href = item.url;
                }
             }"
             x-init="$watch('query', () => search())"
        >
            <div class="modal-header block">
                <div class="relative">
                    <input
                        type="text"
                        class="input ps-8"
                        placeholder="{{ $placeholder }}"
                        x-model="query"
                        @keydown.arrow-down.prevent="move(1)"
                        @keydown.arrow-up.prevent="move(-1)"
                        @keydown.enter.prevent="select()"
                        autofocus
                    />
                    <span class="icon-[tabler--search] text-base-content absolute start-3 top-1/2 size-4 shrink-0 -translate-y-1/2"></span>
                </div>
            </div>
            <div class="modal-body">
                <div class="overflow-y-auto max-h-72 space-y-0.5">
                    <template x-for="(item, index) in flatResults" :key="item.group + '-' + item.url">
                        <div>
                            <div x-show="index === 0 || flatResults[index - 1].group !== item.group"
                                 class="px-2 pt-2 pb-1 text-xs font-semibold uppercase text-base-content/50"
                                 x-text="item.group"></div>
                            <button type="button"
                                    class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-start text-sm"
                                    :class="index === activeIndex ? 'bg-base-200' : 'hover:bg-base-200'"
                                    @click="select(index)"
                                    @mouseenter="activeIndex = index">
                                <span :class="'icon-[' + item.icon + '] size-4 shrink-0 text-base-content/60'"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate" x-text="item.title"></span>
                                    <span x-show="item.subtitle" class="block truncate text-xs text-base-content/60" x-text="item.subtitle"></span>
                                </span>
                            </button>
                        </div>
                    </template>

                    <div x-show="loading" class="flex items-center justify-center py-4">
                        <span class="loading loading-spinner loading-sm text-base-content/50"></span>
                    </div>

                    <div x-show="!loading && query.trim().length > 0 && flatResults.length === 0"
                         class="px-2 py-6 text-center text-sm text-base-content/50">
                        No results for "<span x-text="query"></span>"
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

{{-- Ctrl/Cmd+K shortcut + reset-on-open. @once assumes a single instance
     of this component per page (i.e. one topbar search) — if you ever
     render it twice with different ids, this needs to move to data
     attributes instead of baking {{ $id }} into the script. --}}
@once
    @push('scripts')
    <script>
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-overlay="#{{ $id }}"]');
        if (!trigger) return;
        const modal = document.getElementById('{{ $id }}');
        const state = window.Alpine?.$data(modal.querySelector('.modal-content'));
        if (state) {
            state.query = '';
            state.recordGroups = [];
            state.activeIndex = 0;
        }
        setTimeout(() => modal.querySelector('input[type="text"]')?.focus(), 100);
    });

    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            document.querySelector('[data-overlay="#{{ $id }}"]')?.click();
        }
    });
    </script>
    @endpush
@endonce