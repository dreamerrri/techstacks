{{-- resources/views/partials/breadcrumbs.blade.php --}}

@unless ($breadcrumbs->isEmpty())
  <div class="breadcrumb-trail flex items-center" data-depth="{{ $breadcrumbs->count() }}">
    @foreach ($breadcrumbs as $breadcrumb)

        <span class="breadcrumb-item inline-flex items-center"
              style="animation-delay: {{ $loop->index * 60 }}ms">

            @if (!is_null($breadcrumb->url) && !$loop->last)
                <a href="{{ $breadcrumb->url }}"
                   class="text-white/55 no-underline transition-colors duration-200 hover:text-white/80">
                    {{ $breadcrumb->title }}
                </a>
            @elseif($loop->last)
                <span class="text-white font-semibold">
                    {{ $breadcrumb->title }}
                </span>
            @else
                <span class="text-white/55">
                    {{ $breadcrumb->title }}
                </span>
            @endif

        </span>

        @unless ($loop->last)
            <span class="mx-3 inline-flex items-center text-white/40">
                <i class="icon-[ph--caret-right-fill] text-xs"></i>
            </span>
        @endunless

    @endforeach
</div>
    <script>
        (function () {
            var el = document.querySelector('.breadcrumb-trail');
            if (!el) return;

            var currentDepth  = parseInt(el.dataset.depth, 10);
            var previousDepth = parseInt(sessionStorage.getItem('breadcrumb_depth'), 10);

            if (isNaN(previousDepth)) {
                el.classList.add('breadcrumb-fade-only');
            } else if (currentDepth > previousDepth) {
                el.classList.add('breadcrumb-slide-forward');
            } else if (currentDepth < previousDepth) {
                el.classList.add('breadcrumb-slide-backward');
            } else {
                el.classList.add('breadcrumb-fade-only');
            }

            sessionStorage.setItem('breadcrumb_depth', currentDepth);
        })();
    </script>

    <style>
        @keyframes breadcrumb-slide-in-right {
            from { opacity: 0; transform: translateX(14px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes breadcrumb-slide-in-left {
            from { opacity: 0; transform: translateX(-14px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes breadcrumb-fade-in {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .breadcrumb-slide-forward .breadcrumb-item {
            animation: breadcrumb-slide-in-right 250ms ease-out backwards;
        }
        .breadcrumb-slide-backward .breadcrumb-item {
            animation: breadcrumb-slide-in-left 250ms ease-out backwards;
        }
        .breadcrumb-fade-only .breadcrumb-item {
            animation: breadcrumb-fade-in 250ms ease-out backwards;
        }

        @media (prefers-reduced-motion: reduce) {
            .breadcrumb-slide-forward .breadcrumb-item,
            .breadcrumb-slide-backward .breadcrumb-item,
            .breadcrumb-fade-only .breadcrumb-item {
                animation: none;
            }
        }
    </style>
@endunless