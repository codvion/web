<?php
$pageTitle = 'Revenue & Expenses';
$activePage = 'finance';
$pageDescription = 'Create invoices, receive payments, and record business expenses.';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) post_value('action');

    if ($action === 'invoice') {
        $invoiceNumber = trim((string) post_value('invoice_number'));
        if ($invoiceNumber === '') {
            $invoiceNumber = 'INV-' . date('Ymd-His');
        }
        $status = (string) post_value('status', 'pending');
        $amount = (float) post_value('amount', 0);

        execute_query(
            'INSERT INTO invoices (client_id, project_id, invoice_number, amount, status, due_date, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                (int) post_value('client_id'),
                post_value('project_id') ?: null,
                $invoiceNumber,
                $amount,
                $status,
                post_value('due_date') ?: null,
                $status === 'paid' ? date('Y-m-d') : null,
            ]
        );

        if ($status === 'paid' && $amount > 0) {
            execute_query(
                'INSERT INTO payments (invoice_id, amount, paid_on, method, notes) VALUES (?, ?, ?, ?, ?)',
                [(int) db()->lastInsertId(), $amount, date('Y-m-d'), 'Direct', 'Invoice marked paid']
            );
        }
        redirect_to('finance?saved=1');
    }

    if ($action === 'invoice_update') {
        $invoiceId = (int) post_value('id', 0);
        $amount = (float) post_value('amount', 0);
        $status = (string) post_value('status', 'pending');

        execute_query(
            'UPDATE invoices SET client_id = ?, project_id = ?, invoice_number = ?, amount = ?, status = ?, due_date = ?, paid_at = ? WHERE id = ?',
            [
                (int) post_value('client_id'),
                post_value('project_id') ?: null,
                trim((string) post_value('invoice_number')),
                $amount,
                $status,
                post_value('due_date') ?: null,
                $status === 'paid' ? date('Y-m-d') : null,
                $invoiceId,
            ]
        );

        $paidAmount = (float) query_value('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = ?', [$invoiceId]);
        if ($status === 'paid' && $amount > $paidAmount) {
            execute_query(
                'INSERT INTO payments (invoice_id, amount, paid_on, method, notes) VALUES (?, ?, ?, ?, ?)',
                [$invoiceId, $amount - $paidAmount, date('Y-m-d'), 'Direct', 'Invoice balance adjusted']
            );
        }
        redirect_to('finance?updated=1');
    }

    if ($action === 'invoice_delete') {
        execute_query('DELETE FROM invoices WHERE id = ?', [(int) post_value('id', 0)]);
        redirect_to('finance?deleted=1');
    }

    if ($action === 'payment') {
        $invoiceId = (int) post_value('invoice_id');
        $amount = (float) post_value('amount', 0);
        execute_query(
            'INSERT INTO payments (invoice_id, amount, paid_on, method, notes) VALUES (?, ?, ?, ?, ?)',
            [
                $invoiceId,
                $amount,
                post_value('paid_on') ?: date('Y-m-d'),
                trim((string) post_value('method')),
                trim((string) post_value('notes')),
            ]
        );

        $invoice = query_one(
            'SELECT i.amount, COALESCE(SUM(p.amount), 0) AS paid_amount
             FROM invoices i
             LEFT JOIN payments p ON p.invoice_id = i.id
             WHERE i.id = ?
             GROUP BY i.id',
            [$invoiceId]
        );

        if ($invoice && (float) $invoice['paid_amount'] >= (float) $invoice['amount']) {
            execute_query("UPDATE invoices SET status = 'paid', paid_at = ? WHERE id = ?", [date('Y-m-d'), $invoiceId]);
        }
        redirect_to('finance?saved=1');
    }

    if ($action === 'expense') {
        execute_query(
            'INSERT INTO expenses (title, category, amount, expense_date, notes) VALUES (?, ?, ?, ?, ?)',
            [
                trim((string) post_value('title')),
                trim((string) post_value('category')),
                (float) post_value('amount', 0),
                post_value('expense_date') ?: date('Y-m-d'),
                trim((string) post_value('notes')),
            ]
        );
        redirect_to('finance?saved=1');
    }

    if ($action === 'expense_update') {
        execute_query(
            'UPDATE expenses SET title = ?, category = ?, amount = ?, expense_date = ?, notes = ? WHERE id = ?',
            [
                trim((string) post_value('title')),
                trim((string) post_value('category')),
                (float) post_value('amount', 0),
                post_value('expense_date') ?: date('Y-m-d'),
                trim((string) post_value('notes')),
                (int) post_value('id', 0),
            ]
        );
        redirect_to('finance?updated=1');
    }

    if ($action === 'expense_delete') {
        execute_query('DELETE FROM expenses WHERE id = ?', [(int) post_value('id', 0)]);
        redirect_to('finance?deleted=1');
    }

    redirect_to('finance');
}

