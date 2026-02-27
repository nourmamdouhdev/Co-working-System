<section class="card receipt">
    <h1>Receipt #<?= e((string) $receipt['payment_id']) ?></h1>
    <p><strong>Paid At:</strong> <?= e((string) $receipt['paid_at']) ?></p>
    <p><strong>Client:</strong> <?= e((string) $receipt['full_name']) ?> (<?= e((string) $receipt['phone']) ?>)</p>
    <p><strong>Visit:</strong> #<?= e((string) $receipt['visit_id']) ?> | <?= e((string) $receipt['check_in_at']) ?> to <?= e((string) $receipt['check_out_at']) ?></p>

    <h2>Time Charges</h2>
    <ul>
        <li>Duration: <?= e((string) $receipt['duration_minutes']) ?> minutes</li>
        <li>Billable Hours: <?= e((string) $receipt['billable_hours']) ?></li>
        <li>Rate: <?= e(format_money((float) $receipt['hourly_rate_snapshot'], $currency)) ?>/hour</li>
        <li>Time Charge: <?= e(format_money((float) $receipt['time_charge'], $currency)) ?></li>
    </ul>

    <h2>Add-ons</h2>
    <?php if ($addons === []): ?>
        <p>No add-on purchases.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($addons as $line): ?>
                    <tr>
                        <td><?= e((string) $line['product_name_snapshot']) ?></td>
                        <td><?= e(format_money((float) $line['unit_price_snapshot'], $currency)) ?></td>
                        <td><?= e((string) $line['qty']) ?></td>
                        <td><?= e(format_money((float) $line['line_total'], $currency)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h2>Payment</h2>
    <p><strong>Method:</strong> <?= e(strtoupper((string) $receipt['method'])) ?></p>
    <p><strong>Add-ons Total:</strong> <?= e(format_money((float) $receipt['addons_total'], $currency)) ?></p>
    <p><strong>Grand Total:</strong> <?= e(format_money((float) $receipt['grand_total'], $currency)) ?></p>
    <p><strong>Received By:</strong> <?= e((string) $receipt['received_by_name']) ?></p>

    <div class="actions">
        <button type="button" onclick="window.print()">Print Receipt</button>
        <a class="button-link" href="<?= e(app_url('/checkout')) ?>">Back to Checkout</a>
    </div>
</section>
