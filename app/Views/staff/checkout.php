<section class="card">
    <h1>Client Checkout</h1>

    <form method="GET" action="<?= e(app_url('/checkout/search')) ?>">
        <label for="q">Search by phone or name</label>
        <input id="q" name="q" value="<?= e($query) ?>" placeholder="Phone exact or name partial" required>
        <button type="submit">Search</button>
    </form>

    <?php if ($query !== ''): ?>
        <h2>Search Results</h2>
        <?php if ($results === []): ?>
            <p>No clients found.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Client</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?= e((string) $row['full_name']) ?></td>
                            <td><?= e((string) $row['phone']) ?></td>
                            <td>
                                <?php if (!empty($row['active_visit_id'])): ?>
                                    Active (#<?= e((string) $row['active_visit_id']) ?>)
                                <?php else: ?>
                                    No active visit
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['active_visit_id'])): ?>
                                    <a class="button-link" href="<?= e(app_url('/checkout/search?q=' . urlencode($query) . '&visit_id=' . (int) $row['active_visit_id'])) ?>">Select</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="card">
    <h2>Active Visits</h2>
    <?php if ($activeVisits === []): ?>
        <p>No active visits right now.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Visit</th>
                    <th>Client</th>
                    <th>Phone</th>
                    <th>Check-In</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($activeVisits as $visit): ?>
                    <tr>
                        <td>#<?= e((string) $visit['id']) ?></td>
                        <td><?= e((string) $visit['full_name']) ?></td>
                        <td><?= e((string) $visit['phone']) ?></td>
                        <td><?= e((string) $visit['check_in_at']) ?></td>
                        <td>
                            <a class="button-link" href="<?= e(app_url('/checkout/search?visit_id=' . (int) $visit['id'])) ?>">Select</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($selectedVisit): ?>
<section class="card">
    <h2>Finalize Checkout for Visit #<?= e((string) $selectedVisit['id']) ?></h2>
    <p><strong>Client:</strong> <?= e((string) $selectedVisit['full_name']) ?> (<?= e((string) $selectedVisit['phone']) ?>)</p>
    <p><strong>Check-In:</strong> <?= e((string) $selectedVisit['check_in_at']) ?></p>
    <p><strong>Current Duration:</strong> <?= e((string) $durationMinutes) ?> minutes</p>
    <p><strong>Billable Hours (rounded up):</strong> <?= e((string) $billableHours) ?></p>
    <p><strong>Time Charge:</strong> <?= e(format_money((float) $timeCharge, $currency)) ?></p>

    <form method="POST" action="<?= e(app_url('/checkout/' . (int) $selectedVisit['id'] . '/finalize')) ?>">
        <?= csrf_field() ?>

        <h3>Add-on Purchases</h3>
        <?php if ($products === []): ?>
            <p>No active products configured.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= e((string) $product['name']) ?></td>
                            <td><?= e(format_money((float) $product['unit_price'], $currency)) ?></td>
                            <td>
                                <input type="number" min="0" step="1" name="qty[<?= (int) $product['id'] ?>]" value="0">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <h3>Payment Method</h3>
        <label>
            <input type="radio" name="payment_method" value="cash" checked>
            Cash
        </label>
        <label>
            <input type="radio" name="payment_method" value="visa">
            Visa
        </label>

        <button type="submit">Finalize and Pay</button>
    </form>
</section>
<?php endif; ?>