$editingInvoice = isset($_GET['edit_invoice']) ? query_one('SELECT * FROM invoices WHERE id = ?', [(int) $_GET['edit_invoice']]) : null;
$editingExpense = isset($_GET['edit_expense']) ? query_one('SELECT * FROM expenses WHERE id = ?', [(int) $_GET['edit_expense']]) : null;
$clients = query_all('SELECT id, name, status FROM clients ORDER BY name ASC');
$projects = query_all('SELECT id, name, status FROM projects ORDER BY name ASC');
$openInvoices = query_all(
    "SELECT i.id, i.invoice_number, i.amount, c.name AS client_name,
            COALESCE(SUM(p.amount), 0) AS paid_amount
     FROM invoices i
     LEFT JOIN clients c ON c.id = i.client_id
     LEFT JOIN payments p ON p.invoice_id = i.id
     WHERE i.status IN ('pending', 'overdue')
     GROUP BY i.id
     HAVING i.amount > paid_amount
     ORDER BY i.due_date ASC"
);
$invoices = query_all(
    'SELECT i.*, c.name AS client_name, p.name AS project_name,
            COALESCE(SUM(pay.amount), 0) AS paid_amount
     FROM invoices i
     LEFT JOIN clients c ON c.id = i.client_id
     LEFT JOIN projects p ON p.id = i.project_id
     LEFT JOIN payments pay ON pay.invoice_id = i.id
     GROUP BY i.id
     ORDER BY i.created_at DESC
     LIMIT 25'
);
$expenses = query_all('SELECT * FROM expenses ORDER BY expense_date DESC, id DESC LIMIT 25');
?>

