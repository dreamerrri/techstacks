{{--
    <x-dot-loader />

    A three-dot "loading" indicator. At rest it's three dots in a row
    (like the Linux/GNOME loading ellipsis). While something is happening
    (page navigation, form submit, fetch/XHR/Livewire request) the dots
    morph into a triangle and spin like a circle, then morph back when
    it's done.

    INSTALL
    -------
    1. Save this file as: resources/views/components/dot-loader.blade.php
    2. Drop it anywhere in a Blade view:
           <x-dot-loader />
    3. Make sure your main layout has `@stack('scripts')` right before
       `</body>`. This component pushes its (one-time) script there.

       If your layout uses `@yield('scripts')` instead of stacks (a few
       of your other Laravel views do), either:
         a) swap that yield for `@stack('scripts')` in the layout, or
         b) delete the @push/@endpush block below and paste the
            <script> tag directly before </body> in your layout once.

    That's it — no build step, no npm package. It auto-hooks into:
      - normal <form> submits
      - full page reloads / navigations
      - window.fetch calls
      - XMLHttpRequest calls (this covers jQuery $.ajax too)
      - Livewire v3 navigate events (harmless no-op if you don't use Livewire)

    You can also trigger it manually from your own JS, e.g. around a
    custom async action:

        DotLoader.start();
        // ... do the thing ...
        DotLoader.stop();

    RESIZE / RESTYLE
    ----------------
    All visual knobs are props (or just override the CSS vars yourself):

        <x-dot-loader
            size="8px"          {{-- dot diameter --}}
           {{--  color="#22c55e"     {{-- default/fallback color for all dots --}}
           {{--  gap="12px"          {{-- spacing between dots at rest --}}
          {{--   radius="14px"       {{-- circle radius while spinning --}}
         {{--    morph=".3s"         {{-- speed of row <-> circle transition --}}
         {{--    spin=".7s"          {{-- speed of one full rotation --}}
{{-- />

    To give each of the 3 dots its own color, use color1 / color2 / color3
    (each falls back to `color` above if you leave it out):

        <x-dot-loader color1="#ef4444" color2="#22c55e" color3="#3b82f6" />

    Multiple instances on the same page are fine — each has its own
    props/CSS vars, and the script only ever attaches itself once.
    Calling DotLoader.start()/stop() (or a real request firing) will
    animate every instance on the page at once. If you need independent
    per-instance control (e.g. one spinner per row in a table), give
    each one a unique `id` prop and target it directly, e.g.:

        document.getElementById('row-42-loader').classList.add('is-active');
--}}

@props([
    'size' => '10px',
    'color' => 'currentColor',
    'color1' => '#A8E6CF',
    'color2' => '#F8C8DC',
    'color3' => '#2D3748',
    'gap' => '14px',
    'radius' => '12px',
    'morph' => '.35s',
    'spin' => '.8s',
    'id' => null,
])

<span
    {{ $attributes->merge(['class' => 'dot-loader']) }}
    @if($id) id="{{ $id }}" @endif
    role="status"
    aria-live="polite"
    aria-label="Loading"
    style="
        --dl-size: {{ $size }};
        --dl-color: {{ $color }};
        @if($color1) --dl-color-1: {{ $color1 }}; @endif
        @if($color2) --dl-color-2: {{ $color2 }}; @endif
        @if($color3) --dl-color-3: {{ $color3 }}; @endif
        --dl-gap: {{ $gap }};
        --dl-radius: {{ $radius }};
        --dl-morph: {{ $morph }};
        --dl-spin: {{ $spin }};
    "
>
    <span class="dot-loader__ring">
        <span class="dot-loader__dot"></span>
        <span class="dot-loader__dot"></span>
        <span class="dot-loader__dot"></span>
    </span>
</span>

@once
    @push('scripts')
    <style>
        .dot-loader {
            --dl-size: 10px;
            --dl-color: currentColor;
            --dl-gap: 14px;
            --dl-radius: 12px;
            --dl-morph: .35s;
            --dl-spin: .8s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 0;
        }

        .dot-loader__ring {
            position: relative;
            display: block;
            width: calc(var(--dl-radius) * 2 + var(--dl-size));
            height: calc(var(--dl-radius) * 2 + var(--dl-size));
        }

        .dot-loader.is-active .dot-loader__ring {
            animation: dl-spin var(--dl-spin) linear infinite;
        }

        .dot-loader__dot {
            position: absolute;
            top: 50%;
            left: 50%;
            width: var(--dl-size);
            height: var(--dl-size);
            margin: calc(var(--dl-size) / -2);
            border-radius: 50%;
            transition: transform var(--dl-morph) cubic-bezier(.4, 0, .2, 1);
        }

        /* each dot falls back to the shared --dl-color unless its own is set */
        .dot-loader__dot:nth-child(1) { background: var(--dl-color-1, var(--dl-color)); }
        .dot-loader__dot:nth-child(2) { background: var(--dl-color-2, var(--dl-color)); }
        .dot-loader__dot:nth-child(3) { background: var(--dl-color-3, var(--dl-color)); }

        /* resting state: a row of three dots */
        .dot-loader__dot:nth-child(1) { transform: translate(calc(var(--dl-gap) * -1), 0); }
        .dot-loader__dot:nth-child(2) { transform: translate(0, 0); }
        .dot-loader__dot:nth-child(3) { transform: translate(var(--dl-gap), 0); }

        /* active state: three points 120deg apart on a circle */
        .dot-loader.is-active .dot-loader__dot:nth-child(1) {
            transform: translate(0, calc(var(--dl-radius) * -1));
        }
        .dot-loader.is-active .dot-loader__dot:nth-child(2) {
            transform: translate(calc(var(--dl-radius) * 0.866), calc(var(--dl-radius) * 0.5));
        }
        .dot-loader.is-active .dot-loader__dot:nth-child(3) {
            transform: translate(calc(var(--dl-radius) * -0.866), calc(var(--dl-radius) * 0.5));
        }

        @keyframes dl-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            .dot-loader.is-active .dot-loader__ring { animation: none; }
            .dot-loader__dot { transition: none; }
        }

        /* Optional full-page overlay wrapper: <div class="dot-loader-overlay">...<x-dot-loader/>...</div> */
        .dot-loader-overlay {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .6);
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }
        .dot-loader-overlay.is-visible {
            opacity: 1;
            pointer-events: all;
        }
        .dark .dot-loader-overlay {
            background: rgba(15, 23, 42, .6);
        }
    </style>

    <script>
        (function () {
            if (window.DotLoader) return; // guard against duplicate init

            var ACTIVE = 'is-active';
            var pending = 0;

            function nodes() {
                return document.querySelectorAll('.dot-loader');
            }

            function overlays() {
                return document.querySelectorAll('.dot-loader-overlay');
            }

            function paint() {
                var isOn = pending > 0;
                nodes().forEach(function (el) { el.classList.toggle(ACTIVE, isOn); });
                overlays().forEach(function (el) { el.classList.toggle('is-visible', isOn); });
            }

            function start() {
                pending++;
                paint();
            }

            function stop(force) {
                pending = force ? 0 : Math.max(0, pending - 1);
                paint();
            }

            window.DotLoader = { start: start, stop: stop };

            // Full page reload / navigation away from this page
            window.addEventListener('beforeunload', function () { start(); });

            // Normal (non-AJAX) form submits
            document.addEventListener('submit', function (e) {
                if (e.defaultPrevented) return;
                start();
            }, true);

            // fetch()
            var nativeFetch = window.fetch;
            if (nativeFetch) {
                window.fetch = function () {
                    start();
                    return nativeFetch.apply(this, arguments).finally(function () { stop(); });
                };
            }

            // XMLHttpRequest (covers jQuery $.ajax too)
            var nativeOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function () {
                this.addEventListener('loadstart', function () { start(); });
                this.addEventListener('loadend', function () { stop(); });
                return nativeOpen.apply(this, arguments);
            };

            // Livewire v3 (no-op if Livewire isn't present)
            document.addEventListener('livewire:navigating', function () { start(); });
            document.addEventListener('livewire:navigated', function () { stop(true); });
        })();
    </script>
    @endpush
@endonce