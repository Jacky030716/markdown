<?php
// This file defines API routes for administrator functions related to course management,
// including lecturer assignment and student enrollments.

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {

    /**
     * Route: GET /api/v1/admin/courses
     * Description: Retrieves all courses with their assigned lecturer's name.
     * Returns:
     * - 200 OK: JSON array of course objects including lecturer details.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/courses', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');

        try {
            $stmt = $pdo->prepare('
                SELECT
                    c.id,
                    c.course_code,
                    c.course_name,
                    c.credit_hours,
                    c.lecturer_id,
                    c.semester,
                    c.academic_year,
                    c.is_active,
                    l.name AS lecturer_name,
                    l.lecturer_id AS lecturer_staff_id -- lecturer_id from lecturers table
                FROM
                    courses AS c
                LEFT JOIN
                    lecturers AS l ON c.lecturer_id = l.id
                ORDER BY c.course_code ASC
            ');
            $stmt->execute();
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add student count for each course
            $enrollmentCountsStmt = $pdo->prepare('
                SELECT course_id, COUNT(student_id) AS student_count
                FROM enrollments
                WHERE status = "enrolled"
                GROUP BY course_id
            ');
            $enrollmentCountsStmt->execute();
            $enrollmentCounts = $enrollmentCountsStmt->fetchAll(PDO::FETCH_KEY_PAIR); // [course_id => count]

            foreach ($courses as &$course) {
                $course['student_count'] = (int)($enrollmentCounts[$course['id']] ?? 0);
            }
            unset($course); // Break the reference

            $response->getBody()->write(json_encode(['data' => $courses, 'status' => 'success']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            error_log("Database error fetching all courses for admin: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("Application error fetching all courses for admin: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: PATCH /api/v1/admin/courses/{course_id}/assign-lecturer
     * Description: Assigns a lecturer to a course or unassigns them.
     * Parameters:
     * - course_id (int): The ID of the course.
     * Request Body: JSON object with `lecturer_id` (int|null).
     * Returns:
     * - 200 OK: JSON message confirming update.
     * - 400 Bad Request: If `lecturer_id` is invalid.
     * - 404 Not Found: If course or lecturer does not exist.
     * - 500 Internal Server Error: For database or unexpected errors.
     */
    $group->patch('/courses/{course_id}/assign-lecturer', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $courseId = $args['course_id'];
        $data = $request->getParsedBody();
        $lecturerId = $data['lecturer_id'] ?? null; // Can be null to unassign

        try {
            // Check if course exists
            $stmtCourse = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id');
            $stmtCourse->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmtCourse->execute();
            if ($stmtCourse->fetchColumn() === 0) {
                $response->getBody()->write(json_encode(['message' => "Course ID {$courseId} not found."]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // If a lecturer is being assigned, verify the lecturer ID
            if ($lecturerId !== null) {
                $stmtLecturer = $pdo->prepare('SELECT COUNT(*) FROM lecturers WHERE id = :lecturer_id');
                $stmtLecturer->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
                $stmtLecturer->execute();
                if ($stmtLecturer->fetchColumn() === 0) {
                    $response->getBody()->write(json_encode(['message' => "Lecturer ID {$lecturerId} not found."]));
                    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
                }
            }

            // Update the course with the new lecturer_id
            $stmtUpdate = $pdo->prepare('UPDATE courses SET lecturer_id = :lecturer_id, updated_at = NOW() WHERE id = :course_id');
            if ($lecturerId === null) {
                $stmtUpdate->bindValue(':lecturer_id', null, PDO::PARAM_NULL);
            } else {
                $stmtUpdate->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
            }
            $stmtUpdate->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $response->getBody()->write(json_encode(['message' => 'Lecturer assigned successfully.', 'status' => 'success']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            error_log("Database error assigning lecturer to course {$courseId}: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("Application error assigning lecturer to course {$courseId}: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: GET /api/v1/admin/courses/{course_id}/enrollments
     * Description: Fetches all students currently enrolled in a specific course.
     * Parameters:
     * - course_id (int): The ID of the course.
     * Returns:
     * - 200 OK: JSON array of enrolled student objects (id, name, matric_no).
     * - 404 Not Found: If the course does not exist.
     * - 500 Internal Server Error: For database or unexpected errors.
     */
    $group->get('/courses/{course_id}/enrollments', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $courseId = $args['course_id'];

        try {
            // Check if course exists
            $stmtCourse = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id');
            $stmtCourse->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmtCourse->execute();
            if ($stmtCourse->fetchColumn() === 0) {
                $response->getBody()->write(json_encode(['message' => "Course ID {$courseId} not found."]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $stmt = $pdo->prepare('
                SELECT
                    u.id,
                    s.name,
                    s.matric_no
                FROM
                    enrollments AS e
                JOIN
                    students AS s ON e.student_id = s.id
                JOIN 
                    users AS u ON s.user_id = u.id
                WHERE
                    e.course_id = :course_id AND e.status = "enrolled"
                ORDER BY s.name ASC
            ');
            $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmt->execute();
            $enrolledStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode(['data' => $enrolledStudents, 'status' => 'success']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            error_log("Database error fetching enrollments for course {$courseId}: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("Application error fetching enrollments for course {$courseId}: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: POST /api/v1/admin/courses/{course_id}/enroll-students
     * Description: Enrolls multiple students into a specified course.
     * Request Body: JSON object with `student_ids` (array of int).
     * Returns:
     * - 200 OK: JSON message confirming successful enrollments.
     * - 400 Bad Request: If `student_ids` is missing or not an array.
     * - 404 Not Found: If course or some students do not exist.
     * - 409 Conflict: If students are already enrolled.
     * - 500 Internal Server Error: For database or unexpected errors.
     */
    $group->post('/courses/{course_id}/enroll-students', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $courseId = $args['course_id'];
        $data = $request->getParsedBody();
        $studentIds = $data['student_ids'] ?? [];

        if (!is_array($studentIds)) {
            $response->getBody()->write(json_encode(['message' => "Bad Request: 'student_ids' must be an array."]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        if (empty($studentIds)) {
            $response->getBody()->write(json_encode(['message' => "No students provided for enrollment."]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $pdo->beginTransaction();

            // Check if course exists
            $stmtCourse = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id');
            $stmtCourse->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmtCourse->execute();
            if ($stmtCourse->fetchColumn() === 0) {
                $pdo->rollBack();
                $response->getBody()->write(json_encode(['message' => "Course ID {$courseId} not found."]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $successCount = 0;
            $failedCount = 0;
            $failedStudents = [];

            foreach ($studentIds as $studentId) {
                // Check if student exists
                $stmtStudent = $pdo->prepare('SELECT COUNT(*) FROM students WHERE id = :student_id');
                $stmtStudent->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $stmtStudent->execute();
                if ($stmtStudent->fetchColumn() === 0) {
                    $failedCount++;
                    $failedStudents[] = "Student ID {$studentId} not found.";
                    continue;
                }

                // Check for existing enrollment (unique_enrollment constraint handles this too, but for clearer error)
                $stmtCheckEnrollment = $pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE student_id = :student_id AND course_id = :course_id');
                $stmtCheckEnrollment->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $stmtCheckEnrollment->bindParam(':course_id', $courseId, PDO::PARAM_INT);
                $stmtCheckEnrollment->execute();

                if ($stmtCheckEnrollment->fetchColumn() > 0) {
                    // Update existing enrollment if status is not 'enrolled'
                    $stmtUpdateEnrollment = $pdo->prepare('UPDATE enrollments SET status = "enrolled", updated_at = NOW() WHERE student_id = :student_id AND course_id = :course_id');
                    $stmtUpdateEnrollment->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                    $stmtUpdateEnrollment->bindParam(':course_id', $courseId, PDO::PARAM_INT);
                    $stmtUpdateEnrollment->execute();
                    $successCount++;
                } else {
                    // Insert new enrollment
                    $stmtEnroll = $pdo->prepare('INSERT INTO enrollments (student_id, course_id, status) VALUES (:student_id, :course_id, "enrolled")');
                    $stmtEnroll->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                    $stmtEnroll->bindParam(':course_id', $courseId, PDO::PARAM_INT);
                    $stmtEnroll->execute();
                    $successCount++;
                }
            }

            $pdo->commit();

            $message = "Successfully enrolled {$successCount} students.";
            if ($failedCount > 0) {
                $message .= " Failed to enroll {$failedCount} students.";
            }

            $response->getBody()->write(json_encode(['message' => $message, 'status' => 'success', 'failed_students' => $failedStudents]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Database error enrolling students: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Application error enrolling students: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: PATCH /api/v1/admin/courses/{course_id}/unenroll-students
     * Description: Unenrolls multiple students from a specified course by setting their status to 'dropped'.
     * Request Body: JSON object with `student_ids` (array of int).
     * Returns:
     * - 200 OK: JSON message confirming successful unenrollments.
     * - 400 Bad Request: If `student_ids` is missing or not an array.
     * - 404 Not Found: If course or some students are not found/enrolled.
     * - 500 Internal Server Error: For database or unexpected errors.
     */
    $group->patch('/courses/{course_id}/unenroll-students', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $courseId = $args['course_id'];
        $data = $request->getParsedBody();
        $studentIds = $data['student_ids'] ?? [];

        if (!is_array($studentIds)) {
            $response->getBody()->write(json_encode(['message' => "Bad Request: 'student_ids' must be an array."]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        if (empty($studentIds)) {
            $response->getBody()->write(json_encode(['message' => "No students provided for unenrollment."]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $pdo->beginTransaction();

            // Check if course exists
            $stmtCourse = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id');
            $stmtCourse->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmtCourse->execute();
            if ($stmtCourse->fetchColumn() === 0) {
                $pdo->rollBack();
                $response->getBody()->write(json_encode(['message' => "Course ID {$courseId} not found."]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $successCount = 0;
            $failedCount = 0;
            $failedStudents = [];

            foreach ($studentIds as $studentId) {
                // Update enrollment status to 'dropped' for existing enrollments
                $stmtUnenroll = $pdo->prepare('
                    UPDATE enrollments
                    SET status = "dropped", updated_at = NOW()
                    WHERE student_id = :student_id AND course_id = :course_id AND status = "enrolled"
                ');
                $stmtUnenroll->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $stmtUnenroll->bindParam(':course_id', $courseId, PDO::PARAM_INT);
                $stmtUnenroll->execute();

                if ($stmtUnenroll->rowCount() > 0) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $failedStudents[] = "Student ID {$studentId} not found or not currently enrolled in course {$courseId}.";
                }
            }

            $pdo->commit();

            $message = "Successfully unenrolled {$successCount} students.";
            if ($failedCount > 0) {
                $message .= " Failed to unenroll {$failedCount} students.";
            }

            $response->getBody()->write(json_encode(['message' => $message, 'status' => 'success', 'failed_students' => $failedStudents]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Database error unenrolling students: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Application error unenrolling students: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });
};