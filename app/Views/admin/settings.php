<section class="card">
    <h1>System Settings</h1>

    <form method="POST" action="<?= e(app_url('/admin/settings')) ?>">
        <?= csrf_field() ?>

        <label for="hourly_rate">Hourly Rate</label>
        <input id="hourly_rate" name="hourly_rate" type="number" min="0.01" step="0.01" required value="<?= e((string) $settings['hourly_rate']) ?>">

        <label for="currency">Currency</label>
        <select id="currency" name="currency" required>
            <option value="EGP" selected>EGP (Egyptian Pound)</option>
        </select>

        <label for="timezone">Timezone</label>
        <select id="timezone" name="timezone" required>
            <option value="Africa/Cairo" selected>Africa/Cairo (Egypt)</option>
        </select>

        <button type="submit">Save Settings</button>
    </form>
</section>
