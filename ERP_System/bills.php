<?php
$pageTitle = 'Rent & Bills';
$activePage = 'bills';
$pageDescription = 'Track office rent, utilities, internet, software, and other recurring bills.';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) post_value('action', 'create');
    $billId = (int) post_value('id', 0);

    if ($action === 'update' && $billId > 0) {
        execute_query(
            'UPDATE bills SET title = ?, bill_type = ?, amount = ?, bill_month = ?, status = ?, due_date = ?, paid_on = ? WHERE id = ?',
            [
                trim((string) post_value('title')),
                (string) post_value('bill_type', 'other'),
                (float) post_value('amount', 0),
                month_input_to_date((string) post_value('bill_month')),
                (string) post_value('status', 'pending'),
                post_value('due_date') ?: null,
                post_value('paid_on') ?: null,
                $billId,
            ]
        );
        redirect_to('bills?updated=1');
    }

    if ($action === 'delete' && $billId > 0) {
        execute_query('DELETE FROM bills WHERE id = ?', [$billId]);
        redirect_to('bills?deleted=1');
    }

    execute_query(
        'INSERT INTO bills (title, bill_type, amount, bill_month, status, due_date, paid_on) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [
            trim((string) post_value('title')),
            (string) post_value('bill_type', 'other'),
            (float) post_value('amount', 0),
            month_input_to_date((string) post_value('bill_month')),
            (string) post_value('status', 'pending'),
            post_value('due_date') ?: null,
            post_value('paid_on') ?: null,
        ]
    );
    redirect_to('bills?saved=1');
}

$editId = (int) ($_GET['edit'] ?? 0);
$editingBill = $editId > 0 ? query_one('SELECT * FROM bills WHERE id = ?', [$editId]) : null;
$bills = query_all('SELECT * FROM bills ORDER BY bill_month DESC, due_date ASC, id DESC');
$monthBills = query_value(
    'SELECT COALESCE(SUM(amount), 0) FROM bills WHERE bill_month >= ? AND bill_month < ?',
    [month_start(), next_month_start()]
);
?>

<section class="content-grid">
    <article class="panel form-panel <?= $editingBill ? 'editing' : '' ?>">
        <div class="panel-heading">
            <div>
                <p class="eyebrow"><?= $editingBill ? 'Update Office Cost' : 'Office Cost' ?></p>
                <h2><?= $editingBill ? 'Edit Rent/Bill' : 'Add Rent/Bill' ?></h2>
            </div>
            <?php if ($editingBill): ?>
                <a class="ghost-link" href="bills">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="<?= $editingBill ? 'update' : 'create' ?>">
            <?php if ($editingBill): ?>
                <input type="hidden" name="id" value="<?= e($editingBill['id']) ?>">
            <?php endif; ?>
            <label>
                Title
                <input type="text" name="title" required value="<?= e($editingBill['title'] ?? '') ?>" placeholder="Office Rent">
            </label>
            <label>
                Bill Type
                <select name="bill_type">
                    <?php foreach (['rent', 'electricity', 'internet', 'software', 'other'] as $type): ?>
                        <option value="<?= e($type) ?>" <?= selected((string) ($editingBill['bill_type'] ?? 'other'), $type) ?>><?= e(ucfirst($type)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Amount
                <input type="number" name="amount" min="0" step="1000" value="<?= e($editingBill['amount'] ?? '') ?>" required>
            </label>
            <label>
                Bill Month
                <input type="month" name="bill_month" value="<?= e(date_to_month($editingBill['bill_month'] ?? null)) ?>">
            </label>
            <label>
                Due Date
                <input type="date" name="due_date" value="<?= e($editingBill['due_date'] ?? '') ?>">
            </label>
            <label>
                Status
                <select name="status">
                    <?php foreach (['pending', 'paid', 'overdue'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= selected((string) ($editingBill['status'] ?? 'pending'), $status) ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Paid On
                <input type="date" name="paid_on" value="<?= e($editingBill['paid_on'] ?? '') ?>">
            </label>
            <div class="form-actions">
                <button type="submit" class="primary-button"><?= $editingBill ? 'Update Bill' : 'Save Bill' ?></button>
                <?php if ($editingBill): ?>
                    <a class="secondary-button" href="bills">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">This Month</p>
                <h2>Bills Summary</h2>
            </div>
        </div>
        <div class="mini-stats">
            <div><strong><?= e(money($monthBills)) ?></strong><span>This Month Rent/Bills</span></div>
            <div><strong><?= e(count($bills)) ?></strong><span>Bill Records</span></div>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">Admin</p>
            <h2>Bill Records</h2>
        </div>
        <input class="search-input" type="search" placeholder="Search bills" data-table-search="billTable">
    </div>
    <div class="table-wrap">
        <table id="billTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Month</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$bills): ?>
                <tr><td colspan="7" class="empty">No bills yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($bills as $bill): ?>
                <tr>
                    <td><?= e($bill['title']) ?></td>
                    <td><?= e(ucfirst($bill['bill_type'])) ?></td>
                    <td><?= e(date('M Y', strtotime($bill['bill_month']))) ?></td>
                    <td><?= e($bill['due_date'] ?: 'N/A') ?></td>
                    <td><span class="status <?= e(status_class($bill['status'])) ?>"><?= e(ucfirst($bill['status'])) ?></span></td>
                    <td><?= e(money($bill['amount'])) ?></td>
                    <td>
                        <div class="action-group">
                            <a class="action-link" href="bills?edit=<?= e($bill['id']) ?>">Edit</a>
                            <form method="post" class="inline-form" data-confirm="Delete this bill?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($bill['id']) ?>">
                                <button type="submit" class="link-button danger-text">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