<section class="finance-forms">
    <article class="panel form-panel <?= $editingInvoice ? 'editing' : '' ?>">
        <div class="panel-heading">
            <div>
                <p class="eyebrow"><?= $editingInvoice ? 'Update Billing' : 'Billing' ?></p>
                <h2><?= $editingInvoice ? 'Edit Invoice' : 'Create Invoice' ?></h2>
            </div>
            <?php if ($editingInvoice): ?>
                <a class="ghost-link" href="finance">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="<?= $editingInvoice ? 'invoice_update' : 'invoice' ?>">
            <?php if ($editingInvoice): ?>
                <input type="hidden" name="id" value="<?= e($editingInvoice['id']) ?>">
            <?php endif; ?>
            <label>
                Invoice Number
                <input type="text" name="invoice_number" value="<?= e($editingInvoice['invoice_number'] ?? '') ?>" placeholder="Auto generated if empty" <?= $editingInvoice ? 'required' : '' ?>>
            </label>
            <label>
                Client
                <select name="client_id" required>
                    <option value="">Select client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e($client['id']) ?>" <?= selected((string) ($editingInvoice['client_id'] ?? ''), (string) $client['id']) ?>>
                            <?= e($client['name']) ?><?= $client['status'] === 'inactive' ? ' (Inactive)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Project
                <select name="project_id">
                    <option value="">No project</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= e($project['id']) ?>" <?= selected((string) ($editingInvoice['project_id'] ?? ''), (string) $project['id']) ?>>
                            <?= e($project['name']) ?><?= in_array($project['status'], ['completed', 'cancelled'], true) ? ' (' . e(ucfirst($project['status'])) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Amount
                <input type="number" name="amount" min="0" step="1000" value="<?= e($editingInvoice['amount'] ?? '') ?>" required>
            </label>
            <label>
                Due Date
                <input type="date" name="due_date" value="<?= e($editingInvoice['due_date'] ?? '') ?>">
            </label>
            <label>
                Status
                <select name="status">
                    <?php foreach (['pending', 'paid', 'overdue'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= selected((string) ($editingInvoice['status'] ?? 'pending'), $status) ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="form-actions">
                <button type="submit" class="primary-button"><?= $editingInvoice ? 'Update Invoice' : 'Save Invoice' ?></button>
                <?php if ($editingInvoice): ?>
                    <a class="secondary-button" href="finance">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="panel form-panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Collection</p>
                <h2>Receive Payment</h2>
            </div>
        </div>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="payment">
            <label>
                Invoice
                <select name="invoice_id" required>
                    <option value="">Select invoice</option>
                    <?php foreach ($openInvoices as $invoice): ?>
                        <option value="<?= e($invoice['id']) ?>"><?= e($invoice['invoice_number']) ?> - <?= e($invoice['client_name']) ?> - Due <?= e(money((float) $invoice['amount'] - (float) $invoice['paid_amount'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Amount
                <input type="number" name="amount" min="0" step="1000" required>
            </label>
            <label>
                Paid On
                <input type="date" name="paid_on" value="<?= e(date('Y-m-d')) ?>">
            </label>
            <label>
                Method
                <input type="text" name="method" placeholder="Bank, Cash, JazzCash">
            </label>
            <label class="full">
                Notes
                <input type="text" name="notes" placeholder="Reference or transaction note">
            </label>
            <button type="submit" class="primary-button">Record Payment</button>
        </form>
    </article>

    <article class="panel form-panel <?= $editingExpense ? 'editing' : '' ?>">
        <div class="panel-heading">
            <div>
                <p class="eyebrow"><?= $editingExpense ? 'Update Cost' : 'Cost Control' ?></p>
                <h2><?= $editingExpense ? 'Edit Expense' : 'Add Expense' ?></h2>
            </div>
            <?php if ($editingExpense): ?>
                <a class="ghost-link" href="finance">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="<?= $editingExpense ? 'expense_update' : 'expense' ?>">
            <?php if ($editingExpense): ?>
                <input type="hidden" name="id" value="<?= e($editingExpense['id']) ?>">
            <?php endif; ?>
            <label>
                Title
                <input type="text" name="title" required value="<?= e($editingExpense['title'] ?? '') ?>" placeholder="Office supplies">
            </label>
            <label>
                Category
                <input type="text" name="category" value="<?= e($editingExpense['category'] ?? '') ?>" placeholder="Operations">
            </label>
            <label>
                Amount
                <input type="number" name="amount" min="0" step="1000" value="<?= e($editingExpense['amount'] ?? '') ?>" required>
            </label>
            <label>
                Date
                <input type="date" name="expense_date" value="<?= e($editingExpense['expense_date'] ?? date('Y-m-d')) ?>">
            </label>
            <label class="full">
                Notes
                <input type="text" name="notes" value="<?= e($editingExpense['notes'] ?? '') ?>" placeholder="Optional">
            </label>
            <div class="form-actions">
                <button type="submit" class="primary-button"><?= $editingExpense ? 'Update Expense' : 'Save Expense' ?></button>
                <?php if ($editingExpense): ?>
                    <a class="secondary-button" href="finance">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </article>
</section>

<section class="dashboard-grid">
    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Revenue</p>
                <h2>Invoices</h2>
            </div>
        </div>
        <div class="table-wrap compact">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$invoices): ?>
                    <tr><td colspan="6" class="empty">No invoices yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?= e($invoice['invoice_number']) ?></td>
                        <td><?= e($invoice['client_name'] ?? 'N/A') ?></td>
                        <td><span class="status <?= e(status_class($invoice['status'])) ?>"><?= e(ucfirst($invoice['status'])) ?></span></td>
                        <td><?= e(money($invoice['amount'])) ?></td>
                        <td><?= e(money($invoice['paid_amount'])) ?></td>
                        <td>
                            <div class="action-group">
                                <a class="action-link" href="finance?edit_invoice=<?= e($invoice['id']) ?>">Edit</a>
                                <form method="post" class="inline-form" data-confirm="Delete this invoice and its payment history?">
                                    <input type="hidden" name="action" value="invoice_delete">
                                    <input type="hidden" name="id" value="<?= e($invoice['id']) ?>">
                                    <button type="submit" class="link-button danger-text">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Expenses</p>
                <h2>Recent Expenses</h2>
            </div>
        </div>
        <div class="table-wrap compact">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$expenses): ?>
                    <tr><td colspan="5" class="empty">No expenses yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= e($expense['title']) ?></td>
                        <td><?= e($expense['category'] ?: 'General') ?></td>
                        <td><?= e($expense['expense_date']) ?></td>
                        <td><?= e(money($expense['amount'])) ?></td>
                        <td>
                            <div class="action-group">
                                <a class="action-link" href="finance?edit_expense=<?= e($expense['id']) ?>">Edit</a>
                                <form method="post" class="inline-form" data-confirm="Delete this expense?">
                                    <input type="hidden" name="action" value="expense_delete">
                                    <input type="hidden" name="id" value="<?= e($expense['id']) ?>">
                                    <button type="submit" class="link-button danger-text">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
