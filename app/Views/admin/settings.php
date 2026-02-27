<section class="card">
    <h1>System Settings</h1>

    <form method="POST" action="<?= e(app_url('/admin/settings')) ?>">
        <?= csrf_field() ?>

        <label for="hourly_rate">Hourly Rate</label>
        <input id="hourly_rate" name="hourly_rate" type="number" min="0.01" step="0.01" required value="<?= e((string) $settings['hourly_rate']) ?>">

        <label for="currency">Currency</label>
        <input id="currency" name="currency" maxlength="10" required value="<?= e((string) $settings['currency']) ?>">

        <label for="timezone">Timezone</label>
        <input id="timezone" name="timezone" required value="<?= e((string) $settings['timezone']) ?>" placeholder="e.g. America/New_York">

        <button type="submit">Save Settings</button>
    </form>
</section>
