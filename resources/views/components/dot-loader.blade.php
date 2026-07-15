@props([
    'size' => '10px',
    'color' => 'currentColor',
    'color1' => 'var(--color-dot-1, var(--color-success))',
    'color2' => 'var(--color-dot-2, var(--color-warning))',
    'color3' => 'var(--color-dot-3, var(--color-neutral))',
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
            background: color-mix(in srgb, var(--color-base-100) 60%, transparent);
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }
        .dot-loader-overlay.is-visible {
            opacity: 1;
            pointer-events: all;
        }
    </style>

    <script>
        (function () {
    if (window.DotLoader) return; // guard against duplicate init

    var ACTIVE = 'is-active';
    var VISIBLE = 'is-visible';
    var STORAGE_KEY = 'dot-loader:navigating';
    var MIN_VISIBLE_MS = 400; // don't let the morph blip by unnoticed

    var pending = 0;
    var activatedAt = 0;

    function nodes() {
        return document.querySelectorAll('.dot-loader');
    }

    function overlays() {
        return document.querySelectorAll('.dot-loader-overlay');
    }

    function paint(isOn) {
        nodes().forEach(function (el) { el.classList.toggle(ACTIVE, isOn); });
        overlays().forEach(function (el) { el.classList.toggle(VISIBLE, isOn); });
    }

    function start() {
        pending++;
        if (pending === 1) activatedAt = Date.now();
        paint(true);
    }

    function finishStop(force) {
        pending = force ? 0 : Math.max(0, pending - 1);
        if (pending === 0) paint(false);
    }

    function stop(force) {
        var elapsed = Date.now() - activatedAt;
        var wait = Math.max(0, MIN_VISIBLE_MS - elapsed);
        if (wait === 0) {
            finishStop(force);
        } else {
            setTimeout(function () { finishStop(force); }, wait);
        }
    }

    window.DotLoader = { start: start, stop: stop };

    if (sessionStorage.getItem(STORAGE_KEY)) {
        sessionStorage.removeItem(STORAGE_KEY);
        pending = 1;
        activatedAt = Date.now();
        paint(true);
        window.addEventListener('load', function () { stop(true); });
    }

    window.addEventListener('beforeunload', function () {
        sessionStorage.setItem(STORAGE_KEY, '1');
        start();
    });

    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        start();
    }, true);

    var nativeFetch = window.fetch;
    if (nativeFetch) {
        window.fetch = function () {
            start();
            return nativeFetch.apply(this, arguments).finally(function () { stop(); });
        };
    }

    var nativeOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function () {
        this.addEventListener('loadstart', function () { start(); });
        this.addEventListener('loadend', function () { stop(); });
        return nativeOpen.apply(this, arguments);
    };

    document.addEventListener('livewire:navigating', function () { start(); });
    document.addEventListener('livewire:navigated', function () { stop(true); });

    if (window.MutationObserver) {
        var overlayObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                var el = m.target;
                if (!el.classList || !el.classList.contains('overlay')) return;

                var oldClasses = (m.oldValue || '').split(' ');
                var wasOpen = oldClasses.indexOf('open') !== -1;
                var isOpen = el.classList.contains('open');

                if (isOpen && !wasOpen) start();
                if (!isOpen && wasOpen) stop();
            });
        });

        overlayObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
            attributeOldValue: true,
            subtree: true,
        });
    }
})();
    </script>
@endonce