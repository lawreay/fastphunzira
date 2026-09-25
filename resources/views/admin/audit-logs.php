<section class="card">
    <h1>Audit Logs</h1>

    <?php if (empty($logs)): ?>
        <p>No operational activity has been recorded yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>User</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($log['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($log['action'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) (($log['entity_type'] ?? '') . (!empty($log['entity_id']) ? ' #' . (int) $log['entity_id'] : '')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($log['user_id'] ?? 'system'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($log['ip_address'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p>
        <a href="<?= htmlspecialchars(base_url('/dashboard'), ENT_QUOTES, 'UTF-8') ?>">Back to dashboard</a>
    </p>
</section>
