<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $role = $user?->role === 'admin' ? 'admin' : ($user?->role === 'hr' ? 'hr' : 'user');

        $notifications = $user
            ? \App\Models\Notification::forCurrentUser()
                ->where(function ($q) {
                    $q->where('is_read', false)
                      ->orWhere('is_resolved', false);
                })
                ->latest()
                ->limit(50)
                ->get()
            : collect();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'role'          => $user->role,
                    'profile_photo' => $user->profile_photo,
                    'photo_url'     => $user->photo_url,
                    'theme'         => $user->theme ?? 'techstacks',
                    'is_active'     => $user->is_active,
                    'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),
                    'created_at'    => $user->created_at?->format('Y-m-d H:i:s'),
                    'employee'      => $user->employee ? [
                        'department'        => $user->employee->department,
                        'position'          => $user->employee->position,
                        'employment_status' => $user->employee->employment_status,
                        'date_hired'        => $user->employee->date_hired,
                    ] : null,
                ] : null,
            ],
            'role' => $role,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info'    => fn () => $request->session()->get('info'),
            ],
            'notifications' => $notifications,
            'notifCount'    => $user ? \App\Models\Notification::forCurrentUser()->unread()->count() : 0,
        ];
    }
}