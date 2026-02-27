<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Visit;
use Throwable;

final class StaffController
{
    public function showCheckinForm(array $params = []): void
    {
        Auth::requireLogin(['admin', 'staff']);

        render('staff/checkin', [
            'title' => 'Client Check-In',
            'hourlyRate' => Setting::hourlyRate(),
            'currency' => Setting::currency(),
        ]);
    }

    public function checkin(array $params = []): void
    {
        $user = Auth::requireLogin(['admin', 'staff']);

        $fullName = trim((string) request_input('full_name', ''));
        $phone = normalize_phone((string) request_input('phone', ''));

        with_old_input([
            'full_name' => $fullName,
            'phone' => $phone,
        ]);

        if ($fullName === '' || mb_strlen($fullName) > 120) {
            flash('error', 'Please enter a valid full name (max 120 chars).');
            redirect('/checkin');
        }

        if ($phone === '' || mb_strlen($phone) < 6 || mb_strlen($phone) > 20) {
            flash('error', 'Please enter a valid phone number.');
            redirect('/checkin');
        }

        try {
            Database::transaction(function () use ($fullName, $phone, $user): void {
                $client = Client::findByPhone($phone);

                if ($client === null) {
                    $clientId = Client::create($fullName, $phone);
                } else {
                    $clientId = (int) $client['id'];
                    if (trim((string) $client['full_name']) !== $fullName) {
                        Client::updateName($clientId, $fullName);
                    }
                }

                Client::lockById($clientId);
                $activeVisit = Visit::findActiveByClient($clientId);
                if ($activeVisit !== null) {
                    throw new \RuntimeException('This client already has an active session.');
                }

                $visitId = Visit::createActive($clientId, (int) $user['id'], Setting::hourlyRate());

                AuditLog::log((int) $user['id'], 'checkin_created', 'visits', $visitId, [
                    'client_id' => $clientId,
                    'phone' => $phone,
                ]);
            });

            clear_old_input();
            flash('success', 'Client checked in successfully.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        redirect('/checkin');
    }

    public function listActiveVisits(array $params = []): void
    {
        Auth::requireLogin(['admin', 'staff']);

        render('staff/active_visits', [
            'title' => 'Active Visits',
            'currency' => Setting::currency(),
            'activeVisits' => Visit::listActive(),
        ]);
    }

    public function showCheckout(array $params = []): void
    {
        Auth::requireLogin(['admin', 'staff']);

        $query = trim((string) request_input('q', ''));
        $selectedVisitId = (int) request_input('visit_id', 0);

        $results = $query !== '' ? Client::searchByNameOrPhone($query) : [];
        $activeVisits = Visit::listActive();
        $selectedVisit = null;

        if ($selectedVisitId > 0) {
            $visit = Visit::findByIdWithClient($selectedVisitId);
            if ($visit && $visit['status'] === 'active') {
                $selectedVisit = $visit;
            }
        }

        $durationMinutes = 0;
        $billableHours = 0;
        $timeCharge = 0.0;

        if ($selectedVisit !== null) {
            $durationMinutes = Visit::computeDurationMinutes((string) $selectedVisit['check_in_at']);
            $billableHours = max(1, (int) ceil($durationMinutes / 60));
            $timeCharge = $billableHours * (float) $selectedVisit['hourly_rate_snapshot'];
        }

        render('staff/checkout', [
            'title' => 'Client Checkout',
            'currency' => Setting::currency(),
            'query' => $query,
            'results' => $results,
            'activeVisits' => $activeVisits,
            'selectedVisit' => $selectedVisit,
            'products' => Product::listActive(),
            'durationMinutes' => $durationMinutes,
            'billableHours' => $billableHours,
            'timeCharge' => $timeCharge,
        ]);
    }

    public function finalizeCheckout(array $params): void
    {
        $user = Auth::requireLogin(['admin', 'staff']);

        $visitId = isset($params['visit_id']) ? (int) $params['visit_id'] : 0;
        $paymentMethod = strtolower(trim((string) request_input('payment_method', '')));
        $qtyInput = request_input('qty', []);

        if ($visitId <= 0) {
            flash('error', 'Invalid visit ID.');
            redirect('/checkout');
        }

        if (!in_array($paymentMethod, ['cash', 'visa'], true)) {
            flash('error', 'Payment method must be cash or visa.');
            redirect('/checkout/search?visit_id=' . $visitId);
        }

        $quantities = [];
        if (is_array($qtyInput)) {
            foreach ($qtyInput as $productId => $qty) {
                $productId = (int) $productId;
                $qty = (int) $qty;
                if ($productId > 0 && $qty > 0) {
                    $quantities[$productId] = $qty;
                }
            }
        }

        try {
            $paymentId = Database::transaction(function () use ($visitId, $paymentMethod, $quantities, $user): int {
                $visit = Visit::findActiveByIdForUpdate($visitId);
                if ($visit === null) {
                    throw new \RuntimeException('Visit is not active or already closed.');
                }

                if (Payment::existsForVisit($visitId)) {
                    throw new \RuntimeException('Payment already exists for this visit.');
                }

                $durationMinutes = Visit::computeDurationMinutes((string) $visit['check_in_at']);
                $billableHours = max(1, (int) ceil($durationMinutes / 60));
                $timeCharge = $billableHours * (float) $visit['hourly_rate_snapshot'];

                $productsById = Product::findActiveByIds(array_keys($quantities));
                $addonsTotal = 0.0;
                $addonLines = [];

                foreach ($quantities as $productId => $qty) {
                    if (!isset($productsById[$productId])) {
                        throw new \RuntimeException('Invalid or inactive product selected.');
                    }

                    $product = $productsById[$productId];
                    $unitPrice = (float) $product['unit_price'];
                    $lineTotal = $unitPrice * $qty;
                    $addonsTotal += $lineTotal;

                    $addonLines[] = [
                        'product_id' => $productId,
                        'name' => (string) $product['name'],
                        'unit_price' => $unitPrice,
                        'qty' => $qty,
                        'line_total' => $lineTotal,
                    ];
                }

                $grandTotal = $timeCharge + $addonsTotal;

                Visit::closeVisit($visitId, $durationMinutes, $billableHours, $timeCharge, $addonsTotal, $grandTotal);

                foreach ($addonLines as $line) {
                    Visit::addAddonLine(
                        $visitId,
                        $line['product_id'],
                        $line['name'],
                        $line['unit_price'],
                        $line['qty'],
                        $line['line_total']
                    );
                }

                $paymentId = Payment::create($visitId, $paymentMethod, $grandTotal, (int) $user['id']);

                AuditLog::log((int) $user['id'], 'checkout_finalized', 'visits', $visitId, [
                    'payment_id' => $paymentId,
                    'method' => $paymentMethod,
                    'grand_total' => $grandTotal,
                ]);

                return $paymentId;
            });

            flash('success', 'Checkout completed successfully.');
            redirect('/receipt/' . $paymentId);
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
            redirect('/checkout/search?visit_id=' . $visitId);
        }
    }
}
