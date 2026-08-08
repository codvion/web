<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
$pageDescription = 'Live overview of clients, projects, revenue, expenses, salaries, and bills.';
require_once __DIR__ . '/includes/header.php';

$monthStart = month_start();
$nextMonthStart = next_month_start();

$totalClients = (int) query_value('SELECT COUNT(*) FROM clients');
$activeProjects = (int) query_value("SELECT COUNT(*) FROM projects WHERE status IN ('planning', 'active')");
$totalRevenue = (float) query_value('SELECT COALESCE(SUM(amount), 0) FROM payments');
$totalExpenses = (float) query_value('SELECT COALESCE(SUM(amount), 0) FROM expenses');
$salaryTotal = (float) query_value("SELECT COALESCE(SUM(amount), 0) FROM salaries WHERE status = 'paid'");
$billTotal = (float) query_value("SELECT COALESCE(SUM(amount), 0) FROM bills WHERE status = 'paid'");
$netProfit = $totalRevenue - $totalExpenses - $salaryTotal - $billTotal;
$pendingPayments = (float) query_value(
    "SELECT COALESCE(SUM(GREATEST(i.amount - COALESCE(p.paid_total, 0), 0)), 0)
     FROM invoices i
     LEFT JOIN (
        SELECT invoice_id, SUM(amount) AS paid_total
        FROM payments
        GROUP BY invoice_id
     ) p ON p.invoice_id = i.id
     WHERE i.status IN ('pending', 'overdue')"
);
$thisMonthSalary = (float) query_value(
    'SELECT COALESCE(SUM(amount), 0) FROM salaries WHERE salary_month >= ? AND salary_month < ?',
    [$monthStart, $nextMonthStart]
);
$thisMonthBills = (float) query_value(
    'SELECT COALESCE(SUM(amount), 0) FROM bills WHERE bill_month >= ? AND bill_month < ?',
    [$monthStart, $nextMonthStart]
);

$recentProjects = query_all(
    'SELECT p.*, c.name AS client_name
     FROM projects p
     LEFT JOIN clients c ON c.id = p.client_id
     ORDER BY p.created_at DESC
     LIMIT 5'
);

$pendingInvoices = query_all(
    "SELECT i.*, c.name AS client_name
     FROM invoices i
     LEFT JOIN clients c ON c.id = i.client_id
     WHERE i.status IN ('pending', 'overdue')
     ORDER BY i.due_date ASC
     LIMIT 5"
);

$monthlyRevenue = (float) query_value(
    'SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paid_on >= ? AND paid_on < ?',
    [$monthStart, $nextMonthStart]
);
$monthlyExpenses = (float) query_value(
    'SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date >= ? AND expense_date < ?',
    [$monthStart, $nextMonthStart]
);
$monthlyCosts = $monthlyExpenses + $thisMonthSalary + $thisMonthBills;
?>

<section class="stats-grid">
    <article class="stat-card">
        <span>Total Clients</span>
        <strong><?= e($totalClients) ?></strong>
        <small>CRM records</small>
    </article>
    <article class="stat-card">
        <span>Active Projects</span>
        <strong><?= e($activeProjects) ?></strong>
        <small>Planning and active work</small>
    </article>
    <article class="stat-card success">
        <span>Total Revenue</span>
        <strong><?= e(money($totalRevenue)) ?></strong>
        <small>Received payments</small>
    </article>
    <article class="stat-card danger">
        <span>Total Expenses</span>
        <strong><?= e(money($totalExpenses)) ?></strong>
        <small>Operating expenses</small>
    </article>
    <article class="stat-card <?= $netProfit >= 0 ? 'success' : 'danger' ?>">
        <span>Net Profit</span>
        <strong><?= e(money($netProfit)) ?></strong>
        <small>Revenue minus costs</small>
    </article>
    <article class="stat-card warning">
        <span>Pending Payments</span>
        <strong><?= e(money($pendingPayments)) ?></strong>
        <small>Pending and overdue invoices</small>
    </article>
    <article class="stat-card">
        <span>This Month Salary</span>
        <strong><?= e(money($thisMonthSalary)) ?></strong>
        <small><?= e(date('F Y')) ?></small>
    </article>
    <article class="stat-card">
        <span>This Month Rent/Bills</span>
        <strong><?= e(money($thisMonthBills)) ?></strong>
        <small><?= e(date('F Y')) ?></small>
    </article>
</section>

<section class="dashboard-grid">
    <article class="panel chart-panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">This Month</p>
                <h2>Revenue vs Expense</h2>
            </div>
        </div>
        <canvas id="financeChart" height="220" data-revenue="<?= e((string) $monthlyRevenue) ?>" data-expense="<?= e((string) $monthlyCosts) ?>"></canvas>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Priority</p>
                <h2>Pending Payments</h2>
            </div>
            <a class="ghost-link" href="finance">Open Finance</a>
        </div>
        <div class="table-wrap compact">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Due</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$pendingInvoices): ?>
                    <tr><td colspan="4" class="empty">No pending invoices.</td></tr>
                <?php endif; ?>
                <?php foreach ($pendingInvoices as $invoice): ?>
                    <tr>
                        <td><?= e($invoice['invoice_number']) ?></td>
                        <td><?= e($invoice['client_name'] ?? 'N/A') ?></td>
                        <td><?= e($invoice['due_date']) ?></td>
                        <td><?= e(money($invoice['amount'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">Operations</p>
            <h2>Recent Projects</h2>
        </div>
        <a class="ghost-link" href="projects">Manage Projects</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Status</th>
                    <th>Budget</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$recentProjects): ?>
                <tr><td colspan="5" class="empty">No projects found. Add your first project from the Projects module.</td></tr>
            <?php endif; ?>
            <?php foreach ($recentProjects as $project): ?>
                <tr>
                    <td><?= e($project['name']) ?></td>
                    <td><?= e($project['client_name'] ?? 'N/A') ?></td>
                    <td><span class="status <?= e(status_class($project['status'])) ?>"><?= e(ucfirst($project['status'])) ?></span></td>
                    <td><?= e(money($project['budget'])) ?></td>
                    <td><?= e($project['deadline'] ?: 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
