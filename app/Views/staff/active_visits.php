<section class="card">
    <h1>Active Visits</h1>

    <?php if ($activeVisits === []): ?>
        <p>No active visits right now.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Visit ID</th>
                    <th>Client</th>
                    <th>Phone</th>
                    <th>Check-In</th>
                    <th>Elapsed (min)</th>
                    <th>Rate</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($activeVisits as $visit): ?>
                    <?php $elapsed = \App\Models\Visit::computeDurationMinutes((string) $visit['check_in_at']); ?>
                    <tr>
                        <td>#<?= e((string) $visit['id']) ?></td>
                        <td><?= e((string) $visit['full_name']) ?></td>
                        <td><?= e((string) $visit['phone']) ?></td>
                        <td><?= e(format_datetime((string) $visit['check_in_at'])) ?></td>
                        <td><?= e((string) $elapsed) ?></td>
                        <td><?= e(format_money((float) $visit['hourly_rate_snapshot'], $currency)) ?>/hour</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
