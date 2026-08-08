<?php
$pageTitle = 'Clients';
$activePage = 'clients';
$pageDescription = 'CRM module for client profiles, contact details, and client status.';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) post_value('action', 'create');
    $clientId = (int) post_value('id', 0);

    if ($action === 'update' && $clientId > 0) {
        execute_query(
            'UPDATE clients SET name = ?, company = ?, email = ?, phone = ?, status = ? WHERE id = ?',
            [
                trim((string) post_value('name')),
                trim((string) post_value('company')),
                trim((string) post_value('email')),
                trim((string) post_value('phone')),
                (string) post_value('status', 'active'),
                $clientId,
            ]
        );
        redirect_to('clients?updated=1');
    }

    if ($action === 'status' && $clientId > 0) {
        execute_query('UPDATE clients SET status = ? WHERE id = ?', [(string) post_value('status', 'inactive'), $clientId]);
        redirect_to('clients?status=1');
    }

    execute_query(
        'INSERT INTO clients (name, company, email, phone, status) VALUES (?, ?, ?, ?, ?)',
        [
            trim((string) post_value('name')),
            trim((string) post_value('company')),
            trim((string) post_value('email')),
            trim((string) post_value('phone')),
            (string) post_value('status', 'active'),
        ]
    );
    redirect_to('clients?saved=1');
}

$editId = (int) ($_GET['edit'] ?? 0);
$editingClient = $editId > 0 ? query_one('SELECT * FROM clients WHERE id = ?', [$editId]) : null;
$clients = query_all(
    'SELECT c.*,
            COUNT(DISTINCT p.id) AS projects_count,
            COALESCE(SUM(i.amount), 0) AS invoices_total
     FROM clients c
     LEFT JOIN projects p ON p.client_id = c.id
     LEFT JOIN invoices i ON i.client_id = c.id
     GROUP BY c.id
     ORDER BY c.created_at DESC'
);
?>

<section class="content-grid">
    <article class="panel form-panel <?= $editingClient ? 'editing' : '' ?>">
        <div class="panel-heading">
            <div>
                <p class="eyebrow"><?= $editingClient ? 'Update Record' : 'New Record' ?></p>
                <h2><?= $editingClient ? 'Edit Client' : 'Add Client' ?></h2>
            </div>
            <?php if ($editingClient): ?>
                <a class="ghost-link" href="clients">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="<?= $editingClient ? 'update' : 'create' ?>">
            <?php if ($editingClient): ?>
                <input type="hidden" name="id" value="<?= e($editingClient['id']) ?>">
            <?php endif; ?>
            <label>
                Client Name
                <input type="text" name="name" required value="<?= e($editingClient['name'] ?? '') ?>" placeholder="Ali Khan">
            </label>
            <label>
                Company
                <input type="text" name="company" value="<?= e($editingClient['company'] ?? '') ?>" placeholder="ABC Solutions">
            </label>
            <label>
                Email
                <input type="email" name="email" value="<?= e($editingClient['email'] ?? '') ?>" placeholder="client@example.com">
            </label>
            <label>
                Phone
                <input type="text" name="phone" value="<?= e($editingClient['phone'] ?? '') ?>" placeholder="+92 300 0000000">
            </label>
            <label>
                Status
                <select name="status">
                    <option value="active" <?= selected((string) ($editingClient['status'] ?? 'active'), 'active') ?>>Active</option>
                    <option value="inactive" <?= selected((string) ($editingClient['status'] ?? ''), 'inactive') ?>>Inactive</option>
                </select>
            </label>
            <div class="form-actions">
                <button type="submit" class="primary-button"><?= $editingClient ? 'Update Client' : 'Save Client' ?></button>
                <?php if ($editingClient): ?>
                    <a class="secondary-button" href="clients">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Client Base</p>
                <h2>Client Summary</h2>
            </div>
        </div>
        <div class="mini-stats">
            <div><strong><?= e(count($clients)) ?></strong><span>Total Clients</span></div>
            <div><strong><?= e(count(array_filter($clients, fn ($client) => $client['status'] === 'active'))) ?></strong><span>Active</span></div>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">CRM</p>
            <h2>All Clients</h2>
        </div>
        <input class="search-input" type="search" placeholder="Search clients" data-table-search="clientsTable">
    </div>
    <div class="table-wrap">
        <table id="clientsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Projects</th>
                    <th>Invoice Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$clients): ?>
                <tr><td colspan="7" class="empty">No clients yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= e($client['name']) ?></td>
                    <td><?= e($client['company'] ?: 'N/A') ?></td>
                    <td><?= e($client['email'] ?: $client['phone'] ?: 'N/A') ?></td>
                    <td><span class="status <?= e(status_class($client['status'])) ?>"><?= e(ucfirst($client['status'])) ?></span></td>
                    <td><?= e($client['projects_count']) ?></td>
                    <td><?= e(money($client['invoices_total'])) ?></td>
                    <td>
                        <div class="action-group">
                            <a class="action-link" href="clients?edit=<?= e($client['id']) ?>">Edit</a>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="id" value="<?= e($client['id']) ?>">
                                <input type="hidden" name="status" value="<?= $client['status'] === 'active' ? 'inactive' : 'active' ?>">
                                <button type="submit" class="link-button <?= $client['status'] === 'active' ? 'danger-text' : '' ?>">
                                    <?= $client['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                </button>
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
