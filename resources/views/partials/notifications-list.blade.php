<div style="max-height:380px; overflow-y:auto;">
    @if($notifications->count() > 0)
        @foreach($notifications as $notification)
            <div style="display:flex; align-items:center; gap:12px; padding:11px 16px; border-bottom:1px solid #f3f4f6; background:{{ $notification->is_read ? '#f9fafb' : 'white' }}; transition:background 0.15s; cursor:pointer;"
                 onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='{{ $notification->is_read ? '#f9fafb' : 'white' }}'"
                 onclick="markAsRead({{ $notification->id }}, '{{ $notification->link ?? '#' }}'); return false;">
                <div style="width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:{{ match($notification->type) {
                    'alert' => '#fee2e2',
                    'error' => '#fee2e2',
                    'warning' => '#fef3c7',
                    'success' => '#d1fae5',
                    'info' => '#e0f2fe',
                    default => '#f3f4f6'
                } }};">
                    <i class="fas {{ match($notification->type) {
                    'alert' => 'fa-exclamation-triangle',
                    'error' => 'fa-times-circle',
                    'warning' => 'fa-exclamation-circle',
                    'success' => 'fa-check-circle',
                    'info' => 'fa-info-circle',
                    default => 'fa-bell'
                } }}" style="font-size:14px; color:{{ match($notification->type) {
                    'alert' => '#dc2626',
                    'error' => '#dc2626',
                    'warning' => '#d97706',
                    'success' => '#059669',
                    'info' => '#0891b2',
                    default => '#6b7280'
                } }};"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:{{ $notification->is_read ? '400' : '600' }}; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $notification->title }}</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:1px;">{{ $notification->message }}</div>
                </div>
                <button onclick="event.stopPropagation(); markAsResolved({{ $notification->id }}); return false;"
                        style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:4px; font-size:12px; flex-shrink:0;"
                        title="Mark as resolved">
                    <i class="icon-[ph--check-fill]"></i>
                </button>
            </div>
        @endforeach
        <div style="padding:10px 16px; border-top:1px solid #f3f4f6; text-align:center;">
            <button onclick="markAllAsResolved(); return false;"
                    style="background:#f3f4f6; border:none; color:#374151; font-size:11px; font-weight:600; padding:6px 12px; border-radius:20px; cursor:pointer;">
                Mark all as resolved
            </button>
        </div>
    @else
        <div style="padding:32px 16px; text-align:center;">
            <div style="width:44px; height:44px; border-radius:12px; background:#d1fae5; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                <i class="icon-[ph--check-fill]" style="font-size:18px; color:#059669;"></i>
            </div>
            <div style="font-size:13px; font-weight:600; color:#111827; margin-bottom:4px;">All caught up</div>
            <div style="font-size:12px; color:#9ca3af;">No pending actions right now</div>
        </div>
    @endif
</div>

<script>
function markAsRead(notificationId, link) {
    console.log('Marking notification as read:', notificationId);
    const url = '/notifications/' + notificationId + '/mark-read';
    console.log('Request URL:', url);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    }).then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            console.error('Failed to mark as read:', response.status);
            return response.json().then(err => { throw err; });
        }
        return response.json();
    }).then(data => {
        console.log('Success:', data);
        // Update counter immediately
        const mobileCounter = document.getElementById('notifCountMobile');
        const desktopCounter = document.getElementById('notifCountDesktop');
        console.log('Mobile counter:', mobileCounter);
        console.log('Desktop counter:', desktopCounter);
        
        [mobileCounter, desktopCounter].forEach(el => {
            if (!el) return;
            let currentCount = parseInt(el.textContent);
            if (el.textContent === '9+') currentCount = 10;
            
            if (currentCount > 0) {
                const newCount = currentCount - 1;
                el.textContent = newCount > 9 ? '9+' : newCount;
                if (newCount === 0) {
                    el.style.display = 'none';
                }
            }
        });

        if (link && link !== '#') {
            window.location.href = link;
        } else {
            location.reload();
        }
    }).catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAsResolved(notificationId) {
    console.log('Marking notification as resolved:', notificationId);
    const url = '/notifications/' + notificationId + '/mark-resolved';
    console.log('Request URL:', url);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    }).then(response => {
        console.log('Response status:', response.status);
        if (response.ok) {
            location.reload();
        } else {
            console.error('Failed to mark as resolved:', response.status);
        }
    }).catch(error => {
        console.error('Error marking notification as resolved:', error);
    });
}

function markAllAsResolved() {
    console.log('Marking all notifications as resolved');
    const url = '/notifications/mark-all-resolved';
    console.log('Request URL:', url);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    }).then(response => {
        console.log('Response status:', response.status);
        if (response.ok) {
            location.reload();
        } else {
            console.error('Failed to mark all as resolved:', response.status);
        }
    }).catch(error => {
        console.error('Error marking all as resolved:', error);
    });
}
</script>
