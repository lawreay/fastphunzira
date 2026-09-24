diff --git a/CHANGELOG.md b/CHANGELOG.md
index 19a98dc..a08a047 100644
--- a/CHANGELOG.md
+++ b/CHANGELOG.md
@@ -18,3 +18,6 @@
 ## Unreleased
 - Continuous documentation refinement and validation
 - Add implementation planning as development begins
+- Fixed a front-controller bug where route data (e.g. `$courses`, `$course`) was never made available to views, so course listings and other dynamic pages rendered empty
+- Rebuilt the public landing page with a hero, "how it works" steps, featured courses, feature highlights, and a call-to-action section
+- Polished the shared stylesheet: added design tokens (colors, spacing, radius), hover/focus states, and card/table refinements per the UI design system
diff --git a/public/assets/css/app.css b/public/assets/css/app.css
index e3f671f..2150278 100644
--- a/public/assets/css/app.css
+++ b/public/assets/css/app.css
@@ -1,8 +1,60 @@
+/* FastPhunzira — design tokens (see docs/11-UI-DESIGN-SYSTEM.md) */
+:root {
+    --color-primary: #2563eb;
+    --color-primary-dark: #1d4ed8;
+    --color-primary-light: #dbeafe;
+    --color-accent: #7c3aed;
+    --color-success: #16a34a;
+    --color-success-bg: #dcfce7;
+    --color-success-text: #166534;
+    --color-danger: #dc2626;
+    --color-danger-bg: #fee2e2;
+    --color-danger-text: #991b1b;
+    --color-warning: #d97706;
+    --color-info: #0369a1;
+    --color-info-bg: #e0f2fe;
+
+    --color-bg: #f6f8fb;
+    --color-surface: #ffffff;
+    --color-border: #e5e7eb;
+    --color-text: #1f2937;
+    --color-text-muted: #6b7280;
+    --color-heading: #0f172a;
+
+    --radius-sm: 6px;
+    --radius-md: 10px;
+    --radius-lg: 16px;
+    --shadow-card: 0 8px 30px rgba(15, 23, 42, 0.06);
+    --shadow-card-hover: 0 14px 34px rgba(15, 23, 42, 0.1);
+
+    --space-1: 4px;
+    --space-2: 8px;
+    --space-3: 12px;
+    --space-4: 16px;
+    --space-6: 24px;
+    --space-8: 32px;
+    --space-12: 48px;
+}
+
+*, *::before, *::after {
+    box-sizing: border-box;
+}
+
 body {
-    font-family: Arial, sans-serif;
-    background: #f6f8fb;
+    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
+    background: var(--color-bg);
     margin: 0;
-    color: #1f2937;
+    color: var(--color-text);
+    line-height: 1.55;
+}
+
+h1, h2, h3, h4 {
+    color: var(--color-heading);
+    line-height: 1.25;
+}
+
+a {
+    color: var(--color-primary);
 }
 
 .container {
@@ -12,7 +64,7 @@ body {
 }
 
 header {
-    background: #0f172a;
+    background: var(--color-heading);
     color: white;
     padding: 18px 0;
 }
@@ -22,6 +74,7 @@ nav {
     gap: 16px;
     align-items: center;
     justify-content: space-between;
+    flex-wrap: wrap;
 }
 
 .brand {
@@ -32,13 +85,20 @@ nav {
 nav a {
     color: white;
     text-decoration: none;
+    padding: 6px 4px;
+    border-radius: var(--radius-sm);
+}
+
+nav a:hover,
+nav a:focus-visible {
+    text-decoration: underline;
 }
 
 .card {
-    background: white;
-    border-radius: 12px;
+    background: var(--color-surface);
+    border-radius: var(--radius-lg);
     padding: 24px;
-    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
+    box-shadow: var(--shadow-card);
 }
 
 button, input, textarea, select {
@@ -47,23 +107,66 @@ button, input, textarea, select {
 
 .btn {
     display: inline-block;
-    background: #2563eb;
+    background: var(--color-primary);
     color: white;
     padding: 10px 18px;
     border: none;
-    border-radius: 8px;
+    border-radius: var(--radius-sm);
     cursor: pointer;
     text-decoration: none;
+    font-weight: 600;
+    transition: background-color 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
+}
+
+.btn:hover {
+    background: var(--color-primary-dark);
+}
+
+.btn:active {
+    transform: translateY(1px);
+}
+
+.btn:focus-visible,
+a:focus-visible,
+button:focus-visible,
+input:focus-visible,
+textarea:focus-visible,
+select:focus-visible {
+    outline: 3px solid var(--color-accent);
+    outline-offset: 2px;
 }
 
 .btn.secondary {
     background: #e2e8f0;
-    color: #0f172a;
+    color: var(--color-heading);
+}
+
+.btn.secondary:hover {
+    background: #cbd5e1;
 }
 
 .btn.light {
     background: white;
-    color: #0f172a;
+    color: var(--color-heading);
+}
+
+.btn.light:hover {
+    background: #f1f5f9;
+}
+
+.btn.large {
+    padding: 14px 26px;
+    font-size: 1.05rem;
+}
+
+.btn.outline {
+    background: transparent;
+    color: white;
+    border: 1px solid rgba(255, 255, 255, 0.6);
+}
+
+.btn.outline:hover {
+    background: rgba(255, 255, 255, 0.12);
 }
 
 .form-group {
@@ -82,8 +185,17 @@ select {
     width: 100%;
     padding: 10px 12px;
     border: 1px solid #d1d5db;
-    border-radius: 8px;
+    border-radius: var(--radius-sm);
     box-sizing: border-box;
+    transition: border-color 0.15s ease, box-shadow 0.15s ease;
+}
+
+input:focus,
+textarea:focus,
+select:focus {
+    border-color: var(--color-primary);
+    box-shadow: 0 0 0 3px var(--color-primary-light);
+    outline: none;
 }
 
 textarea {
@@ -92,18 +204,21 @@ textarea {
 
 .alert {
     padding: 12px 16px;
-    border-radius: 8px;
+    border-radius: var(--radius-sm);
     margin-bottom: 18px;
+    border: 1px solid transparent;
 }
 
 .alert.error {
-    background: #fee2e2;
-    color: #991b1b;
+    background: var(--color-danger-bg);
+    color: var(--color-danger-text);
+    border-color: #fecaca;
 }
 
 .alert.success {
-    background: #dcfce7;
-    color: #166534;
+    background: var(--color-success-bg);
+    color: var(--color-success-text);
+    border-color: #bbf7d0;
 }
 
 .course-grid {
@@ -114,24 +229,39 @@ textarea {
 }
 
 .course-card {
-    background: #fff;
-    border: 1px solid #e5e7eb;
-    border-radius: 12px;
+    background: var(--color-surface);
+    border: 1px solid var(--color-border);
+    border-radius: var(--radius-md);
     padding: 20px;
+    transition: box-shadow 0.15s ease, transform 0.15s ease;
+    display: flex;
+    flex-direction: column;
+}
+
+.course-card:hover {
+    box-shadow: var(--shadow-card-hover);
+    transform: translateY(-2px);
 }
 
 .course-card h3 {
     margin-top: 0;
 }
 
+.course-card p {
+    color: var(--color-text-muted);
+    flex-grow: 1;
+}
+
 .status-badge {
     display: inline-block;
-    background: #e0f2fe;
-    color: #075985;
+    background: var(--color-info-bg);
+    color: var(--color-info);
     border-radius: 999px;
     padding: 4px 10px;
     font-size: 0.8rem;
     margin-bottom: 12px;
+    font-weight: 600;
+    letter-spacing: 0.02em;
 }
 
 .inline-form {
@@ -146,13 +276,213 @@ textarea {
 
 .table th,
 .table td {
-    border-bottom: 1px solid #e5e7eb;
+    border-bottom: 1px solid var(--color-border);
     padding: 12px;
     text-align: left;
 }
 
+.table th {
+    color: var(--color-text-muted);
+    font-size: 0.85rem;
+    text-transform: uppercase;
+    letter-spacing: 0.03em;
+}
+
+.table tbody tr:hover {
+    background: #f9fafb;
+}
+
 .actions {
     display: flex;
     gap: 10px;
     flex-wrap: wrap;
 }
+
+/* --- Landing page --- */
+
+.hero {
+    background: linear-gradient(135deg, var(--color-heading) 0%, #1e3a8a 55%, var(--color-accent) 130%);
+    color: white;
+    border-radius: var(--radius-lg);
+    padding: 56px 40px;
+    margin-bottom: var(--space-12);
+}
+
+.hero-inner {
+    max-width: 640px;
+}
+
+.hero .eyebrow {
+    display: inline-block;
+    background: rgba(255, 255, 255, 0.15);
+    padding: 4px 12px;
+    border-radius: 999px;
+    font-size: 0.8rem;
+    font-weight: 600;
+    letter-spacing: 0.03em;
+    margin-bottom: var(--space-4);
+}
+
+.hero h1 {
+    color: white;
+    font-size: 2.4rem;
+    margin: 0 0 var(--space-4);
+}
+
+.hero p.lead {
+    font-size: 1.1rem;
+    color: rgba(255, 255, 255, 0.88);
+    margin: 0 0 var(--space-6);
+}
+
+.hero-actions {
+    display: flex;
+    gap: var(--space-3);
+    flex-wrap: wrap;
+}
+
+.section {
+    margin-bottom: var(--space-12);
+}
+
+.section-heading {
+    text-align: center;
+    max-width: 560px;
+    margin: 0 auto var(--space-8);
+}
+
+.section-heading h2 {
+    font-size: 1.7rem;
+    margin-bottom: var(--space-2);
+}
+
+.section-heading p {
+    color: var(--color-text-muted);
+    margin: 0;
+}
+
+.trust-stats {
+    display: grid;
+    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
+    gap: var(--space-4);
+}
+
+.trust-stat {
+    background: var(--color-surface);
+    border: 1px solid var(--color-border);
+    border-radius: var(--radius-md);
+    padding: var(--space-6) var(--space-4);
+    text-align: center;
+}
+
+.trust-stat .stat-number {
+    display: block;
+    font-size: 1.8rem;
+    font-weight: 700;
+    color: var(--color-primary);
+    margin-bottom: var(--space-1);
+}
+
+.trust-stat .stat-label {
+    color: var(--color-text-muted);
+    font-size: 0.9rem;
+}
+
+.steps {
+    display: grid;
+    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
+    gap: var(--space-4);
+    counter-reset: step;
+}
+
+.step-card {
+    background: var(--color-surface);
+    border: 1px solid var(--color-border);
+    border-radius: var(--radius-md);
+    padding: var(--space-6);
+    position: relative;
+}
+
+.step-card .step-number {
+    display: inline-flex;
+    align-items: center;
+    justify-content: center;
+    width: 32px;
+    height: 32px;
+    border-radius: 50%;
+    background: var(--color-primary-light);
+    color: var(--color-primary-dark);
+    font-weight: 700;
+    margin-bottom: var(--space-3);
+}
+
+.step-card h3 {
+    margin: 0 0 var(--space-2);
+    font-size: 1.05rem;
+}
+
+.step-card p {
+    color: var(--color-text-muted);
+    margin: 0;
+    font-size: 0.95rem;
+}
+
+.feature-grid {
+    display: grid;
+    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
+    gap: var(--space-4);
+}
+
+.feature-card {
+    padding: var(--space-4) 0;
+}
+
+.feature-card .feature-icon {
+    font-size: 1.6rem;
+    margin-bottom: var(--space-2);
+}
+
+.feature-card h3 {
+    font-size: 1rem;
+    margin: 0 0 var(--space-1);
+}
+
+.feature-card p {
+    color: var(--color-text-muted);
+    margin: 0;
+    font-size: 0.92rem;
+}
+
+.cta-banner {
+    background: var(--color-primary);
+    color: white;
+    border-radius: var(--radius-lg);
+    padding: var(--space-12) var(--space-6);
+    text-align: center;
+}
+
+.cta-banner h2 {
+    color: white;
+    margin: 0 0 var(--space-2);
+}
+
+.cta-banner p {
+    color: rgba(255, 255, 255, 0.88);
+    margin: 0 0 var(--space-6);
+}
+
+.empty-note {
+    color: var(--color-text-muted);
+    text-align: center;
+    padding: var(--space-8) 0;
+}
+
+@media (max-width: 640px) {
+    .hero {
+        padding: 40px 24px;
+    }
+
+    .hero h1 {
+        font-size: 1.9rem;
+    }
+}
diff --git a/public/index.php b/public/index.php
index 7ec2953..c81a433 100644
--- a/public/index.php
+++ b/public/index.php
@@ -76,6 +76,10 @@ if (!is_file($viewPath)) {
     exit;
 }
 
+// Make every key returned by the route handler (e.g. 'courses', 'course',
+// 'quiz') available to the view as its own variable.
+extract($result, EXTR_SKIP);
+
 ob_start();
 require $viewPath;
 $body = ob_get_clean();
diff --git a/resources/views/landing.php b/resources/views/landing.php
index 101a472..9370141 100644
--- a/resources/views/landing.php
+++ b/resources/views/landing.php
@@ -1,8 +1,151 @@
-<section class="card">
-    <h1>Welcome to FastPhunzira</h1>
-    <p>Learn, practice, examine, pass, certify, and verify.</p>
-    <p>
-        <a class="btn" href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Create account</a>
-        <a class="btn" href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">Login</a>
-    </p>
+<?php
+
+use App\Core\Auth;
+
+$featuredCourses = $featuredCourses ?? [];
+?>
+<section class="hero">
+    <div class="hero-inner">
+        <span class="eyebrow">Learn · Practice · Certify</span>
+        <h1>Learn a skill, sit the exam, get a certificate you can prove.</h1>
+        <p class="lead">
+            FastPhunzira brings courses, timed online examinations, automatic marking, and
+            digitally verifiable certificates into one simple platform — built for learners,
+            educators, and training institutions.
+        </p>
+        <div class="hero-actions">
+            <?php if (Auth::check()): ?>
+                <a class="btn large" href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Go to dashboard</a>
+                <a class="btn large outline" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Browse courses</a>
+            <?php else: ?>
+                <a class="btn large" href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Create free account</a>
+                <a class="btn large outline" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Browse courses</a>
+            <?php endif; ?>
+        </div>
+    </div>
 </section>
+
+<section class="section">
+    <div class="trust-stats">
+        <div class="trust-stat">
+            <span class="stat-number">100%</span>
+            <span class="stat-label">Auto-marked examinations</span>
+        </div>
+        <div class="trust-stat">
+            <span class="stat-number">&infin;</span>
+            <span class="stat-label">Publicly verifiable certificates</span>
+        </div>
+        <div class="trust-stat">
+            <span class="stat-number">24/7</span>
+            <span class="stat-label">Access to courses and results</span>
+        </div>
+        <div class="trust-stat">
+            <span class="stat-number">1</span>
+            <span class="stat-label">Simple learn-to-certify workflow</span>
+        </div>
+    </div>
+</section>
+
+<section class="section">
+    <div class="section-heading">
+        <h2>How FastPhunzira works</h2>
+        <p>A straightforward path from your first lesson to a certificate you can share.</p>
+    </div>
+    <div class="steps">
+        <div class="step-card">
+            <span class="step-number">1</span>
+            <h3>Enroll in a course</h3>
+            <p>Browse the catalogue and enroll in the course that matches what you want to learn.</p>
+        </div>
+        <div class="step-card">
+            <span class="step-number">2</span>
+            <h3>Study the lessons</h3>
+            <p>Work through modules and lessons at your own pace, and check yourself with practice quizzes.</p>
+        </div>
+        <div class="step-card">
+            <span class="step-number">3</span>
+            <h3>Take the exam</h3>
+            <p>Sit a timed final examination that is marked automatically the moment you submit it.</p>
+        </div>
+        <div class="step-card">
+            <span class="step-number">4</span>
+            <h3>Earn &amp; verify your certificate</h3>
+            <p>Pass and receive a digital certificate that anyone can verify online.</p>
+        </div>
+    </div>
+</section>
+
+<section class="section">
+    <div class="section-heading">
+        <h2>Featured courses</h2>
+        <p>A sample of what's available right now in the catalogue.</p>
+    </div>
+
+    <?php if ($featuredCourses !== []): ?>
+        <div class="course-grid">
+            <?php foreach ($featuredCourses as $course): ?>
+                <article class="course-card">
+                    <span class="status-badge"><?= htmlspecialchars((string) ($course['status'] ?? 'published'), ENT_QUOTES, 'UTF-8') ?></span>
+                    <h3><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
+                    <p><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
+                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>">View details</a>
+                </article>
+            <?php endforeach; ?>
+        </div>
+        <p style="text-align:center; margin-top: 24px;">
+            <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">See all courses</a>
+        </p>
+    <?php else: ?>
+        <p class="empty-note">
+            No courses are published yet — check back soon, or
+            <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">browse the catalogue</a>.
+        </p>
+    <?php endif; ?>
+</section>
+
+<section class="section">
+    <div class="section-heading">
+        <h2>Everything you need, in one place</h2>
+        <p>Built for individual learners and for institutions managing many of them.</p>
+    </div>
+    <div class="feature-grid">
+        <div class="feature-card">
+            <div class="feature-icon">📚</div>
+            <h3>Courses &amp; modules</h3>
+            <p>Content organized into clear modules and lessons.</p>
+        </div>
+        <div class="feature-card">
+            <div class="feature-icon">⏱️</div>
+            <h3>Timed examinations</h3>
+            <p>Exams run on the clock and mark themselves automatically.</p>
+        </div>
+        <div class="feature-card">
+            <div class="feature-icon">📊</div>
+            <h3>Results &amp; history</h3>
+            <p>Every attempt and result is kept for the student to review.</p>
+        </div>
+        <div class="feature-card">
+            <div class="feature-icon">🏆</div>
+            <h3>Digital certificates</h3>
+            <p>Certificates are generated automatically and can be checked publicly.</p>
+        </div>
+        <div class="feature-card">
+            <div class="feature-icon">🛠️</div>
+            <h3>Admin dashboard</h3>
+            <p>Manage courses, students, exams, and certificates from one place.</p>
+        </div>
+        <div class="feature-card">
+            <div class="feature-icon">📱</div>
+            <h3>Works everywhere</h3>
+            <p>A responsive interface that works on phones, tablets, and desktops.</p>
+        </div>
+    </div>
+</section>
+
+<?php if (!Auth::check()): ?>
+    <section class="cta-banner">
+        <h2>Ready to start learning?</h2>
+        <p>Create a free account and enroll in your first course today.</p>
+        <a class="btn light large" href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Create free account</a>
+    </section>
+<?php endif; ?>
diff --git a/routes/web.php b/routes/web.php
index ae9e945..5d3dbed 100644
--- a/routes/web.php
+++ b/routes/web.php
@@ -11,15 +11,17 @@ $progressRepository = $app['progressRepository'];
 $learningService = $app['enrollmentLearningService'];
 $quizService = $app['quizService'];
 $quizAttemptRepository = $app['quizAttemptRepository'];
+$courseService = $app['courseService'];
 
 use App\Core\Auth;
 use App\Support\Csrf;
 
 return [
-    ['GET', '/', function () {
+    ['GET', '/', function () use ($courseService) {
         return [
             'view' => 'landing',
-            'title' => 'FastPhunzira',
+            'title' => 'FastPhunzira — Learn, Get Certified, Get Ahead',
+            'featuredCourses' => array_slice($courseService->getPublishedCourses(), 0, 3),
         ];
     }],
     ['GET', '/courses', [$courseController, 'catalogue']],