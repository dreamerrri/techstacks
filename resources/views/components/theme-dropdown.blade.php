{{-- 
    <x-theme-dropdown />

    Anonymous Blade component — place this file at:
        resources/views/components/theme-dropdown.blade.php
--}}
@props([
    'label' => 'Theme',
])

@php
    $flyonThemes = [
        'light', 'dark', 'black', 'claude', 'corporate', 'ghibli', 'gourmet',
        'luxury', 'mintlify', 'pastel', 'perplexity', 'shadcn', 'slack',
        'soft', 'spotify', 'valorant', 'vscode',
    ];

    $currentTheme = auth()->check()
        ? (auth()->user()->theme ?? 'light')
        : 'light';

    $dropdownId = 'theme-dropdown-toggle-' . uniqid();
@endphp

<div {{ $attributes->merge(['class' => 'dropdown relative inline-flex [--auto-close:inside]']) }}>

    <button
        id="{{ $dropdownId }}"
        type="button"
        class="dropdown-toggle btn btn-soft btn-primary btn-sm rounded-full gap-2"
        aria-haspopup="menu"
        aria-expanded="false"
        aria-label="{{ $label }} switcher"
    >
        {{ $label }}

        <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 transition-transform"></span>
    </button>

    <div
        class="dropdown-menu dropdown-open:opacity-100 hidden w-64 p-2"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="{{ $dropdownId }}"
    >

        <div class="theme-scroll max-h-80 overflow-y-auto overflow-x-hidden py-2 pr-2 flex flex-col gap-2">

            @foreach ($flyonThemes as $theme)

                <label
                    data-theme="{{ $theme }}"
                    class="
                        theme-row
                        group
                        w-full
                        box-border

                        flex
                        items-center
                        justify-between

                        rounded-full
                        border-2
                        border-transparent

                        bg-base-100
                        text-base-content

                        px-4
                        py-2

                        cursor-pointer
                        transition-all
                        duration-150

                        hover:border-base-300

                        has-checked:border-primary
                    "
                >

                    <span class="flex items-center gap-2 min-w-0">

                        <input
                            type="radio"
                            name="theme-picker-{{ $dropdownId }}"
                            value="{{ $theme }}"
                            class="theme-picker-input sr-only"
                            {{ $currentTheme === $theme ? 'checked' : '' }}
                        >

                        <span
                            class="icon-[tabler--check]
                                   size-4
                                   shrink-0
                                   opacity-0
                                   transition-opacity
                                   group-has-checked:opacity-100">
                        </span>

                        <span class="capitalize truncate font-medium">
                            {{ $theme }}
                        </span>

                    </span>

                    <span class="ml-4 flex items-center gap-1 shrink-0">

                        <span class="h-5 w-2 rounded-sm bg-primary"></span>
                        <span class="h-5 w-2 rounded-sm bg-secondary"></span>
                        <span class="h-5 w-2 rounded-sm bg-accent"></span>

                    </span>

                </label>

            @endforeach

        </div>

    </div>

</div>

<style>
.theme-scroll{
    scrollbar-width:thin;
    scrollbar-color:var(--color-base-300,#d1d5db) transparent;
}

.theme-scroll::-webkit-scrollbar{
    width:6px;
}

.theme-scroll::-webkit-scrollbar-track{
    background:transparent;
}

.theme-scroll::-webkit-scrollbar-thumb{
    background:var(--color-base-300,#d1d5db);
    border-radius:9999px;
}
</style>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.theme-picker-input').forEach(input => {

        input.addEventListener('change', function () {

            document.documentElement.setAttribute('data-theme', this.value);

            localStorage.setItem('theme', this.value);

        });

    });

});
</script>
@endonce