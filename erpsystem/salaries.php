<?php
$pageTitle = 'Salaries';
$activePage = 'salaries';
$pageDescription = 'HR payroll module for monthly salaries and payment status.';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) post_value('action', 'create');
    $salaryId = (int) post_value('id', 0);

    if ($action === 'update' && $salaryId > 0) {
        execute_query(
            'UPDATE salaries SET employee_name = ?, role = ?, amount = ?, salary_month = ?, status = ?, paid_on = ? WHERE id = ?',
            [
                trim((string) post_value('employee_name')),
                trim((string) post_value('role')),
                (float) post_value('amount', 0),
                month_input_to_date((string) post_value('salary_month')),
                (string) post_value('status', 'pending'),
                post_value('paid_on') ?: null,
                $salaryId,
            ]
        );
        redirect_to('salaries?updated=1');
    }

    if ($action === 'delete' && $salaryId > 0) {
        execute_query('DELETE FROM salaries WHERE id = ?', [$salaryId]);
        redirect_to('salaries?deleted=1');
    }

    execute_query(
        'INSERT INTO salaries (employee_name, role, amount, salary_month, status, paid_on) VALUES (?, ?, ?, ?, ?, ?)',
        [
            trim((string) post_value('employee_name')),
            trim((string) post_value('role')),
            (float) post_value('amount', 0),
            month_input_to_date((string) post_value('salary_month')),
            (string) post_value('status', 'pending'),
            post_value('paid_on') ?: null,
        ]
    );
    redirect_to('salaries?saved=1');
}

$editId = (int) ($_GET['edit'] ?? 0);
$editingSalary = $editId > 0 ? query_one('SELECT * FROM salaries WHERE id = ?', [$editId]) : null;
$salaries = query_all('SELECT * FROM salaries ORDER BY salary_month DESC, id DESC');
$monthTotal = query_value(
    'SELECT COALESCE(SUM(amount), 0) FROM salaries WHERE salary_month >= ? AND salary_month < ?',
    [month_start(), next_month_start()]
);
?>

<section class="content-grid">
    <article class="panel form-panel <?= $editingSalary ? 'editing' : '' ?>">
        <div class="panel-heading">
            <div>
                <p class="eyebrow"><?= $editingSalary ? 'Update Payroll' : 'Payroll' ?></p>
                <h2><?= $editingSalary ? 'Edit Salary' : 'Add Salary' ?></h2>
            </div>
            <?php if ($editingSalary): ?>
                <a class="ghost-link" href="salaries">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="<?= $editingSalary ? 'update' : 'create' ?>">
            <?php if ($editingSalary): ?>
                <input type="hidden" name="id" value="<?= e($editingSalary['id']) ?>">
            <?php endif; ?>
            <label>
                Employee Name
                <input type="text" name="employee_name" required value="<?= e($editingSalary['employee_name'] ?? '') ?>" placeholder="Employee name">
            </label>
            <label>
                Role
                <input type="text" name="role" value="<?= e($editingSalary['role'] ?? '') ?>" placeholder="Developer">
            </label>
            <label>
                Amount
                <input type="number" name="amount" min="0" step="1000" value="<?= e($editingSalary['amount'] ?? '') ?>" required>
            </label>
            <label>
                Salary Month
                <input type="month" name="salary_month" value="<?= e(date_to_month($editingSalary['salary_month'] ?? null)) ?>">
            </label>
            <label>
                Status
                <select name="status">
                    <option value="pending" <?= selected((string) ($editingSalary['status'] ?? 'pending'), 'pending') ?>>Pending</option>
                    <option value="paid" <?= selected((string) ($editingSalary['status'] ?? ''), 'paid') ?>>Paid</option>
                </select>
            </label>
            <label>
                Paid On
                <input type="date" name="paid_on" value="<?= e($editingSalary['paid_on'] ?? '') ?>">
            </label>
            <div class="form-actions">
                <button type="submit" class="primary-button"><?= $editingSalary ? 'Update Salary' : 'Save Salary' ?></button>
                <?php if ($editingSalary): ?>
                    <a class="secondary-button" href="salaries">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">This Month</p>
                <h2>Salary Summary</h2>
            </div>
        </div>
        <div class="mini-stats">
            <div><strong><?= e(money($monthTotal)) ?></strong><span>This Month Salary</span></div>
            <div><strong><?= e(count($salaries)) ?></strong><span>Salary Records</span></div>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">HR</p>
            <h2>Salary Records</h2>
        </div>
        <input class="search-input" type="search" placeholder="Search salaries" data-table-search="salaryTable">
    </div>
    <div class="table-wrap">
        <table id="salaryTable">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Month</th>
                    <th>Status</th>
                    <th>Paid On</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$salaries): ?>
                <tr><td colspan="7" class="empty">No salary records yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($salaries as $salary): ?>
                <tr>
                    <td><?= e($salary['employee_name']) ?></td>
                    <td><?= e($salary['role'] ?: 'N/A') ?></td>
                    <td><?= e(date('M Y', strtotime($salary['salary_month']))) ?></td>
                    <td><span class="status <?= e(status_class($salary['status'])) ?>"><?= e(ucfirst($salary['status'])) ?></span></td>
                    <td><?= e($salary['paid_on'] ?: 'N/A') ?></td>
                    <td><?= e(money($salary['amount'])) ?></td>
                    <td>
                        <div class="action-group">
                            <a class="action-link" href="salaries?edit=<?= e($salary['id']) ?>">Edit</a>
                            <form method="post" class="inline-form" data-confirm="Delete this salary record?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($salary['id']) ?>">
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
