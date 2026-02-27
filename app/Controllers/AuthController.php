<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;

final class AuthController
{
    public function showLogin(array $params = []): void
    {
        if (Auth::check()) {
            redirect('/checkin');
        }

        render('auth/login', [
            'title' => 'Login',
        ]);
    }

    public function login(array $params = []): void
    {
        $username = trim((string) request_input('username', ''));
        $password = (string) request_input('password', '');

        with_old_input(['username' => $username]);

        if ($username === '' || $password === '') {
            flash('error', 'Username and password are required.');
            redirect('/login');
        }

        $user = User::findByUsername($username);
        if (!$user || (int) $user['is_active'] !== 1 || !password_verify($password, (string) $user['password_hash'])) {
            flash('error', 'Invalid credentials.');
            redirect('/login');
        }

        Auth::login($user);
        clear_old_input();
        flash('success', 'Welcome, ' . $user['name'] . '.');
        redirect('/checkin');
    }

    public function logout(array $params = []): void
    {
        Auth::logout();
        flash('success', 'Logged out successfully.');
        redirect('/login');
    }
}
