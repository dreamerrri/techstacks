<button type="button" class="btn btn-text max-sm:btn-square sm:hidden" aria-haspopup="dialog" aria-expanded="false" aria-controls="collapsible-mini-sidebar" data-overlay="#collapsible-mini-sidebar">
  <span class="icon-[tabler--menu-2] size-5"></span>
</button>
<aside id="collapsible-mini-sidebar" class="overlay [--auto-close:sm] overlay-minified:w-17 sm:shadow-none overlay-open:translate-x-0 drawer drawer-start hidden w-66 sm:absolute sm:z-0 sm:flex sm:translate-x-0 border-e border-base-content/20" role="dialog" tabindex="-1">
  <div class="drawer-header overlay-minified:px-3.75 py-2 w-full flex items-center justify-between gap-3">
    <h3 class="drawer-title text-xl font-semibold overlay-minified:hidden">FlyonUI</h3>
    <div class="hidden sm:block">
      <!-- Toggle Button + Tooltip -->
      <div class="tooltip [--placement:right]">
        <button type="button" class="tooltip-toggle btn btn-circle btn-text" aria-haspopup="dialog" aria-expanded="false" aria-controls="collapsible-mini-sidebar" aria-label="Minify navigation" data-overlay-minifier="#collapsible-mini-sidebar">
          <span class="icon-[tabler--menu-2] size-5"></span>
          <span class="sr-only">Navigation Toggle</span>
        </button>
        <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
          <span class="tooltip-body">Minify navigation</span>
        </span>
      </div>
    </div>
  </div>
  <div class="drawer-body px-2 pt-4">
    <ul class="menu p-0">
      <li>
        <a href="#">
          <span class="icon-[tabler--home] size-5"></span>
          <span class="overlay-minified:hidden">Home</span>
        </a>
      </li>
      <li>
        <a href="#">
          <span class="icon-[tabler--user] size-5"></span>
          <span class="overlay-minified:hidden">Account</span>
        </a>
      </li>
      <li>
        <a href="#">
          <span class="icon-[tabler--message] size-5"></span>
          <span class="overlay-minified:hidden">Notifications</span>
        </a>
      </li>
      <li class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
        <button id="dropdown-default" type="button" class="dropdown-toggle" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
          <span class="icon-[tabler--apps] size-5"></span>
          <span class="overlay-minified:hidden">Apps</span>
          <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
        </button>
        <ul class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-60 overlay-minified:before:absolute overlay-minified:before:-start-4 overlay-minified:before:top-0 overlay-minified:before:h-full overlay-minified:before:w-4 before:bg-transparent" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-default">
          <li>
            <a href="#">
              <span class="icon-[tabler--mail] size-5"></span>
              Email
            </a>
          </li>
          <li>
            <a href="#">
              <span class="icon-[tabler--calendar] size-5"></span>
              Calendar
            </a>
          </li>
        </ul>
      </li>
      <li>
        <a href="#">
          <span class="icon-[tabler--shopping-bag] size-5"></span>
          <span class="overlay-minified:hidden">Product</span>
        </a>
      </li>
      <li>
        <a href="#">
          <span class="icon-[tabler--login] size-5"></span>
          <span class="overlay-minified:hidden">Sign In</span>
        </a>
      </li>
      <li>
        <a href="#">
          <span class="icon-[tabler--logout-2] size-5"></span>
          <span class="overlay-minified:hidden">Sign Out</span>
        </a>
      </li>
    </ul>
  </div>
</aside>