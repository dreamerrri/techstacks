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
        'user_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
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
        $this->update(['is_read' => true]);
    }

    public static function createForEmployee(array $data): self
    {
        return self::create(array_merge($data, ['audience_type' => 'employee']));
    }

    public static function createForHrAdmin(array $data): self
    {
        return self::create(array_merge($data, ['audience_type' => 'hr_admin']));
    }

    public static function createForAll(array $data): self
    {
        return self::create(array_merge($data, ['audience_type' => 'all']));
    }
}
