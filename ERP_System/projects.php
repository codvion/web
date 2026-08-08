<?php
$pageTitle = 'Projects';
$activePage = 'projects';
$pageDescription = 'Track active projects, budgets, deadlines, and client ownership.';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) post_value('action', 'create');
    $projectId = (int) post_value('id', 0);

    if ($action === 'update' && $projectId > 0) {
        execute_query(
            'UPDATE projects SET client_id = ?, name = ?, status = ?, budget = ?, start_date = ?, deadline = ? WHERE id = ?',
            [
                (int) post_value('client_id'),
                trim((string) post_value('name')),
                (string) post_value('status', 'planning'),
                (float) post_value('budget', 0),
                post_value('start_date') ?: null,
                post_value('deadline') ?: null,
                $projectId,
            ]
        );
        redirect_to('projects?updated=1');
    }

    if ($action === 'delete' && $projectId > 0) {
        execute_query('DELETE FROM projects WHERE id = ?', [$projectId]);
        redirect_to('projects?deleted=1');
    }

    execute_query(
        'INSERT INTO projects (client_id, name, status, budget, start_date, deadline) VALUES (?, ?, ?, ?, ?, ?)',
        [
            (int) post_value('client_id'),
            trim((string) post_value('name')),
            (string) post_value('status', 'planning'),
            (float) post_value('budget', 0),
            post_value('start_date') ?: null,
            post_value('deadline') ?: null,
        ]
    );
    redirect_to('projects?saved=1');
}

$editId = (int) ($_GET['edit'] ?? 0);
$editingProject = $editId > 0 ? query_one('SELECT * FROM projects WHERE id = ?', [$editId]) : null;
$clients = query_all('SELECT id, name, company, status FROM clients ORDER BY name ASC');
$projects = query_all(
    'SELECT p.*, c.name AS client_name, c.company AS company
     FROM projects p
     LEFT JOIN clients c ON c.id = p.client_id
     ORDER BY p.created_at DESC'
);
?>

<section class="content-grid">
    <article class="panel form-panel <?= $editingProject ? 'editing' : '' ?>">
        <div class="panel-heading">
            <div>
                <p class="eyebrow"><?= $editingProject ? 'Update Work' : 'New Work' ?></p>
                <h2><?= $editingProject ? 'Edit Project' : 'Add Project' ?></h2>
            </div>
            <?php if ($editingProject): ?>
                <a class="ghost-link" href="projects">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="<?= $editingProject ? 'update' : 'create' ?>">
            <?php if ($editingProject): ?>
                <input type="hidden" name="id" value="<?= e($editingProject['id']) ?>">
            <?php endif; ?>
            <label>
                Project Name
                <input type="text" name="name" required value="<?= e($editingProject['name'] ?? '') ?>" placeholder="Website Redesign">
            </label>
            <label>
                Client
                <select name="client_id" required>
                    <option value="">Select client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e($client['id']) ?>" <?= selected((string) ($editingProject['client_id'] ?? ''), (string) $client['id']) ?>>
                            <?= e($client['name']) ?><?= $client['company'] ? ' - ' . e($client['company']) : '' ?><?= $client['status'] === 'inactive' ? ' (Inactive)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Status
                <select name="status">
                    <?php foreach (['planning', 'active', 'hold', 'completed', 'cancelled'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= selected((string) ($editingProject['status'] ?? 'planning'), $status) ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Budget
                <input type="number" name="budget" min="0" step="1000" value="<?= e($editingProject['budget'] ?? '0') ?>">
            </label>
            <label>
                Start Date
                <input type="date" name="start_date" value="<?= e($editingProject['start_date'] ?? '') ?>">
            </label>
            <label>
                Deadline
                <input type="date" name="deadline" value="<?= e($editingProject['deadline'] ?? '') ?>">
            </label>
            <div class="form-actions">
                <button type="submit" class="primary-button"><?= $editingProject ? 'Update Project' : 'Save Project' ?></button>
                <?php if ($editingProject): ?>
                    <a class="secondary-button" href="projects">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Portfolio</p>
                <h2>Project Summary</h2>
            </div>
        </div>
        <div class="mini-stats">
            <div><strong><?= e(count($projects)) ?></strong><span>Total Projects</span></div>
            <div><strong><?= e(count(array_filter($projects, fn ($project) => in_array($project['status'], ['planning', 'active'], true)))) ?></strong><span>Active Work</span></div>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">Delivery</p>
            <h2>All Projects</h2>
        </div>
        <input class="search-input" type="search" placeholder="Search projects" data-table-search="projectsTable">
    </div>
    <div class="table-wrap">
        <table id="projectsTable">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Status</th>
                    <th>Budget</th>
                    <th>Start</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$projects): ?>
                <tr><td colspan="7" class="empty">No projects yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= e($project['name']) ?></td>
                    <td><?= e($project['client_name'] ?? 'N/A') ?></td>
                    <td><span class="status <?= e(status_class($project['status'])) ?>"><?= e(ucfirst($project['status'])) ?></span></td>
                    <td><?= e(money($project['budget'])) ?></td>
                    <td><?= e($project['start_date'] ?: 'N/A') ?></td>
                    <td><?= e($project['deadline'] ?: 'N/A') ?></td>
                    <td>
                        <div class="action-group">
                            <a class="action-link" href="projects?edit=<?= e($project['id']) ?>">Edit</a>
                            <form method="post" class="inline-form" data-confirm="Delete this project? Linked invoices will keep the client value but lose the project link.">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($project['id']) ?>">
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
