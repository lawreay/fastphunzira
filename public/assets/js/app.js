document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelector('.nav-links');
    const menuToggle = document.querySelector('.menu-toggle');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function () {
            navLinks.classList.toggle('is-open');
        });
    }

    const slides = Array.from(document.querySelectorAll('.hero-slide'));
    const dots = Array.from(document.querySelectorAll('.dot'));

    if (slides.length && dots.length) {
        let currentSlide = 0;

        const showSlide = (index) => {
            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('active', slideIndex === index);
            });

            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('active', dotIndex === index);
            });
        };

        dots.forEach((dot, index) => {
            dot.addEventListener('click', function () {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        setInterval(function () {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 4500);
    }

    const filterButtons = Array.from(document.querySelectorAll('.filter-btn'));
    const courseCards = Array.from(document.querySelectorAll('.course-card'));

    filterButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const selectedFilter = button.dataset.filter;

            filterButtons.forEach((item) => item.classList.toggle('active', item === button));

            courseCards.forEach((card) => {
                const category = card.dataset.category;
                const shouldShow = selectedFilter === 'all' || category === selectedFilter;
                card.classList.toggle('hidden', !shouldShow);
            });
        });
    });

    const courseData = {
        'html-fundamentals': {
            category: 'Technology',
            title: 'HTML Fundamentals',
            description: 'Learn the structural basics of websites and create your first simple page.',
            level: 'Beginner',
            lessons: '8 lessons',
            outcomes: [
                'Understand HTML elements and page structure.',
                'Create headings, paragraphs and links.',
                'Use lists, tables and basic page layouts.',
                'Build your first simple webpage.'
            ],
            lessonPlan: [
                'Lesson 1: What is HTML?',
                'Lesson 2: Headings and text',
                'Lesson 3: Lists and links',
                'Lesson 4: Images and media',
                'Lesson 5: Tables and forms',
                'Lesson 6: Layout basics',
                'Lesson 7: Practice project',
                'Lesson 8: Review and recap'
            ]
        },
        'digital-marketing-basics': {
            category: 'Marketing',
            title: 'Digital Marketing Basics',
            description: 'Understand how businesses reach customers online and build clear marketing strategies.',
            level: 'Beginner',
            lessons: '6 lessons',
            outcomes: [
                'Understand the digital marketing landscape.',
                'Learn about audience and customer needs.',
                'Explore social media, email and content basics.',
                'Build a simple marketing plan.'
            ],
            lessonPlan: [
                'Lesson 1: What is digital marketing?',
                'Lesson 2: Target audiences',
                'Lesson 3: Social media basics',
                'Lesson 4: Content planning',
                'Lesson 5: Email marketing',
                'Lesson 6: Simple strategy review'
            ]
        },
        'introduction-to-business': {
            category: 'Business',
            title: 'Introduction to Business',
            description: 'Learn the essentials of starting, organizing and managing a small business idea.',
            level: 'Beginner',
            lessons: '7 lessons',
            outcomes: [
                'Understand what a business is and why it exists.',
                'Recognize key business functions and roles.',
                'Learn basic planning and customer thinking.',
                'Explore simple financial and operational concepts.'
            ],
            lessonPlan: [
                'Lesson 1: Business basics',
                'Lesson 2: Customers and value',
                'Lesson 3: Products and services',
                'Lesson 4: Operations',
                'Lesson 5: Money basics',
                'Lesson 6: Marketing essentials',
                'Lesson 7: Simple business idea plan'
            ]
        },
        'excel-basics': {
            category: 'Productivity',
            title: 'Excel Basics',
            description: 'Learn spreadsheets, formulas and practical tools for everyday productivity.',
            level: 'Beginner',
            lessons: '8 lessons',
            outcomes: [
                'Work with rows, columns and worksheets.',
                'Create simple formulas and sums.',
                'Format tables and organise data clearly.',
                'Use Excel for daily planning and reporting.'
            ],
            lessonPlan: [
                'Lesson 1: Excel interface',
                'Lesson 2: Entering data',
                'Lesson 3: Basic formulas',
                'Lesson 4: Formatting cells',
                'Lesson 5: Tables and sorting',
                'Lesson 6: Charts',
                'Lesson 7: Everyday tasks',
                'Lesson 8: Final activity'
            ]
        },
        'math-foundations': {
            category: 'Mathematics',
            title: 'Math Foundations',
            description: 'Strengthen core number skills and everyday problem solving for practical use.',
            level: 'Beginner',
            lessons: '5 lessons',
            outcomes: [
                'Build confidence with numbers and fractions.',
                'Use percentages and ratios in real situations.',
                'Practice basic algebraic thinking.',
                'Solve everyday word problems.'
            ],
            lessonPlan: [
                'Lesson 1: Whole numbers and decimals',
                'Lesson 2: Percentages',
                'Lesson 3: Ratios and proportions',
                'Lesson 4: Basic algebra',
                'Lesson 5: Word problem practice'
            ]
        },
        'design-thinking': {
            category: 'Technology',
            title: 'Design Thinking Basics',
            description: 'Learn a simple user-first process for solving real-world problems more clearly.',
            level: 'Beginner',
            lessons: '5 lessons',
            outcomes: [
                'Define real problems more clearly.',
                'Think from a user’s point of view.',
                'Generate simple ideas and solutions.',
                'Test a concept with basic feedback.'
            ],
            lessonPlan: [
                'Lesson 1: What is design thinking?',
                'Lesson 2: Understanding users',
                'Lesson 3: Brainstorming',
                'Lesson 4: Prototyping ideas',
                'Lesson 5: Feedback and iteration'
            ]
        }
    };

    const modal = document.querySelector('.course-modal');
    const modalCategory = document.getElementById('course-modal-category');
    const modalTitle = document.getElementById('course-modal-title');
    const modalDescription = document.getElementById('course-modal-description');
    const modalLevel = document.getElementById('course-modal-level');
    const modalLessons = document.getElementById('course-modal-lessons');
    const modalOutcomes = document.getElementById('course-modal-outcomes');
    const modalLessonList = document.getElementById('course-modal-lessons-list');
    const closeButton = document.querySelector('.modal-close');
    const backdrop = document.querySelector('[data-close-modal="true"]');

    const openModal = (courseId) => {
        const content = courseData[courseId];

        if (!content || !modal) {
            return;
        }

        modalCategory.textContent = content.category;
        modalTitle.textContent = content.title;
        modalDescription.textContent = content.description;
        modalLevel.textContent = content.level;
        modalLessons.textContent = content.lessons;

        modalOutcomes.innerHTML = content.outcomes.map((item) => `<li>${item}</li>`).join('');
        modalLessonList.innerHTML = content.lessonPlan.map((item) => `<li>${item}</li>`).join('');

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('.js-preview-course').forEach((button) => {
        button.addEventListener('click', function () {
            const card = button.closest('.course-card');
            if (!card) {
                return;
            }

            openModal(card.dataset.courseId);
        });
    });

    if (closeButton) {
        closeButton.addEventListener('click', closeModal);
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeModal);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    const faqItems = Array.from(document.querySelectorAll('.faq-item'));

    faqItems.forEach((item) => {
        const button = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');

        if (!button || !answer) {
            return;
        }

        button.addEventListener('click', function () {
            const isActive = item.classList.contains('active');

            faqItems.forEach((faq) => {
                faq.classList.remove('active');
                const faqAnswer = faq.querySelector('.faq-answer');
                if (faqAnswer) {
                    faqAnswer.style.maxHeight = null;
                }
            });

            if (!isActive) {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });

    const firstFaq = document.querySelector('.faq-item .faq-answer');
    if (firstFaq) {
        firstFaq.style.maxHeight = firstFaq.scrollHeight + 'px';
        document.querySelector('.faq-item').classList.add('active');
    }
});
