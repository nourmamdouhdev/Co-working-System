<section class="card checkout-shell">
    <div class="checkout-head">
        <div>
            <h1>Checkout Desk</h1>
            <p class="muted">Search client, open active visit, then finalize in one screen.</p>
        </div>
        <form method="GET" action="<?= e(app_url('/checkout')) ?>" class="search-inline">
            <label for="q" class="sr-only">Search by phone or name</label>
            <input id="q" name="q" value="<?= e($query) ?>" placeholder="Search by phone or name">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="checkout-flow">
        <aside class="queue-column">
            <?php if ($query === ''): ?>
                <h2>Active Visits</h2>
                <?php if ($activeVisits === []): ?>
                    <p class="muted">No active visits right now.</p>
                <?php else: ?>
                    <div class="visit-list">
                        <?php foreach ($activeVisits as $visit): ?>
                            <?php
                                $visitId = (int) $visit['id'];
                                $isSelected = $selectedVisit && (int) $selectedVisit['id'] === $visitId;
                                $elapsed = \App\Models\Visit::computeDurationMinutes((string) $visit['check_in_at']);
                            ?>
                            <a class="visit-pill <?= $isSelected ? 'is-selected' : '' ?>" href="<?= e(app_url('/checkout?visit_id=' . $visitId)) ?>">
                                <strong><?= e((string) $visit['full_name']) ?></strong>
                                <span>#<?= e((string) $visit['id']) ?> | <?= e((string) $visit['phone']) ?></span>
                                <span>In: <?= e(format_datetime((string) $visit['check_in_at'], 'h:i A')) ?> | <?= e((string) $elapsed) ?>m</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <h2>Search Results</h2>
                <?php if ($results === []): ?>
                    <p class="muted">No clients found for this query.</p>
                <?php else: ?>
                    <div class="search-result-list">
                        <?php foreach ($results as $row): ?>
                            <?php $activeVisitId = (int) ($row['active_visit_id'] ?? 0); ?>
                            <article class="result-item">
                                <div>
                                    <strong><?= e((string) $row['full_name']) ?></strong>
                                    <p><?= e((string) $row['phone']) ?></p>
                                </div>
                                <?php if ($activeVisitId > 0): ?>
                                    <a class="button-link" href="<?= e(app_url('/checkout?visit_id=' . $activeVisitId . '&q=' . urlencode($query))) ?>">Open Visit #<?= e((string) $activeVisitId) ?></a>
                                <?php else: ?>
                                    <span class="status-pill">No active visit</span>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </aside>

        <div class="workspace-column">
            <?php if ($selectedVisit): ?>
                <?php $checkInIso = datetime_to_iso((string) $selectedVisit['check_in_at']); ?>
                <section
                    class="workspace-panel"
                    data-hourly-rate="<?= e((string) (float) $selectedVisit['hourly_rate_snapshot']) ?>"
                    data-currency="<?= e($currency) ?>"
                >
                    <header class="workspace-top">
                        <div>
                            <h2>Visit #<?= e((string) $selectedVisit['id']) ?></h2>
                            <p><?= e((string) $selectedVisit['full_name']) ?> | <?= e((string) $selectedVisit['phone']) ?></p>
                            <p class="muted">Check-in: <?= e(format_datetime((string) $selectedVisit['check_in_at'])) ?></p>
                        </div>
                        <div class="timer-box" data-checkin-iso="<?= e($checkInIso) ?>">
                            <p class="timer-label">Live Timer</p>
                            <p class="timer-value" data-live-timer>00:00:00</p>
                        </div>
                    </header>

                    <div class="metric-grid">
                        <article class="metric">
                            <h3>Current Minutes</h3>
                            <p data-live-minutes><?= e((string) $durationMinutes) ?></p>
                        </article>
                        <article class="metric">
                            <h3>Billable Hours</h3>
                            <p data-live-billable-hours><?= e((string) $billableHours) ?></p>
                        </article>
                        <article class="metric">
                            <h3>Time Charge</h3>
                            <p data-live-time-charge><?= e(format_money((float) $timeCharge, $currency)) ?></p>
                        </article>
                        <article class="metric">
                            <h3>Total Price</h3>
                            <p data-live-total-price><?= e(format_money((float) $timeCharge, $currency)) ?></p>
                        </article>
                    </div>

                    <form method="POST" action="<?= e(app_url('/checkout/' . (int) $selectedVisit['id'] . '/finalize')) ?>">
                        <?= csrf_field() ?>

                        <section class="addons-panel">
                            <div class="section-head">
                                <h3>Add-ons</h3>
                                <p class="muted">Choose quantities for extra items before finalizing checkout.</p>
                            </div>

                            <?php if ($products === []): ?>
                                <p class="muted">No active products configured.</p>
                            <?php else: ?>
                                <div class="addons-grid">
                                    <?php foreach ($products as $product): ?>
                                        <article class="addon-box">
                                            <div class="addon-meta">
                                                <strong><?= e((string) $product['name']) ?></strong>
                                                <p><?= e(format_money((float) $product['unit_price'], $currency)) ?></p>
                                            </div>
                                            <label class="addon-qty" for="qty_<?= (int) $product['id'] ?>">
                                                <span>Qty</span>
                                                <input
                                                    id="qty_<?= (int) $product['id'] ?>"
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    name="qty[<?= (int) $product['id'] ?>]"
                                                    value="0"
                                                    data-addon-qty
                                                    data-unit-price="<?= e((string) (float) $product['unit_price']) ?>"
                                                >
                                            </label>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>

                        <h3>Payment Method</h3>
                        <div class="payment-methods">
                            <label class="method-option">
                                <input type="radio" name="payment_method" value="cash" checked>
                                <span>Cash</span>
                                <small>Counter payment</small>
                            </label>
                            <label class="method-option">
                                <input type="radio" name="payment_method" value="visa">
                                <span>Visa</span>
                                <small>Card payment</small>
                            </label>
                        </div>

                        <button type="submit" class="finalize-btn">Finalize Checkout</button>
                    </form>
                </section>
            <?php else: ?>
                <section class="workspace-empty">
                    <h2>Select an Active Visit</h2>
                    <p>Pick a visit from the left list or search for a client to start checkout.</p>
                </section>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(() => {
    const timerBox = document.querySelector('[data-checkin-iso]');
    if (!timerBox) {
        return;
    }

    const checkInIso = timerBox.getAttribute('data-checkin-iso') || '';
    const checkIn = new Date(checkInIso);
    if (Number.isNaN(checkIn.getTime())) {
        return;
    }

    const timerText = timerBox.querySelector('[data-live-timer]');
    const minutesText = document.querySelector('[data-live-minutes]');
    const workspacePanel = timerBox.closest('[data-hourly-rate]');
    const billableHoursText = document.querySelector('[data-live-billable-hours]');
    const timeChargeText = document.querySelector('[data-live-time-charge]');
    const totalPriceText = document.querySelector('[data-live-total-price]');
    const qtyInputs = Array.from(document.querySelectorAll('[data-addon-qty]'));
    const hourlyRate = Number(workspacePanel?.getAttribute('data-hourly-rate') || '0');
    const currency = (workspacePanel?.getAttribute('data-currency') || 'EGP').toUpperCase();

    const pad = (value) => String(value).padStart(2, '0');
    const formatMoney = (value) => `${currency} ${Number(value).toFixed(2)}`;
    const addonsTotal = () => qtyInputs.reduce((sum, input) => {
        const unitPrice = Number(input.getAttribute('data-unit-price') || '0');
        const qty = Math.max(0, parseInt(input.value || '0', 10) || 0);
        return sum + (unitPrice * qty);
    }, 0);

    const render = () => {
        const diffSeconds = Math.max(0, Math.floor((Date.now() - checkIn.getTime()) / 1000));
        const hours = Math.floor(diffSeconds / 3600);
        const minutes = Math.floor((diffSeconds % 3600) / 60);
        const seconds = diffSeconds % 60;
        const totalMinutes = Math.floor(diffSeconds / 60);
        const billableHours = Math.max(1, Math.ceil(totalMinutes / 60));
        const timeCharge = billableHours * hourlyRate;
        const totalPrice = timeCharge + addonsTotal();

        if (timerText) {
            timerText.textContent = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        }

        if (minutesText) {
            minutesText.textContent = String(totalMinutes);
        }

        if (billableHoursText) {
            billableHoursText.textContent = String(billableHours);
        }

        if (timeChargeText) {
            timeChargeText.textContent = formatMoney(timeCharge);
        }

        if (totalPriceText) {
            totalPriceText.textContent = formatMoney(totalPrice);
        }
    };

    qtyInputs.forEach((input) => {
        input.addEventListener('input', render);
        input.addEventListener('change', render);
    });

    render();
    setInterval(render, 1000);
})();
</script>
