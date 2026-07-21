<div class="max-h-[380px] overflow-y-auto">
    <div id="notif-status" class="sr-only" role="status" aria-live="polite"></div>

<ul id="notif-list" role="list" class="list-none divide-y divide-base-300{{ $notifCount > 0 ? '' : ' hidden' }}">
            @foreach($notifications as $notification)
            @php
                $style = match ($notification->type) {
                    'alert' => ['bg' => 'bg-error/10', 'text' => 'text-error', 'icon' => 'icon-[ph--warning-fill]', 'label' => 'Alert'],
                    'error' => ['bg' => 'bg-error/10', 'text' => 'text-error', 'icon' => 'icon-[tabler--circle-x]', 'label' => 'Error'],
                    'warning' => ['bg' => 'bg-warning/10', 'text' => 'text-warning', 'icon' => 'icon-[ph--warning-circle-fill]', 'label' => 'Warning'],
                    'success' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'icon' => 'icon-[tabler--circle-check]', 'label' => 'Success'],
                    'info' => ['bg' => 'bg-info/10', 'text' => 'text-info', 'icon' => 'icon-[ph--info-fill]', 'label' => 'Info'],
                    default => ['bg' => 'bg-base-300', 'text' => 'text-base-content/50', 'icon' => 'icon-[ph--bell-fill]', 'label' => 'Notification'],
                };
            @endphp
            <li role="listitem" data-notif-id="{{ $notification->id }}">
                <a href="{{ $notification->link ?: '#' }}"
                   onclick="markAsRead(event, {{ $notification->id }})"
                   class="group flex items-center gap-3 bg-base-100 px-4 py-[11px] no-underline outline-none transition-colors duration-150 hover:bg-base-200 focus-visible:bg-base-200 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary/30">
                    <div class="flex h-[34px] w-[34px] flex-shrink-0 items-center justify-center rounded-[9px] {{ $style['bg'] }}">
                        <i class="{{ $style['icon'] }} {{ $style['text'] }} text-sm" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[13px] font-semibold text-base-content"><span class="sr-only">{{ $style['label'] }}: </span>{{ $notification->title }}</div>
                        <div class="mt-px text-[11px] text-base-content/60">{{ $notification->message }}</div>
                        <div class="mt-0.5 text-[10px] text-base-content/40">{{ $notification->created_at?->diffForHumans() }}</div>
                    </div>
                    <i class="icon-[ph--caret-right-fill] flex-shrink-0 text-[10px] text-base-content/30 group-hover:text-base-content/50" aria-hidden="true"></i>
                </a>
            </li>
        @endforeach
    </ul>

    <div id="notif-empty-state" class="px-4 py-8 text-center{{ $notifCount > 0 ? ' hidden' : '' }}">
        <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-success/10">
            <i class="icon-[tabler--check] text-lg text-success" aria-hidden="true"></i>
        </div>
        <div class="mb-1 text-[13px] font-semibold text-base-content">All caught up</div>
        <div class="text-xs text-base-content/40">No pending actions right now</div>
    </div>
</div>

<script>
async function markAsRead(event, notificationId) {
    event.preventDefault();

    const item = document.querySelector(`[data-notif-id="${notificationId}"]`);
    if (!item || item.dataset.processing === 'true') return; // ignore repeat clicks mid-request
    item.dataset.processing = 'true';

    const href = event.currentTarget.getAttribute('href');
    const link = href && href !== '#' ? href : null;
    const url = '{{ route('notifications.mark-read', ':id') }}'.replace(':id', notificationId);

    const markReadRequest = fetch(url, {
        method: 'POST',
        keepalive: true, // let the request finish in the background even if we navigate away below
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    });

    if (link) {
        // Don't make the click wait on a network round trip before navigating.
        markReadRequest.catch(err => console.error('Failed to mark notification as read:', err));
        window.location.href = link;
        return;
    }

    try {
        const response = await markReadRequest;
        if (response.ok) {
            removeNotificationFromList(notificationId);
        } else {
            item.dataset.processing = 'false';
        }
    } catch (err) {
        console.error('Failed to mark notification as read:', err);
        item.dataset.processing = 'false';
    }
}

function removeNotificationFromList(notificationId) {
    const item = document.querySelector(`[data-notif-id="${notificationId}"]`);
    if (!item) return;
    const list = item.parentElement;

    // Collapse the row smoothly instead of yanking it out or reloading the whole page.
    item.style.transition = 'max-height 250ms ease, opacity 200ms ease, padding 250ms ease';
    item.style.maxHeight = item.scrollHeight + 'px';
    item.style.overflow = 'hidden';

    requestAnimationFrame(() => {
        item.style.maxHeight = '0px';
        item.style.opacity = '0';
        item.style.paddingTop = '0px';
        item.style.paddingBottom = '0px';
    });

    item.addEventListener('transitionend', () => {
        item.remove();

        // Lets a bell-icon badge elsewhere on the page react without being tightly coupled to this file.
        document.dispatchEvent(new CustomEvent('notification:read', { detail: { id: notificationId } }));

        const status = document.getElementById('notif-status');
        if (status) status.textContent = 'Notification marked as read.';

        if (list && list.children.length === 0) {
            list.classList.add('hidden');
            document.getElementById('notif-empty-state')?.classList.remove('hidden');
        }
    }, { once: true });
}
</script>