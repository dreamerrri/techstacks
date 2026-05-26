<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class PasswordValidator
{
    /**
     * Validate password strength
     */
    public static function validate(string $password): array
    {
        $errors = [];
        $strength = 0;

        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } else {
            $strength += 20;
        }

        // Maximum length
        if (strlen($password) > 128) {
            $errors[] = 'Password must not exceed 128 characters';
        }

        // Uppercase letters
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } else {
            $strength += 20;
        }

        // Lowercase letters
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        } else {
            $strength += 20;
        }

        // Numbers
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        } else {
            $strength += 20;
        }

        // Special characters
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $password)) {
            $errors[] = 'Password must contain at least one special character (!@#$%^&*...)';
        } else {
            $strength += 20;
        }

        // Strength level
        $strengthLevel = match (true) {
            $strength < 40 => 'weak',
            $strength < 60 => 'fair',
            $strength < 80 => 'good',
            default => 'strong',
        };

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $strength,
            'level' => $strengthLevel,
        ];
    }

    /**
     * Check if password is commonly used
     */
    public static function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', '123456', '12345678', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', '123123',
            'password1', 'admin123', 'root', 'toor', 'test',
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Hash password
     */
    public static function hash(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Verify password
     */
    public static function verify(string $password, string $hash): bool
    {
        return Hash::check($password, $hash);
    }
}
