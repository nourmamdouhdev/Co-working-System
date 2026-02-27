<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!is_int($userId) && !ctype_digit((string) $userId)) {
            return null;
        }

        $user = User::findById((int) $userId);
        if (!$user || (int) $user['is_active'] !== 1) {
            self::logout();
            return null;
        }

        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $user): void
    {
        $_SESSION['user_id'] = (int) $user['id'];
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
    }

    public static function requireLogin(array $roles = []): array
    {
        $user = self::user();
        if ($user === null) {
            flash('error', 'Please login first.');
            redirect('/login');
        }

        if ($roles !== [] && !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }

        return $user;
    }
}
