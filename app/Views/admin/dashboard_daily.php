<section class="card">
    <h1>Daily Dashboard</h1>

    <form method="GET" action="<?= e(app_url('/admin/dashboard/daily')) ?>">
        <label for="date">Report Date</label>
        <input id="date" type="date" name="date" value="<?= e($reportDate) ?>" required>
        <button type="submit">Load</button>
    </form>

    <div class="stats-grid">
        <article class="stat">
            <h3>Check-Ins</h3>
            <p><?= e((string) $summary['check_ins']) ?></p>
        </article>
        <article class="stat">
            <h3>Check-Outs</h3>
            <p><?= e((string) $summary['check_outs']) ?></p>
        </article>
        <article class="stat">
            <h3>Total Hours</h3>
            <p><?= e(number_format((float) $summary['total_hours'], 0)) ?></p>
        </article>
        <article class="stat">
            <h3>Total Revenue</h3>
            <p><?= e(format_money((float) $summary['total_revenue'], $currency)) ?></p>
        </article>
        <article class="stat">
            <h3>Cash</h3>
            <p><?= e(format_money((float) $summary['cash_revenue'], $currency)) ?></p>
        </article>
        <article class="stat">
            <h3>Visa</h3>
            <p><?= e(format_money((float) $summary['visa_revenue'], $currency)) ?></p>
        </article>
    </div>
</section>

<section class="card">
    <h2>Closed Visits (<?= e($reportDate) ?>)</h2>
    <?php if ($closedVisits === []): ?>
        <p>No closed visits for this date.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Visit</th>
                    <th>Client</th>
                    <th>Phone</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Hours</th>
                    <th>Total</th>
                    <th>Payment</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($closedVisits as $visit): ?>
                    <tr>
                        <td>#<?= e((string) $visit['id']) ?></td>
                        <td><?= e((string) $visit['full_name']) ?></td>
                        <td><?= e((string) $visit['phone']) ?></td>
                        <td><?= e(format_datetime((string) $visit['check_in_at'])) ?></td>
                        <td><?= e(format_datetime((string) $visit['check_out_at'])) ?></td>
                        <td><?= e((string) $visit['billable_hours']) ?></td>
                        <td><?= e(format_money((float) $visit['grand_total'], $currency)) ?></td>
                        <td><?= e((string) ($visit['payment_method'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
