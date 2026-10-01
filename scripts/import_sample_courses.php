<?php

declare(strict_types=1);

$app = require __DIR__ . '/../bootstrap/app.php';
$pdo = $app['db'] ?? null;

if (!$pdo instanceof PDO) {
    fwrite(STDERR, "Database connection is required to import courses.\n");
    exit(1);
}

$courses = [
    [
        'title' => 'Python Programming Foundations',
        'slug' => 'python-programming-foundations',
        'description' => 'Learn core Python concepts through short lessons, examples, and a practical progression from first program to reusable functions.',
        'modules' => [
            [
                'title' => 'Start with Python',
                'description' => 'Understand the language and write your first small programs.',
                'lessons' => [
                    [
                        'title' => 'What Python Is and Where It Runs',
                        'summary' => 'A quick orientation to Python and the interpreter.',
                        'content' => "Python is a general-purpose programming language used for automation, data work, web services, and education. A Python interpreter reads your instructions and executes them in order.\n\nStart by opening a Python shell or creating a file ending in .py. Keep experiments small: change one line, run the program, and observe the result.\n\nOpen the official Python Tutorial PDF: https://docs.python.org/3/tutorial/tutorial.pdf",
                        'video' => 'https://www.youtube.com/watch?v=_uQrJ0TkZlc',
                    ],
                    [
                        'title' => 'Your First Program and Values',
                        'summary' => 'Print output and store information in variables.',
                        'content' => "A program can display a message with print(\"Hello, world!\"). Values such as text, whole numbers, and decimal numbers can be stored in named variables.\n\nUse names that explain what a value represents, such as learner_name or lesson_count. Python uses the value to determine its type, so a string like \"12\" is different from the number 12.",
                        'video' => null,
                    ],
                ],
            ],
            [
                'title' => 'Decisions, Repetition, and Functions',
                'description' => 'Control program flow and organize repeated work.',
                'lessons' => [
                    [
                        'title' => 'Make Decisions with Conditions',
                        'summary' => 'Choose what a program does using if and else.',
                        'content' => "An if statement runs a block only when its condition is true. Use elif for another condition and else for the remaining case. Indentation defines which lines belong to each block.\n\nTry a small exercise: compare a score with a pass mark and print a different message for each outcome. Test values on both sides of the boundary.",
                        'video' => null,
                    ],
                    [
                        'title' => 'Repeat Work with Loops and Functions',
                        'summary' => 'Use loops for repetition and functions for reusable steps.',
                        'content' => "A for loop processes each item in a sequence. A while loop repeats while its condition remains true, so make sure the condition can eventually change.\n\nA function groups a task behind a clear name. Define it with def, pass information through parameters, and return a result when another part of the program needs to use it.",
                        'video' => null,
                    ],
                ],
            ],
        ],
    ],
    [
        'title' => 'Web Development Fundamentals',
        'slug' => 'web-development-fundamentals',
        'description' => 'Build a strong foundation in how web pages are structured, styled, and made usable across devices.',
        'modules' => [
            [
                'title' => 'Structure Pages with HTML',
                'description' => 'Use semantic elements to give page content meaning.',
                'lessons' => [
                    [
                        'title' => 'How a Web Page Is Structured',
                        'summary' => 'Meet HTML elements, nesting, and page landmarks.',
                        'content' => "HTML describes the structure and meaning of page content. Elements are written with tags; many have an opening tag, content, and a closing tag.\n\nUse headings in a meaningful order, paragraphs for prose, and links for navigation. Semantic elements such as main, nav, and article help people and assistive technologies understand a page.\n\nRead the MDN HTML learning guide: https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Structuring_content",
                        'video' => 'https://www.youtube.com/watch?v=G3e-cpL7ofc',
                    ],
                    [
                        'title' => 'Create Clear Forms and Links',
                        'summary' => 'Connect pages and collect information accessibly.',
                        'content' => "Links should describe their destination, and form controls should have visible labels. Group related inputs and use the appropriate input type so browsers can provide useful validation and mobile keyboards.\n\nA useful check is to navigate the page using only a keyboard and confirm every control has a clear focus state.",
                        'video' => null,
                    ],
                ],
            ],
            [
                'title' => 'Style Responsive Interfaces with CSS',
                'description' => 'Apply visual hierarchy and adapt layouts to different screens.',
                'lessons' => [
                    [
                        'title' => 'CSS Selectors, Color, and Type',
                        'summary' => 'Connect styles to elements and build a readable visual hierarchy.',
                        'content' => "CSS rules select elements and apply declarations such as color, spacing, and font size. Prefer reusable classes for page components and keep related rules together.\n\nCheck contrast between text and its background, and use spacing consistently to show which items belong together.\n\nRead the MDN CSS styling guide: https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Styling_basics",
                        'video' => null,
                    ],
                    [
                        'title' => 'Responsive Layouts with Flexbox and Grid',
                        'summary' => 'Build layouts that adapt instead of overflowing.',
                        'content' => "Flexbox is useful for arranging items along one axis, while Grid handles rows and columns together. Combine either with flexible widths and media queries to adapt a layout to available space.\n\nTest at narrow widths and with longer-than-usual text. A responsive layout should remain readable without horizontal scrolling.",
                        'video' => null,
                    ],
                ],
            ],
        ],
    ],
    [
        'title' => 'JavaScript Programming Basics',
        'slug' => 'javascript-programming-basics',
        'description' => 'Practice JavaScript fundamentals, from values and decisions to functions and interactive browser behavior.',
        'modules' => [
            [
                'title' => 'JavaScript Essentials',
                'description' => 'Work with values, variables, and expressions.',
                'lessons' => [
                    [
                        'title' => 'Values, Variables, and Expressions',
                        'summary' => 'Represent information and combine values in JavaScript.',
                        'content' => "JavaScript represents text, numbers, booleans, and other values. Use const when a variable will not be reassigned and let when it will.\n\nExpressions combine values and operators to produce a result. Use descriptive names and small examples to make behavior easier to inspect.\n\nOpen the free Eloquent JavaScript book PDF: https://eloquentjavascript.net/Eloquent_JavaScript.pdf",
                        'video' => 'https://www.youtube.com/watch?v=PkZNo7MFNFg',
                    ],
                    [
                        'title' => 'Conditions and Reusable Functions',
                        'summary' => 'Choose an action and package logic into functions.',
                        'content' => "Conditional statements let a program respond to different values. Functions give a reusable name to a set of steps and can accept inputs through parameters.\n\nWrite a function that accepts a number and returns whether it is even. Test it with zero, a positive number, and a negative number.",
                        'video' => null,
                    ],
                ],
            ],
            [
                'title' => 'Working with the Browser',
                'description' => 'Connect JavaScript to page elements and user actions.',
                'lessons' => [
                    [
                        'title' => 'Select Elements and Handle Events',
                        'summary' => 'Respond to user actions without losing page clarity.',
                        'content' => "The browser Document Object Model (DOM) represents the page as elements that JavaScript can inspect and update. Select an element, then listen for an event such as a button click.\n\nKeep changes focused: update text or state in response to an action, and make sure the result is visible and understandable to keyboard and screen-reader users.",
                        'video' => null,
                    ],
                    [
                        'title' => 'Debug and Keep Code Readable',
                        'summary' => 'Use the browser console and small checks to find mistakes.',
                        'content' => "Read error messages from the first line and inspect the values your program is using. Browser developer tools let you pause execution, inspect variables, and test small expressions.\n\nAfter fixing a problem, repeat the action that exposed it and check a nearby case so the correction does not introduce a new issue.",
                        'video' => null,
                    ],
                ],
            ],
        ],
    ],
];

