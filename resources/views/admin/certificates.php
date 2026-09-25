<section class="card">
    <h1>Certificate Review</h1>

    <?php if (empty($certificates)): ?>
        <p>No certificates have been issued yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Certificate</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certificates as $certificate): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($certificate['certificate_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($certificate['student_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($certificate['course_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (float) ($certificate['score'] ?? 0) ?>%</td>
                        <td><?= htmlspecialchars((string) ($certificate['status'] ?? 'active'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <form method="POST" action="<?= htmlspecialchars(base_url('/admin/certificates/' . (int) ($certificate['id'] ?? 0) . '/status'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Support\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                <select name="status">
                                    <option value="active" <?= strtolower((string) ($certificate['status'] ?? 'active')) === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="revoked" <?= strtolower((string) ($certificate['status'] ?? 'active')) === 'revoked' ? 'selected' : '' ?>>Revoked</option>
                                    <option value="expired" <?= strtolower((string) ($certificate['status'] ?? 'active')) === 'expired' ? 'selected' : '' ?>>Expired</option>
                                    <option value="invalid" <?= strtolower((string) ($certificate['status'] ?? 'active')) === 'invalid' ? 'selected' : '' ?>>Invalid</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p>
        <a href="<?= htmlspecialchars(base_url('/dashboard'), ENT_QUOTES, 'UTF-8') ?>">Back to dashboard</a>
    </p>
</section>
