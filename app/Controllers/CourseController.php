<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Services\CourseService;
use App\Support\Csrf;

final class CourseController
{
    public function __construct(private CourseService $courseService)
    {
    }

    public function catalogue(): array
    {
        return [
            'view' => 'courses/catalogue',
            'title' => 'Course Catalogue',
            'courses' => $this->courseService->getPublishedCourses(),
        ];
    }

    public function detail(int $id): array
    {
        $course = $this->courseService->getCourseDetail($id, Auth::userCan('courses.manage'));

        if ($course === null) {
            return [
                'view' => 'errors/not_found',
                'title' => 'Course not found',
            ];
        }

        return [
            'view' => 'courses/details',
            'title' => $course['title'],
            'course' => $course,
        ];
    }

    public function adminIndex(): array
    {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        return [
            'view' => 'admin/courses/index',
            'title' => 'Manage Courses',
            'courses' => $this->courseService->getAdminCourses(),
        ];
    }

    public function createForm(): array
    {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        return [
            'view' => 'admin/courses/form',
            'title' => 'Create Course',
            'course' => null,
        ];
    }

    public function store(array $input): array
    {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $result = $this->courseService->createCourse($input, (int) Auth::userId());

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return redirect_to('/admin/courses/create');
        }

        $_SESSION['flash_success'] = 'Course created successfully.';

        return redirect_to('/admin/courses');
    }

    public function editForm(int $id): array
    {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $course = $this->courseService->getCourseDetail($id, true);

        if ($course === null) {
            $_SESSION['flash_error'] = 'Course not found.';

            return redirect_to('/admin/courses');
        }

        return [
            'view' => 'admin/courses/form',
            'title' => 'Edit Course',
            'course' => $course,
        ];
    }

    public function update(int $id, array $input): array
    {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $result = $this->courseService->updateCourse($id, $input, (int) Auth::userId());

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return redirect_to('/admin/courses/' . $id . '/edit');
        }

        $_SESSION['flash_success'] = 'Course updated successfully.';

        return redirect_to('/admin/courses');
    }

    public function publish(): array
    {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $courseId = (int) ($_POST['id'] ?? 0);
        if ($courseId <= 0) {
            $_SESSION['flash_error'] = 'Course not found.';
            return redirect_to('/admin/courses');
        }

        $result = $this->courseService->updateCourse($courseId, ['status' => 'published'], (int) Auth::userId());
        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return redirect_to('/admin/courses');
        }

        $_SESSION['flash_success'] = 'Course published successfully.';
        return redirect_to('/admin/courses');
    }
}
