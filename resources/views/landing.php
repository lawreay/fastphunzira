<?php

use App\Core\Auth;
?>

<section class="hero modern-hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">student learning platform</span>
            <h1>Learn with purpose. Grow with confidence.</h1>
            <p>
                FastPhunzira brings together structured learning, practical practice, assessments and
                certification in one clear digital experience designed for modern students.
            </p>
            <div class="hero-actions">
                <a href="#courses" class="btn primary">Start learning</a>
                <a href="#how-it-works" class="btn secondary-light">See how it works</a>
            </div>
            <div class="hero-pills">
                <span>Learn</span>
                <span>Practice</span>
                <span>Certify</span>
            </div>
        </div>

        <div class="hero-preview" aria-label="Learning dashboard preview">
            <div class="preview-topbar">
                <div class="preview-brand">
                    <span class="mini-brand">F</span>
                    <span>FastPhunzira</span>
                </div>
                <span class="status-tag">Live</span>
            </div>

            <div class="preview-body">
                <div class="preview-left">
                    <span class="mini-label">Current path</span>
                    <h3>HTML Fundamentals</h3>
                    <div class="progress-line">
                        <span style="width: 72%;"></span>
                    </div>
                    <p>72% complete</p>
                    <div class="next-lesson">
                        <small>Next lesson</small>
                        <strong>CSS Basics</strong>
                    </div>
                </div>

                <div class="preview-right">
                    <div class="metric-box">
                        <span>Weekly goal</span>
                        <strong>4 lessons</strong>
                    </div>
                    <div class="metric-box">
                        <span>Quiz score</span>
                        <strong>88%</strong>
                    </div>
                    <button class="preview-btn" type="button">Continue learning</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container trust-row" aria-label="Key platform benefits">
        <div class="trust-item">
            <strong>300+</strong>
            <span>guided learning paths</span>
        </div>
        <div class="trust-item">
            <strong>4-step</strong>
            <span>learning flow</span>
        </div>
        <div class="trust-item">
            <strong>100%</strong>
            <span>digital certification</span>
        </div>
    </div>
</section>

<section class="section value-strip" id="about">
    <div class="section-heading centered">
        <span class="eyebrow dark">A smarter path from learning to certification</span>
        <h2>Everything needed to learn, practice, and prove skills.</h2>
    </div>

    <div class="value-grid">
        <article class="value-card">
            <span class="value-step">01</span>
            <h3>Learn</h3>
            <p>Follow carefully organized courses that turn information into practical understanding.</p>
        </article>
        <article class="value-card">
            <span class="value-step">02</span>
            <h3>Practice</h3>
            <p>Use quizzes, tasks and guided exercises to reinforce knowledge as you move forward.</p>
        </article>
        <article class="value-card">
            <span class="value-step">03</span>
            <h3>Verify</h3>
            <p>Complete assessments and receive clear results that reflect progress and achievement.</p>
        </article>
    </div>
</section>

<section class="section quick-learning">
    <div class="section-heading centered">
        <span class="eyebrow dark">What do you want to learn?</span>
        <h2>Choose a learning path that fits you.</h2>
    </div>

    <div class="filter-bar" aria-label="Course categories">
        <button class="filter-btn active" type="button" data-filter="all">All</button>
        <button class="filter-btn" type="button" data-filter="technology">Technology</button>
        <button class="filter-btn" type="button" data-filter="business">Business</button>
        <button class="filter-btn" type="button" data-filter="marketing">Marketing</button>
        <button class="filter-btn" type="button" data-filter="mathematics">Mathematics</button>
        <button class="filter-btn" type="button" data-filter="productivity">Productivity</button>
    </div>
</section>

