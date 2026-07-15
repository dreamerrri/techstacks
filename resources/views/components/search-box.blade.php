@props([
    'id' => 'search-modal',
    'placeholder' => 'Search or type a command',
])

<!-- Search Trigger -->
<button
    type="button"
    class="input input-bordered flex w-full max-w-xs input-sm items-center gap-2 text-start text-base-content/50 cursor-pointer"
    aria-haspopup="dialog"
    aria-expanded="false"
    aria-controls="{{ $id }}"
    data-overlay="#{{ $id }}"
>
    <span class="icon-[tabler--search] size-4 shrink-0"></span>
    <span class="truncate">{{ $placeholder }}</span>
</button>

@push('modals')
<!-- Search Modal -->
<div id="{{ $id }}" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog overflow-x-hidden">
        <div class="modal-content max-h-full">
            <div class="modal-header block">
                <div class="relative">
                    <input
                        type="text"
                        class="input ps-8"
                        placeholder="{{ $placeholder }}"
                        autofocus
                    />
                    <span class="icon-[tabler--search] text-base-content absolute start-3 top-1/2 size-4 shrink-0 -translate-y-1/2"></span>
                </div>
            </div>
            <div class="modal-body">
                <div class="overflow-y-auto max-h-72 space-y-0.5">
                    {{-- results go here later --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endpush