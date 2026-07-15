@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-base-content/70 bg-white border  border-base-300 cursor-not-allowed leading-5 rounded-md dark:text-base-content/30 dark:bg-gray-700 dark:border-gray-600">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-base-content bg-white border  border-base-300 leading-5 rounded-md hover:text-base-content/80 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-base-content transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-base-content/30 hover:bg-base-200 dark:hover:bg-gray-900 dark:hover:text-gray-200">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-base-content bg-white border  border-base-300 leading-5 rounded-md hover:text-base-content/80 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-base-content transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-base-content/30 hover:bg-base-200 dark:hover:bg-gray-900 dark:hover:text-gray-200">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-base-content/70 bg-white border  border-base-300 cursor-not-allowed leading-5 rounded-md dark:text-base-content/30 dark:bg-gray-700 dark:border-gray-600">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
