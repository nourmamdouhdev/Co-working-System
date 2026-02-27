<section class="card">
    <h1>Product Management</h1>

    <h2>Create Product</h2>
    <form method="POST" action="<?= e(app_url('/admin/products')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">

        <label for="name">Name</label>
        <input id="name" name="name" required maxlength="120">

        <label for="unit_price">Unit Price</label>
        <input id="unit_price" name="unit_price" type="number" step="0.01" min="0" required>

        <button type="submit">Create Product</button>
    </form>
</section>

<section class="card">
    <h2>Products</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Unit Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>#<?= e((string) $product['id']) ?></td>
                    <td><?= e((string) $product['name']) ?></td>
                    <td><?= e(format_money((float) $product['unit_price'], $currency)) ?></td>
                    <td><?= (int) $product['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <form method="POST" action="<?= e(app_url('/admin/products')) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="is_active" value="<?= (int) $product['is_active'] ?>">
                            <button type="submit"><?= (int) $product['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
