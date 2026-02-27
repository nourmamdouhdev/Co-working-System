<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Visit;

final class ReceiptController
{
    public function show(array $params): void
    {
        Auth::requireLogin(['admin', 'staff']);

        $paymentId = isset($params['payment_id']) ? (int) $params['payment_id'] : 0;
        if ($paymentId <= 0) {
            http_response_code(404);
            echo 'Receipt not found.';
            return;
        }

        $receipt = Payment::findReceiptByPaymentId($paymentId);
        if ($receipt === null) {
            http_response_code(404);
            echo 'Receipt not found.';
            return;
        }

        render('receipt/show', [
            'title' => 'Receipt #' . $paymentId,
            'currency' => Setting::currency(),
            'receipt' => $receipt,
            'addons' => Visit::listAddons((int) $receipt['visit_id']),
        ]);
    }
}
