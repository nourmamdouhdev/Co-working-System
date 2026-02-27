<section class="card narrow">
    <h1>Login</h1>
    <form method="POST" action="<?= e(app_url('/login')) ?>">
        <?= csrf_field() ?>

        <label for="username">Username</label>
        <input id="username" name="username" required maxlength="50" value="<?= e(old('username')) ?>">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>

        <button type="submit">Sign In</button>
    </form>
</section>
