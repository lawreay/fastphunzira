<?php

use App\Core\Auth;
?>
<section class="landing-page">
    <div class="hero" data-reveal>
        <div class="hero-copy">
            <span class="eyebrow">Learn / Practice / Certify</span>
            <h1>Learn skills. Prove what you know.</h1>
            <p class="hero-lead">FastPhunzira brings courses, quizzes, final exams, results, and digital certificates into one simple learning journey.</p>
            <div class="hero-actions">
                <?php if (Auth::check()): ?>
                    <a class="btn" href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Go to dashboard</a>
                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Browse courses</a>
                <?php else: ?>
                    <a class="btn" href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Start learning</a>
                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Explore courses</a>
                <?php endif; ?>
            </div>
            <p class="hero-note">A clear path from learning to verified achievement.</p>
        </div>
        <div class="hero-panel" aria-label="FastPhunzira learning journey">
            <figure class="hero-visual">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&amp;fit=crop&amp;w=1200&amp;q=88" alt="Black students collaborating around a laptop" loading="eager">
                <figcaption class="hero-visual-caption"><span class="signal-dot"></span> Learning together, moving forward</figcaption>
            </figure>
            <div class="journey-card">
                <span class="journey-label">Your learning journey</span>
                <div class="journey-step is-active"><span>01</span><strong>Learn</strong><small>Lessons and course content</small></div>
                <div class="journey-step"><span>02</span><strong>Practice</strong><small>Quizzes and knowledge checks</small></div>
                <div class="journey-step"><span>03</span><strong>Examine</strong><small>Final assessment</small></div>
                <div class="journey-step"><span>04</span><strong>Certify</strong><small>Digital certificate</small></div>
            </div>
        </div>
    </div>

    <section class="section-block" data-reveal>
        <div class="section-heading"><span class="eyebrow">Why FastPhunzira</span><h2>Everything important stays in one learning flow.</h2><p>No maze of disconnected tools. Just the parts a learner actually needs.</p></div>
        <div class="feature-grid">
            <article class="feature-card"><span class="feature-number">01</span><h3>Learn at your pace</h3><p>Access structured courses, modules, and lessons from one place.</p></article>
            <article class="feature-card"><span class="feature-number">02</span><h3>Practice before the exam</h3><p>Use quizzes to reinforce concepts and prepare for formal assessment.</p></article>
            <article class="feature-card"><span class="feature-number">03</span><h3>Earn a verifiable certificate</h3><p>Successful learners can receive digital certificates with public verification.</p></article>
        </div>
    </section>

    <section class="learner-story" data-reveal>
        <div class="learner-story-image">
            <img src="https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&amp;fit=crop&amp;w=1000&amp;q=88" alt="White students studying together in a bright classroom" loading="lazy">
        </div>
        <div class="learner-story-copy">
            <span class="eyebrow">Made for momentum</span>
            <h2>Keep the next step visible.</h2>
            <p>From the first lesson to the final certificate, your progress stays clear, practical, and easy to return to.</p>
            <a class="text-link" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Find your next course <span aria-hidden="true">↗</span></a>
        </div>
    </section>

    <?php if (!empty($featured_courses)): ?>
        <section class="section-block">
            <div class="section-heading"><span class="eyebrow">Featured learning</span><h2>Start with a course.</h2><p>Explore published courses and choose where you want to begin.</p></div>
            <div class="course-grid">
                <?php foreach ($featured_courses as $course): ?>
                    <article class="course-card"><span class="status-badge">Published</span><h3><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>">View course</a></article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="section-block split-section">
        <div><span class="eyebrow">How it works</span><h2>A straightforward path from account to achievement.</h2></div>
        <div class="steps-list">
            <div><span>1</span><p><strong>Create an account.</strong> Register with your name, email, and password.</p></div>
            <div><span>2</span><p><strong>Choose a course.</strong> Browse available learning opportunities and enroll.</p></div>
            <div><span>3</span><p><strong>Learn and practice.</strong> Work through lessons and complete quizzes.</p></div>
            <div><span>4</span><p><strong>Take the exam.</strong> Complete the final assessment under the platform rules.</p></div>
            <div><span>5</span><p><strong>Receive your certificate.</strong> Eligible learners can verify their achievement publicly.</p></div>
        </div>
    </section>

    <section class="verification-cta">
        <div><span class="eyebrow">Certificate verification</span><h2>Need to check a FastPhunzira certificate?</h2><p>Use the certificate number and verification code to confirm an issued credential.</p></div>
        <a class="btn light" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Explore the platform</a>
    </section>
</section>