<section class="section" id="courses">
    <div class="section-heading left-heading">
        <span class="eyebrow dark">Start with the basics</span>
        <h2>Build your next skill with beginner-friendly courses.</h2>
    </div>

    <div class="course-grid">
        <article class="course-card" data-category="technology" data-course-id="html-fundamentals">
            <div class="card-topline">
                <span class="meta-tag green">Technology</span>
                <span class="course-icon">⌨️</span>
            </div>
            <h3>HTML Fundamentals</h3>
            <p>Learn the basic structure of websites and create your first web page.</p>
            <div class="card-meta">
                <span>Beginner</span>
                <span>8 Lessons</span>
            </div>
            <button class="course-link js-preview-course" type="button">Start Course</button>
        </article>

        <article class="course-card" data-category="marketing" data-course-id="digital-marketing-basics">
            <div class="card-topline">
                <span class="meta-tag gold">Marketing</span>
                <span class="course-icon">📣</span>
            </div>
            <h3>Digital Marketing Basics</h3>
            <p>Learn the foundations of digital marketing and how businesses reach customers online.</p>
            <div class="card-meta">
                <span>Beginner</span>
                <span>6 Lessons</span>
            </div>
            <button class="course-link js-preview-course" type="button">Start Course</button>
        </article>

        <article class="course-card" data-category="business" data-course-id="introduction-to-business">
            <div class="card-topline">
                <span class="meta-tag blue">Business</span>
                <span class="course-icon">💼</span>
            </div>
            <h3>Introduction to Business</h3>
            <p>Understand the basic principles behind starting and managing a business.</p>
            <div class="card-meta">
                <span>Beginner</span>
                <span>7 Lessons</span>
            </div>
            <button class="course-link js-preview-course" type="button">Start Course</button>
        </article>

        <article class="course-card" data-category="productivity" data-course-id="excel-basics">
            <div class="card-topline">
                <span class="meta-tag purple">Productivity</span>
                <span class="course-icon">📊</span>
            </div>
            <h3>Excel Basics</h3>
            <p>Learn spreadsheets, formulas and useful everyday Excel skills.</p>
            <div class="card-meta">
                <span>Beginner</span>
                <span>8 Lessons</span>
            </div>
            <button class="course-link js-preview-course" type="button">Start Course</button>
        </article>

        <article class="course-card" data-category="mathematics" data-course-id="math-foundations">
            <div class="card-topline">
                <span class="meta-tag orange">Mathematics</span>
                <span class="course-icon">📐</span>
            </div>
            <h3>Math Foundations</h3>
            <p>Build stronger number sense, percentages and problem-solving confidence.</p>
            <div class="card-meta">
                <span>Beginner</span>
                <span>5 Lessons</span>
            </div>
            <button class="course-link js-preview-course" type="button">Start Course</button>
        </article>

        <article class="course-card" data-category="technology" data-course-id="design-thinking">
            <div class="card-topline">
                <span class="meta-tag teal">Technology</span>
                <span class="course-icon">✨</span>
            </div>
            <h3>Design Thinking Basics</h3>
            <p>Understand simple, user-focused ideas for solving everyday problems.</p>
            <div class="card-meta">
                <span>Beginner</span>
                <span>5 Lessons</span>
            </div>
            <button class="course-link js-preview-course" type="button">Start Course</button>
        </article>
    </div>
</section>

<section class="section how-it-works" id="how-it-works">
    <div class="section-heading centered">
        <span class="eyebrow dark">How it works</span>
        <h2>Learning doesn't have to be complicated.</h2>
    </div>

    <div class="steps-grid">
        <div class="step-item">
            <span class="step-number">01</span>
            <h3>Choose a course</h3>
            <p>Pick a topic that matches your goals and interests.</p>
        </div>
        <div class="step-item">
            <span class="step-number">02</span>
            <h3>Follow the path</h3>
            <p>Work through lessons in a clear, manageable sequence.</p>
        </div>
        <div class="step-item">
            <span class="step-number">03</span>
            <h3>Practice regularly</h3>
            <p>Use quick tasks and quizzes to sharpen what you learn.</p>
        </div>
        <div class="step-item">
            <span class="step-number">04</span>
            <h3>Track progress</h3>
            <p>Keep an eye on what you’ve completed and what’s next.</p>
        </div>
    </div>
</section>

<section class="section feature-overview">
    <div class="section-heading centered">
        <span class="eyebrow dark">Everything in one place</span>
        <h2>Built for practical learning.</h2>
    </div>

    <div class="feature-grid">
        <article class="feature-panel wide">
            <div class="feature-icon">📚</div>
            <h3>Structured courses</h3>
            <p>Each course is designed to be easy to follow, practical and clearly organized.</p>
        </article>
        <article class="feature-panel">
            <div class="feature-icon">🧠</div>
            <h3>Learning materials</h3>
            <p>Short lessons, examples and editable study material in one space.</p>
        </article>
        <article class="feature-panel">
            <div class="feature-icon">✅</div>
            <h3>Practice tasks</h3>
            <p>Build confidence with hands-on activities and quick learning checks.</p>
        </article>
        <article class="feature-panel">
            <div class="feature-icon">📈</div>
            <h3>Progress tracking</h3>
            <p>See what you’ve completed and stay focused on your next milestone.</p>
        </article>
        <article class="feature-panel">
            <div class="feature-icon">🧾</div>
            <h3>Student dashboard</h3>
            <p>Keep courses, progress and goals in a simple student-first workspace.</p>
        </article>
    </div>
</section>

