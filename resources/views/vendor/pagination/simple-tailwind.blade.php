@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-base-content/70 bg-base-100 border  border-base-300 cursor-not-allowed leading-5 rounded-md dark:text-base-content/30 dark:bg-base-300 dark:border-base-300">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-base-content bg-base-100 border  border-base-300 leading-5 rounded-md hover:text-base-content/80 focus:outline-none focus:ring ring-base-300 focus:border-primary active:bg-base-200 active:text-base-content transition ease-in-out duration-150 dark:bg-base-300 dark:border-base-300 dark:text-base-content dark:focus:border-primary dark:active:bg-base-300 dark:active:text-base-content/30 hover:bg-base-200 dark:hover:bg-base-300 dark:hover:text-base-content">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-base-content bg-base-100 border  border-base-300 leading-5 rounded-md hover:text-base-content/80 focus:outline-none focus:ring ring-base-300 focus:border-primary active:bg-base-200 active:text-base-content transition ease-in-out duration-150 dark:bg-base-300 dark:border-base-300 dark:text-base-content dark:focus:border-primary dark:active:bg-base-300 dark:active:text-base-content/30 hover:bg-base-200 dark:hover:bg-base-300 dark:hover:text-base-content">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-base-content/70 bg-base-100 border  border-base-300 cursor-not-allowed leading-5 rounded-md dark:text-base-content/30 dark:bg-base-300 dark:border-base-300">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
