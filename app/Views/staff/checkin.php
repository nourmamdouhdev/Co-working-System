<section class="card">
    <h1>Client Check-In</h1>
    <p>Current hourly rate: <strong><?= e(format_money((float) $hourlyRate, $currency)) ?></strong></p>

    <form method="POST" action="<?= e(app_url('/checkin')) ?>">
        <?= csrf_field() ?>

        <label for="full_name">Client Full Name</label>
        <input id="full_name" name="full_name" required maxlength="120" value="<?= e(old('full_name')) ?>">

        <label for="phone">Phone Number</label>
        <input id="phone" name="phone" required maxlength="20" value="<?= e(old('phone')) ?>">

        <button type="submit">Check In Client</button>
    </form>
</section>
