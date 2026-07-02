@if ($paginator->hasPages())
    <nav class="flex items-center gap-x-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <button type="button" class="btn btn-soft btn-disabled" disabled>Previous</button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-soft">Previous</a>
        @endif

        {{-- Page Numbers --}}
        <div class="flex items-center gap-x-1">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <button type="button" class="btn btn-soft btn-square btn-disabled" disabled>{{ $element }}</button>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button type="button" class="btn btn-soft btn-square btn-primary" aria-current="page">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}" class="btn btn-soft btn-square">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-soft">Next</a>
        @else
            <button type="button" class="btn btn-soft btn-disabled" disabled>Next</button>
        @endif
    </nav>

    <p class="text-sm text-gray-500 mt-2">
    Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
</p>
@endif

