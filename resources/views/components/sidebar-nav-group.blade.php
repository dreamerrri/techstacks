{{--
    x-sidebar-nav-group

    One collapsible group in the sidebar (e.g. "Access Control" > Users/Roles/Permissions).
    Wraps FlyonUI's real dropdown component — verified against flyonui's own JS source
    (node_modules/flyonui/src/js/plugins/dropdown/index.ts):

    - The wrapper <li> needs class "open" for the group to render pre-expanded (matches
      the current route) on first paint, with no client JS needed to restore state.
    - The dropdown-menu <ul> must NOT have the "hidden" class in that same case — FlyonUI's
      dropdown-open:* variant (from flyonui/variants.css) only controls opacity/animation,
      "hidden" is the actual display:none toggle and has to be removed separately.
    - aria-expanded is synced by FlyonUI itself on init by reading the "open" class, so we
      just need to set the *initial* value to match for a11y before JS runs.

    In the full-width sidebar this renders as an inline expanding list (via
    [--strategy:static]). Once the sidebar is minified, overlay-minified:[--strategy:fixed]
    switches it to a hover-triggered flyout anchored to the right of the icon rail — same
    behavior the old .overlay-minified .nav-dropdown-menu CSS was hand-rolling, now handled
    by FlyonUI itself.

    Props:
      id    - unique string, used for aria-labelledby wiring (e.g. "dropdown-workforce")
      label - visible group label (e.g. "Workforce")
      icon  - iconify icon name without the "icon-[...]" wrapper (e.g. "ph--wallet-fill")
      open  - bool, whether this group contains the currently active route
      slot  - the <li><a>...</a></li> child links
--}}
@props(['id', 'label', 'icon', 'open' => false])

<li class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start] {{ $open ? 'open' : '' }}">
    <button id="{{ $id }}"
            type="button"
            class="dropdown-toggle"
            aria-haspopup="menu"
            aria-expanded="{{ $open ? 'true' : 'false' }}"
            aria-label="{{ $label }}">
        <span class="icon-[{{ $icon }}] size-5"></span>
        <span class="overlay-minified:hidden">{{ $label }}</span>
        <span class="icon-[ph--caret-down-fill] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
    </button>

    <ul class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 {{ $open ? '' : 'hidden' }} min-w-60 overlay-minified:before:absolute overlay-minified:before:-start-4 overlay-minified:before:top-0 overlay-minified:before:h-full overlay-minified:before:w-4 before:bg-transparent"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="{{ $id }}">
        {{ $slot }}
    </ul>
</li>