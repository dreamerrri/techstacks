{{-- resources/views/partials/breadcrumbs.blade.php --}}

@unless ($breadcrumbs->isEmpty())
    @foreach ($breadcrumbs as $breadcrumb)
        @if (!is_null($breadcrumb->url) && !$loop->last)
            <a href="{{ $breadcrumb->url }}" class="text-white/55 no-underline hover:text-white/80">
                {{ $breadcrumb->title }}
            </a>
        @elseif($loop->last)
            <span class="text-white font-semibold">{{ $breadcrumb->title }}</span>
        @else
            <span class="text-white/55">{{ $breadcrumb->title }}</span>
        @endif

        @unless ($loop->last)
            <i class="icon-[ph--caret-right-fill] text-xs"></i>
        @endunless
    @endforeach
@endunless