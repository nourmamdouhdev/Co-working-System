<section class="card">
    <h1>Staff Management</h1>

    <h2>Create User</h2>
    <form method="POST" action="<?= e(app_url('/admin/staff')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">

        <label for="name">Name</label>
        <input id="name" name="name" required maxlength="120">

        <label for="username">Username</label>
        <input id="username" name="username" required maxlength="50">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required minlength="6">

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Create User</button>
    </form>
</section>

<section class="card">
    <h2>Users</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?= e((string) $user['id']) ?></td>
                    <td><?= e((string) $user['name']) ?></td>
                    <td><?= e((string) $user['username']) ?></td>
                    <td><?= e((string) $user['role']) ?></td>
                    <td><?= (int) $user['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <form method="POST" action="<?= e(app_url('/admin/staff')) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                            <input type="hidden" name="is_active" value="<?= (int) $user['is_active'] ?>">
                            <button type="submit"><?= (int) $user['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
