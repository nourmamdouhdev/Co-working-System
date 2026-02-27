<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Visit;
use Throwable;

final class AdminController
{
    public function dailyDashboard(array $params = []): void
    {
        Auth::requireLogin(['admin']);

        $date = (string) request_input('date', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        render('admin/dashboard_daily', [
            'title' => 'Daily Dashboard',
            'currency' => Setting::currency(),
            'reportDate' => $date,
            'summary' => Visit::dailySummary($date),
            'closedVisits' => Visit::listClosedByDate($date),
        ]);
    }

    public function staff(array $params = []): void
    {
        $currentUser = Auth::requireLogin(['admin']);

        if (request_method() === 'POST') {
            $action = (string) request_input('action', '');

            try {
                if ($action === 'create') {
                    $this->createStaff((int) $currentUser['id']);
                } elseif ($action === 'toggle') {
                    $this->toggleStaff((int) $currentUser['id'], (int) $currentUser['id']);
                } else {
                    throw new \RuntimeException('Invalid staff action.');
                }
                flash('success', 'Staff action completed.');
            } catch (Throwable $exception) {
                flash('error', $exception->getMessage());
            }

            redirect('/admin/staff');
        }

        render('admin/staff', [
            'title' => 'Staff Management',
            'users' => User::listAll(),
        ]);
    }

    public function products(array $params = []): void
    {
        $currentUser = Auth::requireLogin(['admin']);

        if (request_method() === 'POST') {
            $action = (string) request_input('action', '');

            try {
                if ($action === 'create') {
                    $name = trim((string) request_input('name', ''));
                    $unitPrice = (float) request_input('unit_price', 0);

                    if ($name === '' || mb_strlen($name) > 120) {
                        throw new \RuntimeException('Product name is required (max 120 chars).');
                    }
                    if ($unitPrice < 0) {
                        throw new \RuntimeException('Unit price must be 0 or more.');
                    }

                    $productId = Product::create($name, $unitPrice);
                    AuditLog::log((int) $currentUser['id'], 'product_created', 'products', $productId, [
                        'name' => $name,
                        'unit_price' => $unitPrice,
                    ]);
                } elseif ($action === 'toggle') {
                    $productId = (int) request_input('product_id', 0);
                    $isActive = (int) request_input('is_active', 0) === 1;
                    if ($productId <= 0) {
                        throw new \RuntimeException('Invalid product ID.');
                    }

                    Product::updateActiveStatus($productId, !$isActive);
                    AuditLog::log((int) $currentUser['id'], 'product_toggled', 'products', $productId, [
                        'new_is_active' => !$isActive,
                    ]);
                } else {
                    throw new \RuntimeException('Invalid product action.');
                }

                flash('success', 'Product action completed.');
            } catch (Throwable $exception) {
                flash('error', $exception->getMessage());
            }

            redirect('/admin/products');
        }

        render('admin/products', [
            'title' => 'Product Management',
            'products' => Product::listAll(),
            'currency' => Setting::currency(),
        ]);
    }

    public function settings(array $params = []): void
    {
        $currentUser = Auth::requireLogin(['admin']);

        if (request_method() === 'POST') {
            $hourlyRate = (float) request_input('hourly_rate', 0);
            $currency = strtoupper(trim((string) request_input('currency', 'USD')));
            $timezone = trim((string) request_input('timezone', 'UTC'));

            try {
                if ($hourlyRate <= 0) {
                    throw new \RuntimeException('Hourly rate must be greater than 0.');
                }

                if ($currency === '' || mb_strlen($currency) > 10) {
                    throw new \RuntimeException('Currency is required (max 10 chars).');
                }

                if (!in_array($timezone, timezone_identifiers_list(), true)) {
                    throw new \RuntimeException('Invalid timezone.');
                }

                Setting::setMany([
                    'hourly_rate' => number_format($hourlyRate, 2, '.', ''),
                    'currency' => $currency,
                    'timezone' => $timezone,
                ]);

                AuditLog::log((int) $currentUser['id'], 'settings_updated', 'settings', null, [
                    'hourly_rate' => $hourlyRate,
                    'currency' => $currency,
                    'timezone' => $timezone,
                ]);

                date_default_timezone_set($timezone);
                flash('success', 'Settings updated.');
            } catch (Throwable $exception) {
                flash('error', $exception->getMessage());
            }

            redirect('/admin/settings');
        }

        $settings = Setting::all();

        render('admin/settings', [
            'title' => 'System Settings',
            'settings' => [
                'hourly_rate' => $settings['hourly_rate'] ?? '10.00',
                'currency' => $settings['currency'] ?? 'USD',
                'timezone' => $settings['timezone'] ?? date_default_timezone_get(),
            ],
        ]);
    }

    private function createStaff(int $actorId): void
    {
        $name = trim((string) request_input('name', ''));
        $username = trim((string) request_input('username', ''));
        $password = (string) request_input('password', '');
        $role = (string) request_input('role', 'staff');

        if ($name === '' || mb_strlen($name) > 120) {
            throw new \RuntimeException('Name is required (max 120 chars).');
        }

        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
            throw new \RuntimeException('Username must be 3-50 chars: letters, numbers, _, ., -');
        }

        if (mb_strlen($password) < 6) {
            throw new \RuntimeException('Password must be at least 6 chars.');
        }

        if (!in_array($role, ['admin', 'staff'], true)) {
            throw new \RuntimeException('Invalid role.');
        }

        if (User::findByUsername($username) !== null) {
            throw new \RuntimeException('Username already exists.');
        }

        $userId = User::create($name, $username, password_hash($password, PASSWORD_DEFAULT), $role);

        AuditLog::log($actorId, 'user_created', 'users', $userId, [
            'username' => $username,
            'role' => $role,
        ]);
    }

    private function toggleStaff(int $actorId, int $currentUserId): void
    {
        $targetUserId = (int) request_input('user_id', 0);
        $isActive = (int) request_input('is_active', 0) === 1;

        if ($targetUserId <= 0) {
            throw new \RuntimeException('Invalid user ID.');
        }

        if ($targetUserId === $currentUserId && $isActive) {
            throw new \RuntimeException('You cannot deactivate your own account.');
        }

        User::updateActiveStatus($targetUserId, !$isActive);

        AuditLog::log($actorId, 'user_toggled', 'users', $targetUserId, [
            'new_is_active' => !$isActive,
        ]);
    }
}
