<div style="max-height:380px; overflow-y:auto;">
    @if($notifCount > 0)
        @foreach($notifications as $notification)
            <a href="{{ $notification->link ?: '#' }}"
               onclick="markAsRead({{ $notification->id }}, '{{ $notification->link ?: '#' }}'); return false;"
               style="display:flex; align-items:center; gap:12px; padding:11px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; background:white; transition:background 0.15s;"
               onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
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
                    <div style="font-size:13px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $notification->title }}</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:1px;">{{ $notification->message }}</div>
                </div>
                <i class="fas fa-chevron-right" style="font-size:10px; color:#d1d5db; flex-shrink:0;"></i>
            </a>
        @endforeach
    @else
        <div style="padding:32px 16px; text-align:center;">
            <div style="width:44px; height:44px; border-radius:12px; background:#d1fae5; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                <i class="fas fa-check" style="font-size:18px; color:#059669;"></i>
            </div>
            <div style="font-size:13px; font-weight:600; color:#111827; margin-bottom:4px;">All caught up</div>
            <div style="font-size:12px; color:#9ca3af;">No pending actions right now</div>
        </div>
    @endif
</div>

<script>
function markAsRead(notificationId, link) {
    fetch('{{ route('notifications.mark-read', ':id') }}'.replace(':id', notificationId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    }).then(response => {
        if (response.ok) {
            if (link && link !== '#') {
                window.location.href = link;
            } else {
                location.reload();
            }
        }
    });
}
</script>