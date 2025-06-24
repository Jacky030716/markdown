<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

/**
 * Grade calculation function
 * Calculates letter grade based on numeric mark
 */
function calculateGrades($mark)
{
    if ($mark >= 90) return 'A+';
    if ($mark >= 80) return 'A';
    if ($mark >= 75) return 'A-';
    if ($mark >= 70) return 'B+';
    if ($mark >= 65) return 'B';
    if ($mark >= 60) return 'B-';
    if ($mark >= 55) return 'C+';
    if ($mark >= 50) return 'C';
    if ($mark >= 45) return 'C-';
    if ($mark >= 40) return 'D+';
    if ($mark >= 35) return 'D';
    if ($mark >= 30) return 'D-';
    return 'E';
}

return function (RouteCollectorProxy $group) {

    /**
     * Route: GET /api/v1/students/courses
     * Description: Retrieves all active courses for a specific student.
     * Returns:
     * - 200 OK: JSON array of course objects if courses are found.
     * - 404 Not Found: JSON message if no active courses are found for the student.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/courses', function (Request $request, Response $response, array $args) {
        // Mock Student ID - in production, get this from JWT token or session
        $studentId = 4; // Changed to 4 as requested

        try {
            $pdo = $this->get('db');
            $stmt = $pdo->prepare('
                SELECT c.id, c.course_code, c.course_name, c.semester, c.academic_year, c.credit_hours 
                FROM courses c
                JOIN enrollments e ON c.id = e.course_id
                WHERE e.student_id = :student_id AND c.is_active = 1 AND e.status = "enrolled"
                ORDER BY c.academic_year DESC, c.semester, c.course_code
            ');
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert IDs to integers for consistency
            foreach ($courses as &$course) {
                $course['id'] = (int)$course['id'];
                $course['credit_hours'] = (int)$course['credit_hours'];
            }

            // Check if any courses were found
            if (empty($courses)) {
                $response->getBody()->write(json_encode([
                    'message' => "No active courses found for this student",
                    'data' => [],
                    'status' => 'success'
                ]));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }

            // Return the courses data
            $response->getBody()->write(json_encode([
                'data' => $courses,
                'status' => 'success',
                'message' => 'Courses retrieved successfully'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            error_log("Database error fetching courses for student {$studentId}: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("General error fetching courses: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: GET /api/v1/students/{studentId}/courses/{courseId}/marks
     * Description: Retrieves the marks for a specific student and course.
     * Parameters:
     * - studentId (int): The unique identifier of the student.
     * - courseId (int): The unique identifier of the course.
     * Returns:
     * - 200 OK: JSON array of mark components for the student in the selected course.
     * - 404 Not Found: JSON message if no marks are found for the student in the selected course.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/{studentId}/courses/{courseId}/marks', function (Request $request, Response $response, array $args) {
        $studentId = intval($args['studentId']);
        $courseId = intval($args['courseId']);

        // Basic validation
        if ($studentId <= 0 || $courseId <= 0) {
            $response->getBody()->write(json_encode([
                'message' => 'Invalid student ID or course ID',
                'status' => 'error'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $pdo = $this->get('db');

            // First check if the student is enrolled in the course
            $enrollmentStmt = $pdo->prepare('
                SELECT COUNT(*) as count 
                FROM enrollments 
                WHERE student_id = :student_id AND course_id = :course_id AND status = "enrolled"
            ');
            $enrollmentStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $enrollmentStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $enrollmentStmt->execute();

            $enrollmentResult = $enrollmentStmt->fetch(PDO::FETCH_ASSOC);
            if ($enrollmentResult['count'] == 0) {
                $response->getBody()->write(json_encode([
                    'message' => 'Student is not enrolled in this course',
                    'status' => 'error'
                ]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Fetch marks data with component details
            $stmt = $pdo->prepare('
                SELECT 
                    mc.id as component_id,
                    mc.name,
                    mc.type,
                    mc.max_mark,
                    mc.weight,
                    COALESCE(sm.mark, NULL) as mark,
                    CASE 
                        WHEN sm.mark IS NOT NULL THEN ROUND((sm.mark / mc.max_mark) * 100, 2)
                        ELSE NULL 
                    END as percentage
                FROM mark_components mc
                LEFT JOIN student_marks sm ON mc.id = sm.component_id AND sm.student_id = :student_id
                WHERE mc.course_id = :course_id
                ORDER BY mc.type, mc.name
            ');
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmt->execute();

            $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert numeric fields to proper types
            foreach ($marks as &$mark) {
                $mark['component_id'] = (int)$mark['component_id'];
                $mark['max_mark'] = (float)$mark['max_mark'];
                $mark['weight'] = (float)$mark['weight'];
                $mark['mark'] = $mark['mark'] !== null ? (float)$mark['mark'] : null;
                $mark['percentage'] = $mark['percentage'] !== null ? (float)$mark['percentage'] : null;
            }

            // Check if any marks were found
            if (empty($marks)) {
                $response->getBody()->write(json_encode([
                    'message' => "No mark components found for this course",
                    'data' => [],
                    'status' => 'success'
                ]));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }

            // Return the marks data
            $response->getBody()->write(json_encode([
                'data' => $marks,
                'status' => 'success',
                'message' => 'Marks retrieved successfully'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            error_log("Database error fetching marks for student {$studentId}, course {$courseId}: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("General error fetching marks: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: GET /api/v1/students/{studentId}/courses/{courseId}/details
     * Description: Retrieves detailed information about a course for a specific student.
     * Parameters:
     * - studentId (int): The unique identifier of the student.
     * - courseId (int): The unique identifier of the course.
     * Returns:
     * - 200 OK: JSON object with course details, lecturer info, and enrollment status.
     * - 404 Not Found: JSON message if course not found or student not enrolled.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/{studentId}/courses/{courseId}/details', function (Request $request, Response $response, array $args) {
        $studentId = intval($args['studentId']);
        $courseId = intval($args['courseId']);

        // Basic validation
        if ($studentId <= 0 || $courseId <= 0) {
            $response->getBody()->write(json_encode([
                'message' => 'Invalid student ID or course ID',
                'status' => 'error'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $pdo = $this->get('db');

            // Fetch course details with lecturer information
            $stmt = $pdo->prepare('
                SELECT 
                    c.id,
                    c.course_code,
                    c.course_name,
                    c.credit_hours,
                    c.semester,
                    c.academic_year,
                    l.name as lecturer_name,
                    l.lecturer_id,
                    l.department,
                    e.enrollment_date,
                    e.status as enrollment_status
                FROM courses c
                LEFT JOIN lecturers l ON c.lecturer_id = l.id
                LEFT JOIN enrollments e ON c.id = e.course_id AND e.student_id = :student_id
                WHERE c.id = :course_id AND c.is_active = 1
            ');
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmt->execute();

            $courseDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$courseDetails) {
                $response->getBody()->write(json_encode([
                    'message' => 'Course not found or inactive',
                    'status' => 'error'
                ]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            if (!$courseDetails['enrollment_status']) {
                $response->getBody()->write(json_encode([
                    'message' => 'Student is not enrolled in this course',
                    'status' => 'error'
                ]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Convert numeric fields
            $courseDetails['id'] = (int)$courseDetails['id'];
            $courseDetails['credit_hours'] = (int)$courseDetails['credit_hours'];

            $response->getBody()->write(json_encode([
                'data' => $courseDetails,
                'status' => 'success',
                'message' => 'Course details retrieved successfully'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            error_log("Database error fetching course details for student {$studentId}, course {$courseId}: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("General error fetching course details: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: GET /api/v1/students/{studentId}/progress-summary
     * Description: Retrieves a summary of student's overall academic progress.
     * Parameters:
     * - studentId (int): The unique identifier of the student.
     * Returns:
     * - 200 OK: JSON object with overall progress statistics.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/{studentId}/progress-summary', function (Request $request, Response $response, array $args) {
        $studentId = intval($args['studentId']);

        if ($studentId <= 0) {
            $response->getBody()->write(json_encode([
                'message' => 'Invalid student ID',
                'status' => 'error'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $pdo = $this->get('db');

            // Get overall progress summary
            $stmt = $pdo->prepare('
                SELECT 
                    COUNT(DISTINCT c.id) as total_courses,
                    COUNT(DISTINCT CASE WHEN e.status = "enrolled" THEN c.id END) as enrolled_courses,
                    COUNT(DISTINCT CASE WHEN e.status = "completed" THEN c.id END) as completed_courses,
                    SUM(CASE WHEN e.status = "enrolled" THEN c.credit_hours ELSE 0 END) as current_credit_hours,
                    SUM(CASE WHEN e.status = "completed" THEN c.credit_hours ELSE 0 END) as completed_credit_hours
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.student_id = :student_id AND c.is_active = 1
            ');
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $progressSummary = $stmt->fetch(PDO::FETCH_ASSOC);

            // Convert to integers
            foreach ($progressSummary as $key => $value) {
                $progressSummary[$key] = (int)$value;
            }

            $response->getBody()->write(json_encode([
                'data' => $progressSummary,
                'status' => 'success',
                'message' => 'Progress summary retrieved successfully'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            error_log("Database error fetching progress summary for student {$studentId}: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("General error fetching progress summary: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });


    // 3. For Remark Request Page
    //post remark request 
    $group->post('/{studentId}/courses/{courseId}/remark-requests', function (Request $request, Response $response, array $args) {
        $studentId = (int)$args['studentId'];
        $courseId = (int)$args['courseId'];

        $data = json_decode($request->getBody(), true);
        $justification = $data['justification'];
        $componentId = $data['componentId'];

        try {
            $pdo = $this->get('db');

            // First, fetch the current mark for this student and component
            $markStmt = $pdo->prepare('
            SELECT mark 
            FROM student_marks 
            WHERE student_id = :student_id AND component_id = :component_id
        ');
            $markStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $markStmt->bindParam(':component_id', $componentId, PDO::PARAM_INT);
            $markStmt->execute();

            $markResult = $markStmt->fetch(PDO::FETCH_ASSOC);
            $currentMark = $markResult ? $markResult['mark'] : null;

            // Insert the remark request with the current mark
            $stmt = $pdo->prepare('
            INSERT INTO remark_requests (student_id, course_id, component, current_mark, justification, status, requested_at)
            VALUES (:student_id, :course_id, :component, :current_mark, :justification, "pending", NOW())
        ');

            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmt->bindParam(':component', $componentId, PDO::PARAM_INT);
            $stmt->bindParam(':current_mark', $currentMark, PDO::PARAM_STR); // Use PDO::PARAM_STR for decimal values
            $stmt->bindParam(':justification', $justification, PDO::PARAM_STR);
            $stmt->execute();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Remark request submitted successfully',
                'current_mark' => $currentMark
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Database error',
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });
    //get remark requests for a student
    $group->get('/{studentId}/remark-requests', function (Request $request, Response $response, array $args) {
        $studentId = (int)$args['studentId'];

        try {
            $pdo = $this->get('db');
            $stmt = $pdo->prepare('
            SELECT 
                rr.id,
                rr.student_id,
                rr.course_id,
                rr.component,
                rr.current_mark,
                rr.justification,
                rr.status,
                rr.lecturer_response,
                rr.requested_at,
                rr.responded_at,
                c.course_name,
                c.course_code,
                mc.name as component_name,
                mc.type as component_type,
                mc.max_mark
            FROM remark_requests rr
            JOIN courses c ON rr.course_id = c.id
            JOIN mark_components mc ON rr.component = mc.id
            WHERE rr.student_id = :student_id
            ORDER BY rr.requested_at DESC
        ');

            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format the data for frontend
            $formattedRequests = array_map(function ($request) {
                return [
                    'id' => $request['id'],
                    'dateSubmitted' => date('Y-m-d H:i', strtotime($request['requested_at'])),
                    'course' => $request['course_code'] . ' - ' . $request['course_name'],
                    'component' => $request['component_name'] . ' (' . ucfirst($request['component_type']) . ')',
                    'currentMark' => $request['current_mark'] ? $request['current_mark'] . '/' . $request['max_mark'] : 'N/A',
                    'status' => $request['status'],
                    'responseDate' => $request['responded_at'] ? date('Y-m-d H:i', strtotime($request['responded_at'])) : null,
                    'justification' => $request['justification'],
                    'lecturerResponse' => $request['lecturer_response'],
                    'courseId' => $request['course_id'],
                    'componentId' => $request['component']
                ];
            }, $requests);

            $response->getBody()->write(json_encode(['status' => 'success', 'data' => $formattedRequests]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Database error', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    //get all course of student with their the related component of each course
    $group->get('/courses-with-components', function (Request $request, Response $response, array $args) {
        // Mock Student ID - in production, get this from JWT token or session
        $studentId = 4; // Keep consistent with your system

        try {
            $pdo = $this->get('db');

            // First get the courses
            $stmt = $pdo->prepare('
                SELECT c.id, c.course_code, c.course_name, c.semester, c.academic_year, c.credit_hours 
                FROM courses c
                JOIN enrollments e ON c.id = e.course_id
                WHERE e.student_id = :student_id AND c.is_active = 1 AND e.status = "enrolled"
                ORDER BY c.academic_year DESC, c.semester, c.course_code
            ');
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert IDs to integers and add components for each course
            foreach ($courses as &$course) {
                $course['id'] = (int)$course['id'];
                $course['credit_hours'] = (int)$course['credit_hours'];
                $course['name'] = $course['course_code'] . ' - ' . $course['course_name']; // Add display name

                // Get components for this course
                $componentStmt = $pdo->prepare('
                    SELECT id, name, type, max_mark, weight 
                    FROM mark_components 
                    WHERE course_id = :course_id 
                    ORDER BY name
                ');
                $componentStmt->bindParam(':course_id', $course['id'], PDO::PARAM_INT);
                $componentStmt->execute();

                $components = $componentStmt->fetchAll(PDO::FETCH_ASSOC);

                // Convert component IDs to integers and format names
                foreach ($components as &$component) {
                    $component['id'] = (int)$component['id'];
                    $component['max_mark'] = (float)$component['max_mark'];
                    $component['weight'] = (float)$component['weight'];
                    // Format component name for better display
                    $component['name'] = $component['name'] . ' (' . ucfirst($component['type']) . ')';
                }

                $course['components'] = $components;
            }

            // Check if any courses were found
            if (empty($courses)) {
                $response->getBody()->write(json_encode([
                    'message' => "No active courses found for this student",
                    'data' => [],
                    'status' => 'success'
                ]));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }

            // Return the courses data
            $response->getBody()->write(json_encode([
                'data' => $courses,
                'status' => 'success',
                'message' => 'Courses with components retrieved successfully'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            error_log("Database error fetching courses with components for student {$studentId}: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("General error fetching courses with components: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });
};
