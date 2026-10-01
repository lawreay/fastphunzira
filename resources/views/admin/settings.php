<?php
use App\Support\Csrf;
?>
<section class="settings-page">
    <div class="page-heading">
        <div>
            <span class="eyebrow">Administration</span>
            <h1>Platform Settings</h1>
            <p>Configure membership pricing, payments, and email delivery.</p>
        </div>
    </div>

    <div class="settings-grid">
        <section class="settings-card">
            <div class="settings-card-heading">
                <span class="settings-icon">01</span>
                <div><h2>Student membership</h2><p>Regular access is free. Premium access unlocks premium courses.</p></div>
            </div>

            <form method="POST" action="<?= htmlspecialchars(base_url('admin/settings'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="premium_price">Premium price</label>
                        <input id="premium_price" name="premium_price" type="number" min="0" step="0.01" value="<?= htmlspecialchars(number_format((float) $premiumPrice, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="premium_currency">Currency</label>
                        <input id="premium_currency" name="premium_currency" type="text" maxlength="3" value="<?= htmlspecialchars((string) $premiumCurrency, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="premium_duration_days">Premium duration (days)</label>
                    <input id="premium_duration_days" name="premium_duration_days" type="number" min="1" value="<?= (int) $premiumDurationDays ?>" required>
                </div>

                <button class="btn" type="submit">Save membership settings</button>
            </form>
        </section>

        <section class="settings-card">
            <div class="settings-card-heading">
                <span class="settings-icon">02</span>
                <div><h2>PayChangu</h2><p>Payment credentials remain in server environment configuration.</p></div>
            </div>

            <div class="settings-status">
                <div><span>Integration</span><strong><?= $payChanguEnabled ? 'Enabled' : 'Disabled' ?></strong></div>
                <div><span>Mode</span><strong><?= htmlspecialchars(strtoupper((string) $payChanguMode), ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Public key</span><strong><?= $payChanguPublicConfigured ? 'Configured' : 'Not configured' ?></strong></div>
                <div><span>Secret key</span><strong><?= $payChanguSecretConfigured ? 'Configured' : 'Not configured' ?></strong></div>
                <div><span>Webhook secret</span><strong><?= $payChanguWebhookConfigured ? 'Configured' : 'Not configured' ?></strong></div>
                <div><span>Currency</span><strong><?= htmlspecialchars((string) $payChanguCurrency, ENT_QUOTES, 'UTF-8') ?></strong></div>
            </div>

            <div class="notice-panel">
                <strong>Server configuration</strong>
                <span>Set PAYCHANGU_ENABLED, PAYCHANGU_MODE, PAYCHANGU_CURRENCY, PAYCHANGU_PUBLIC_KEY, PAYCHANGU_SECRET_KEY and PAYCHANGU_WEBHOOK_SECRET in the private server environment. Never store provider secrets in GitHub or in the settings table.</span>
            </div>

            <div class="settings-links">
                <a href="https://developer.paychangu.com/docs/api-keys" target="_blank" rel="noopener">PayChangu API keys</a>
                <a href="https://developer.paychangu.com/docs/webhooks" target="_blank" rel="noopener">PayChangu webhooks</a>
            </div>

            <dl class="settings-endpoints">
                <div><dt>Return URL</dt><dd><?= htmlspecialchars(base_url('payments/paychangu/callback'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                <div><dt>Webhook URL</dt><dd><?= htmlspecialchars(base_url('payments/paychangu/webhook'), ENT_QUOTES, 'UTF-8') ?></dd></div>
            </dl>
        </section>

        <section class="settings-card">
            <div class="settings-card-heading">
                <span class="settings-icon">03</span>
                <div><h2>Email delivery (SMTP)</h2><p>SMTP credentials stay in the private server environment.</p></div>
            </div>

            <div class="settings-status">
                <div><span>Delivery</span><strong><?= $smtpConfigured ? 'Ready' : 'Not configured' ?></strong></div>
                <div><span>SMTP host</span><strong><?= htmlspecialchars((string) ($mailConfig['host'] ?? 'Not set'), ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Port / encryption</span><strong><?= (int) ($mailConfig['port'] ?? 0) ?> / <?= htmlspecialchars(strtoupper((string) ($mailConfig['encryption'] ?? 'none')), ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Authentication</span><strong><?= trim((string) ($mailConfig['username'] ?? '')) !== '' ? 'Configured' : 'Not configured' ?></strong></div>
            </div>

            <div class="notice-panel">
                <strong>Private server configuration</strong>
                <span>Set MAIL_ENABLED=true, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION and MAIL_FROM_ADDRESS in .env or your host's secret manager. Never paste credentials into this screen.</span>
            </div>

            <form method="POST" action="<?= htmlspecialchars(base_url('admin/settings/test-email'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group">
                    <label for="test_email">Send a test email to</label>
                    <input id="test_email" name="test_email" type="email" autocomplete="email" required>
                </div>
                <button class="btn secondary" type="submit" <?= $smtpConfigured ? '' : 'disabled' ?>>Send test email</button>
            </form>
        </section>
    </div>

    <section class="settings-card settings-note">
        <div class="settings-card-heading">
            <span class="settings-icon">04</span>
            <div><h2>Access model</h2><p>Use the course editor to mark individual courses as Regular or Premium.</p></div>
        </div>
        <div class="access-model">
            <div><strong>Regular student</strong><span>Can enroll in regular courses.</span></div>
            <div><strong>Premium student</strong><span>Can enroll in regular and premium courses while the membership is active.</span></div>
        </div>
    </section>
</section>
