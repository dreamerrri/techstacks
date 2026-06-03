<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sync existing users to RBAC roles based on their old role field
        $users = User::all();
        
        foreach ($users as $user) {
            $role = Role::where('slug', $user->role)->first();
            if ($role) {
                // Check if user already has this role assigned
                $hasRole = $user->roles()->where('role_id', $role->id)->exists();
                if (!$hasRole) {
                    $user->roles()->attach($role->id);
                }
            }
        }
    }

    public function down(): void
    {
        // Remove all role_user assignments
        Schema::table('role_user', function (Blueprint $table) {
            $table->truncate();
        });
    }
};