$findCourse = $pdo->prepare('SELECT id FROM courses WHERE slug = :slug LIMIT 1');
$insertCourse = $pdo->prepare(
    'INSERT INTO courses (title, slug, description, status, access_tier, created_by, created_at)
     VALUES (:title, :slug, :description, :status, :access_tier, NULL, NOW())'
);
$insertModule = $pdo->prepare(
    'INSERT INTO course_modules (course_id, title, description, sort_order)
     VALUES (:course_id, :title, :description, :sort_order)'
);
$insertLesson = $pdo->prepare(
    'INSERT INTO lessons (module_id, title, summary, content, video_url, file_path, sort_order)
     VALUES (:module_id, :title, :summary, :content, NULL, NULL, :sort_order)'
);
$insertBlock = $pdo->prepare(
    'INSERT INTO lesson_blocks (lesson_id, type, title, content, sort_order)
     VALUES (:lesson_id, :type, :title, :content, :sort_order)'
);

$imported = 0;
foreach ($courses as $course) {
    $findCourse->execute([':slug' => $course['slug']]);
    if ($findCourse->fetchColumn() !== false) {
        fwrite(STDOUT, 'Skipped existing course: ' . $course['title'] . "\n");
        continue;
    }

    $pdo->beginTransaction();
    try {
        $insertCourse->execute([
            ':title' => $course['title'],
            ':slug' => $course['slug'],
            ':description' => $course['description'],
            ':status' => 'published',
            ':access_tier' => 'regular',
        ]);
        $courseId = (int) $pdo->lastInsertId();

        foreach ($course['modules'] as $moduleIndex => $module) {
            $insertModule->execute([
                ':course_id' => $courseId,
                ':title' => $module['title'],
                ':description' => $module['description'],
                ':sort_order' => $moduleIndex + 1,
            ]);
            $moduleId = (int) $pdo->lastInsertId();

            foreach ($module['lessons'] as $lessonIndex => $lesson) {
                $insertLesson->execute([
                    ':module_id' => $moduleId,
                    ':title' => $lesson['title'],
                    ':summary' => $lesson['summary'],
                    ':content' => $lesson['content'],
                    ':sort_order' => $lessonIndex + 1,
                ]);
                $lessonId = (int) $pdo->lastInsertId();
                $blockOrder = 1;

                if ($lesson['video'] !== null) {
                    $insertBlock->execute([
                        ':lesson_id' => $lessonId,
                        ':type' => 'youtube',
                        ':title' => $lesson['title'] . ' video',
                        ':content' => $lesson['video'],
                        ':sort_order' => $blockOrder++,
                    ]);
                }

            }
        }

        $pdo->commit();
        $imported++;
        fwrite(STDOUT, 'Imported course: ' . $course['title'] . "\n");
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(STDERR, 'Failed to import ' . $course['title'] . ': ' . $exception->getMessage() . "\n");
        exit(1);
    }
}

fwrite(STDOUT, "Import complete. New courses: {$imported}\n");