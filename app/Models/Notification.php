<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'audience_type',
        'link',
        'is_read',
        'is_resolved',
        'user_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_resolved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForEmployee($query)
    {
        return $query->where('audience_type', 'employee');
    }

    public function scopeForHrAdmin($query)
    {
        return $query->where('audience_type', 'hr_admin');
    }

    public function scopeForAll($query)
    {
        return $query->where('audience_type', 'all');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeForCurrentUser($query)
    {
        $user = auth()->user();
        if (!$user) {
            return $query->whereNull('id');
        }

        if ($user->role === 'admin' || $user->role === 'hr') {
            return $query->where(function ($q) {
                $q->where('audience_type', 'hr_admin')
                  ->orWhere('audience_type', 'all')
                  ->orWhere('user_id', auth()->id());
            });
        }

        return $query->where(function ($q) {
            $q->where('audience_type', 'all')

              ->orWhere('user_id', auth()->id());
        });
    }

    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->save();
    }

    public function markAsResolved(): void
    {
        $this->update(['is_resolved' => true]);
    }

    public static function createForEmployee(array $data): self
    {
        $notification = self::create(array_merge($data, ['audience_type' => 'employee']));
        if (!empty($data['user_id'])) {
            self::clearUnreadCountsForUserIds([(int) $data['user_id']]);
        }
        return $notification;
    }

    public static function createForHrAdmin(array $data): self
    {
        $notification = self::create(array_merge($data, ['audience_type' => 'hr_admin']));
        self::clearUnreadCountsForRoles(['admin', 'hr']);
        return $notification;
    }

    public static function createForAll(array $data): self
    {
        $notification = self::create(array_merge($data, ['audience_type' => 'all']));
        self::clearUnreadCountsForUserIds(\App\Models\User::pluck('id')->all());
        return $notification;
    }

    /**
     * Invalidate cached unread-badge counts (see User::cachedUnreadNotificationsCount).
     * Creation is rare compared to page loads, so the extra queries here are cheap.
     */
    private static function clearUnreadCountsForUserIds(array $userIds): void
    {
        if (!$userIds) {
            return;
        }
        \App\Models\User::query()->whereIn('id', $userIds)->get()
            ->each->clearUnreadNotificationsCountCache();
    }

    private static function clearUnreadCountsForRoles(array $roles): void
    {
        self::clearUnreadCountsForUserIds(
            \App\Models\User::query()->whereIn('role', $roles)->pluck('id')->all()
        );
    }
}