<section class="section dashboard-showcase">
    <div class="section-heading left-heading">
        <span class="eyebrow dark">Student experience</span>
        <h2>Your learning, all in one place.</h2>
    </div>

    <div class="dashboard-shell" aria-label="Student dashboard preview">
        <aside class="dashboard-sidebar">
            <div class="sidebar-header">FastPhunzira</div>
            <ul>
                <li class="active">Overview</li>
                <li>My Courses</li>
                <li>Progress</li>
                <li>Quizzes</li>
                <li>Certificates</li>
            </ul>
        </aside>

        <div class="dashboard-main">
            <div class="dashboard-topbar">
                <div>
                    <span class="mini-label">Continue Learning</span>
                    <h3>HTML Fundamentals</h3>
                </div>
                <button type="button" class="btn ghost-btn">Open Course</button>
            </div>

            <div class="stat-row">
                <div class="stat-box">
                    <span>Completed</span>
                    <strong>6 lessons</strong>
                </div>
                <div class="stat-box">
                    <span>Quiz score</span>
                    <strong>84%</strong>
                </div>
                <div class="stat-box">
                    <span>Certificates</span>
                    <strong>1 earned</strong>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="panel-header">
                    <h4>Recent courses</h4>
                    <span>Updated today</span>
                </div>
                <div class="recent-item">
                    <div>
                        <strong>HTML Fundamentals</strong>
                        <small>Lesson 5 of 8</small>
                    </div>
                    <div class="mini-progress"><span style="width: 65%;"></span></div>
                </div>
                <div class="recent-item">
                    <div>
                        <strong>Digital Marketing Basics</strong>
                        <small>Lesson 2 of 6</small>
                    </div>
                    <div class="mini-progress"><span style="width: 35%;"></span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section teacher-cta" id="teachers">
    <div class="teacher-copy">
        <span class="eyebrow light">For Teachers</span>
        <h2>Have something worth teaching?</h2>
        <p>
            FastPhunzira gives teachers a cleaner way to organize learning content and support students
            through practical, structured lessons.
        </p>
        <a href="#" class="btn primary">For Teachers</a>
    </div>
</section>

<section class="section faq-section">
    <div class="section-heading centered">
        <span class="eyebrow dark">Common questions</span>
        <h2>Everything a learner needs to know.</h2>
    </div>

    <div class="faq-list">
        <div class="faq-item active">
            <button class="faq-question" type="button">
                What can I learn on FastPhunzira?
                <span>+</span>
            </button>
            <div class="faq-answer">
                <p>FastPhunzira is designed around practical learning in areas like technology, business, productivity and digital skills.</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question" type="button">
                Is it beginner friendly?
                <span>+</span>
            </button>
            <div class="faq-answer">
                <p>Yes. The platform is built to feel clear, approachable and easy to follow for students starting at a beginner level.</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question" type="button">
                Will I be able to track my progress?
                <span>+</span>
            </button>
            <div class="faq-answer">
                <p>The product concept includes a simple student dashboard to help learners stay on top of course progress and upcoming work.</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question" type="button">
                Can teachers use the platform too?
                <span>+</span>
            </button>
            <div class="faq-answer">
                <p>Yes. The future product is planned to support both learner progress and teacher-led content management.</p>
            </div>
        </div>
    </div>
</section>

<section class="section final-cta">
    <div class="cta-inner">
        <div>
            <span class="eyebrow dark">Start today</span>
            <h2>Start with one course.</h2>
        </div>
        <p>Pick something you have always wanted to learn and take the first lesson.</p>
        <a href="#courses" class="btn primary">Explore Courses</a>
    </div>
</section>

<div class="course-modal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="true"></div>
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="course-modal-title">
        <button class="modal-close" type="button" aria-label="Close preview">×</button>
        <div class="modal-content">
            <div class="modal-heading">
                <span id="course-modal-category" class="meta-tag modal-category">Technology</span>
                <h3 id="course-modal-title">HTML Fundamentals</h3>
                <p id="course-modal-description">Learn the structure of the web and build your first page.</p>
            </div>

            <div class="modal-grid">
                <div>
                    <p class="meta-label">Level</p>
                    <strong id="course-modal-level">Beginner</strong>
                </div>
                <div>
                    <p class="meta-label">Lessons</p>
                    <strong id="course-modal-lessons">8 lessons</strong>
                </div>
            </div>

            <div class="modal-block">
                <h4>What you'll learn</h4>
                <ul id="course-modal-outcomes"></ul>
            </div>

            <div class="modal-block">
                <h4>Lessons</h4>
                <ol id="course-modal-lessons-list"></ol>
            </div>

            <button class="btn primary full-width" type="button">Start Course</button>
        </div>
    </div>
</div>
