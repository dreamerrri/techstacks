<div class="flex items-center justify-between">
    <p class="text-sm text-base-content mt-2">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </p>

    @if ($paginator->hasPages())
        <nav class="flex items-center justify-end gap-x-1">
            {{-- First --}}
            @if ($paginator->onFirstPage())
                <button type="button" class="btn btn-disabled" disabled>
                    <span class="icon-[tabler--chevrons-left] size-5 rtl:rotate-180"></span>
                </button>
            @else
                <a href="{{ $paginator->url(1) }}" class="btn">
                    <span class="icon-[tabler--chevrons-left] size-5 rtl:rotate-180"></span>
                </a>
            @endif

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <button type="button" class="btn btn-disabled" disabled>
                    <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180"></span>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn">
                    <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180"></span>
                </a>
            @endif

            {{-- Page Numbers --}}
            <div class="flex items-center gap-x-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <button type="button" class="text-sm btn btn-square btn-disabled" disabled>{{ $element }}</button>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <button type="button" class="text-sm btn btn-primary btn-square" aria-current="page">{{ $page }}</button>
                            @else
                                <a href="{{ $url }}" class="text-sm btn btn-square">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn">
                    <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180"></span>
                </a>
            @else
                <button type="button" class="btn btn-disabled" disabled>
                    <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180"></span>
                </button>
            @endif

            {{-- Last --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->url($paginator->lastPage()) }}" class="btn">
                    <span class="icon-[tabler--chevrons-right] size-5 rtl:rotate-180"></span>
                </a>
            @else
                <button type="button" class="btn btn-disabled" disabled>
                    <span class="icon-[tabler--chevrons-right] size-5 rtl:rotate-180"></span>
                </button>
            @endif
        </nav>
    @endif
</div>