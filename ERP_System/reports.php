<?php
$pageTitle = 'Reports';
$activePage = 'reports';
$pageDescription = 'Business summaries and recommended ERP modules for the full system.';
require_once __DIR__ . '/includes/header.php';

$summary = [
    'Revenue' => query_value('SELECT COALESCE(SUM(amount), 0) FROM payments'),
    'Invoices Pending' => query_value(
        "SELECT COALESCE(SUM(GREATEST(i.amount - COALESCE(p.paid_total, 0), 0)), 0)
         FROM invoices i
         LEFT JOIN (
            SELECT invoice_id, SUM(amount) AS paid_total
            FROM payments
            GROUP BY invoice_id
         ) p ON p.invoice_id = i.id
         WHERE i.status IN ('pending', 'overdue')"
    ),
    'Expenses' => query_value('SELECT COALESCE(SUM(amount), 0) FROM expenses'),
    'Salaries Paid' => query_value("SELECT COALESCE(SUM(amount), 0) FROM salaries WHERE status = 'paid'"),
    'Bills Paid' => query_value("SELECT COALESCE(SUM(amount), 0) FROM bills WHERE status = 'paid'"),
];

$modules = [
    ['Dashboard', 'Total clients, active projects, revenue, expenses, net profit, pending payments, salary, rent and bills.'],
    ['Clients / CRM', 'Client profiles, company info, contact details, status, project history.'],
    ['Projects', 'Project status, client ownership, budget, start dates, deadlines.'],
    ['Revenue / Invoices', 'Invoices, payment collection, pending and overdue payment tracking.'],
    ['Expenses', 'Office and operational expenses with category and date.'],
    ['HR / Salaries', 'Employee salary records, monthly salary totals, paid and pending payroll.'],
    ['Rent & Bills', 'Office rent, electricity, internet, software subscriptions, due dates.'],
    ['Reports', 'Monthly profit/loss, client revenue, expense reports, payroll reports.'],
    ['Users & Roles', 'Admin, accountant, manager, and staff access control for a production ERP.'],
    ['Settings', 'Company profile, currency, tax settings, invoice prefix, backup options.'],
];
?>

<section class="stats-grid">
    <?php foreach ($summary as $label => $amount): ?>
        <article class="stat-card">
            <span><?= e($label) ?></span>
            <strong><?= e(money($amount)) ?></strong>
            <small>System total</small>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">ERP Blueprint</p>
            <h2>Recommended Modules</h2>
        </div>
    </div>
    <div class="module-list">
        <?php foreach ($modules as $module): ?>
            <article>
                <span><?= e($module[0]) ?></span>
                <p><?= e($module[1]) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
