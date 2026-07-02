   <div class="flex items-center justify-between">
   
   <p class="text-sm text-gray-500 mt-2">
    Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
</p>
@if ($paginator->hasPages())

    <nav class="flex items-center justify-end gap-x-1">
        
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <button type="button" class="btn btn-soft btn-disabled" disabled>    
                <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180" disabled></span>
            </button>

  </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-soft">
                <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180" disabled></span>
            </a>
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
                            <button type="button" class="btn btn-soft btn-square btn-success bg-success/20" aria-current="page">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}" class="btn btn-soft btn-square">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next --}}
        @if ($paginator->hasMorePages())


            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-soft">    
                <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180"></span>
</a>
        @else
            <button type="button" class="btn btn-soft btn-disabled" disabled>    
                <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180"></span>
</button>
        @endif
    </nav>


@endif

</div>