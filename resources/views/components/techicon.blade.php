@props(['minified' => false])

<a href="{{ route('dashboard') }}" class="techicon flex items-center gap-2 no-underline {{ $minified ? 'justify-center' : '' }}">
    <svg fill="currentColor" height="2em" viewBox="0 0 1813 1441" width="2em" xmlns="http://www.w3.org/2000/svg" class="brand-logo-icon shrink-0 text-primary">
        <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fill-rule="evenodd"></path>
        <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fill-rule="evenodd"></path>
    </svg>
    <div class="tech drawer-title overlay-minified:hidden leading-tight">
        <span class="block text-xl font-semibold text-primary">Techstacks</span>
        <span class="block text-xs text-primary/60">
            {{ $slot->isEmpty() ? '' : $slot }}
        </span>
    </div>
</a>