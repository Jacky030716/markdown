<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {

    // Get student_id by user_id (important)
    $group->get('/user/{user_id}', function (Request $request, Response $response, array $args) {
        $userId = $args['user_id'];

        try {
            // Get database connection
            $pdo = $this->get('db');

            // Validate user_id is numeric
            if (!is_numeric($userId)) {
                $response->getBody()->write(json_encode([
                    'message' => 'Invalid user ID format',
                    'status' => 'error'
                ]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            // Prepare and execute query to get student_id from user_id
            $stmt = $pdo->prepare(
                'SELECT s.id as student_id, s.matric_no, s.name 
             FROM students AS s
             JOIN users AS u ON s.user_id = u.id
             WHERE s.user_id = :user_id AND u.is_active = 1'
            );

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$studentData) {
                $response->getBody()->write(json_encode([
                    'message' => "No student found for user ID: {$userId}",
                    'status' => 'error'
                ]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Success response
            $response->getBody()->write(json_encode([
                'data' => $studentData,
                'status' => 'success'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            error_log("Database error fetching student by user ID {$userId}: " . $e->getMessage());

            $response->getBody()->write(json_encode([
                'message' => 'Database error occurred',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("Application error fetching student by user ID {$userId}: " . $e->getMessage());

            $response->getBody()->write(json_encode([
                'message' => 'An unexpected error occurred',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    // For sidebar (get student profile)
    $group->get('/{student_id}/profile', function (Request $request, Response $response, array $args) {
        $studentId = $args['student_id'];

        try {
            // Get database connection
            $pdo = $this->get('db');

            // Validate student_id is numeric
            if (!is_numeric($studentId)) {
                $response->getBody()->write(json_encode([
                    'message' => 'Invalid student ID format',
                    'status' => 'error'
                ]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            // Prepare and execute query - Fixed column names to match database schema
            $stmt = $pdo->prepare(
                'SELECT s.user_id, s.id as student_db_id, s.matric_no, s.name, s.program, s.year_of_study, s.advisor_id, u.email, u.role, u.is_active
                 FROM students AS s
                 JOIN users AS u ON s.user_id = u.id
                 WHERE s.id = :student_id AND u.is_active = 1'
            );

            // Fix: Use proper parameter binding
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $response->getBody()->write(json_encode([
                    'message' => "Student with ID: {$studentId} not found or is inactive",
                    'status' => 'error'
                ]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Success response
            $response->getBody()->write(json_encode([
                'data' => $student,
                'status' => 'success'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // Log the actual error for debugging
            error_log("Database error fetching student profile {$studentId}: " . $e->getMessage());
            error_log("SQL Error Code: " . $e->getCode());

            $response->getBody()->write(json_encode([
                'message' => 'Database error occurred',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            // Log general errors
            error_log("Application error fetching student profile {$studentId}: " . $e->getMessage());

            $response->getBody()->write(json_encode([
                'message' => 'An unexpected error occurred',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });



    //1. For Dashboard Page
    /** a)
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


    /** b)
     * Route: GET /api/v1/students/{studentId}/info
     * Description: Retrieves detailed information about a student based on their student_id.
     * Parameters:
     * - studentId (int): The unique identifier of the student.
     * Returns:
     * - 200 OK: JSON object with student information.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/{studentId}/info', function (Request $request, Response $response, array $args) {
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

            // Get student info along with their program, year_of_study, and advisor details
            $stmt = $pdo->prepare('
            SELECT 
                s.id as student_id, 
                s.matric_no, 
                s.name, 
                s.program, 
                s.year_of_study, 
                a.name as advisor_name, 
                a.department as advisor_department 
            FROM students s
            LEFT JOIN advisors a ON s.advisor_id = a.id
            WHERE s.id = :student_id
        ');
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($studentInfo) {
                $response->getBody()->write(json_encode([
                    'data' => $studentInfo,
                    'status' => 'success',
                    'message' => 'Student information retrieved successfully'
                ]));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode([
                    'message' => 'Student not found',
                    'status' => 'error'
                ]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
        } catch (PDOException $e) {
            error_log("Database error fetching student info for student {$studentId}: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("General error fetching student info: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });





    // 2. For Course Mark Page
    /** a)
     * Route: GET /api/v1/students/courses
     * Description: Retrieves all active courses for a specific student.
     * Returns:
     * - 200 OK: JSON array of course objects if courses are found.
     * - 404 Not Found: JSON message if no active courses are found for the student.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/{studentId}/courses', function (Request $request, Response $response, array $args) {
        $studentId = intval($args['studentId']);

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

    /** b)
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

    /** c)
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


    /** d)
     * Route: GET /api/v1/students/{studentId}/courses/{courseId}/analytics
     * Description: Retrieves performance analytics including student marks and class averages
     * Parameters:
     * - studentId (int): The unique identifier of the student.
     * - courseId (int): The unique identifier of the course.
     * Returns:
     * - 200 OK: JSON object with student marks, class averages, and anonymous comparison data
     * - 404 Not Found: JSON message if no data found
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/{studentId}/courses/{courseId}/analytics', function (Request $request, Response $response, array $args) {
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

            // Get student's marks with component details
            $studentMarksStmt = $pdo->prepare('
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
            $studentMarksStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $studentMarksStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $studentMarksStmt->execute();
            $studentMarks = $studentMarksStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get class averages for each component
            $classAveragesStmt = $pdo->prepare('
            SELECT 
                mc.id as component_id,
                mc.name,
                mc.type,
                mc.max_mark,
                mc.weight,
                ROUND(AVG(sm.mark), 2) as average_mark,
                ROUND(AVG((sm.mark / mc.max_mark) * 100), 2) as average_percentage,
                COUNT(sm.mark) as student_count
            FROM mark_components mc
            LEFT JOIN student_marks sm ON mc.id = sm.component_id
            LEFT JOIN students s ON sm.student_id = s.id
            LEFT JOIN enrollments e ON s.id = e.student_id AND e.course_id = mc.course_id
            WHERE mc.course_id = :course_id AND e.status = "enrolled" AND sm.mark IS NOT NULL
            GROUP BY mc.id, mc.name, mc.type, mc.max_mark, mc.weight
            ORDER BY mc.type, mc.name
        ');
            $classAveragesStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $classAveragesStmt->execute();
            $classAverages = $classAveragesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get anonymous comparison data (all students' marks for the course)
            $comparisonStmt = $pdo->prepare('
            SELECT 
                s.id as student_id,
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
            FROM students s
            JOIN enrollments e ON s.id = e.student_id
            JOIN mark_components mc ON e.course_id = mc.course_id
            LEFT JOIN student_marks sm ON mc.id = sm.component_id AND sm.student_id = s.id
            WHERE mc.course_id = :course_id AND e.status = "enrolled"
            ORDER BY s.id, mc.type, mc.name
        ');
            $comparisonStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $comparisonStmt->execute();
            $comparisonData = $comparisonStmt->fetchAll(PDO::FETCH_ASSOC);

            // Process comparison data to group by student
            $studentComparison = [];
            $componentsList = [];

            foreach ($comparisonData as $row) {
                $sid = $row['student_id'];
                $componentId = $row['component_id'];

                if (!isset($studentComparison[$sid])) {
                    $studentComparison[$sid] = [
                        'student_id' => $sid,
                        'is_current_student' => ($sid == $studentId),
                        'components' => [],
                        'total_weighted_score' => 0,
                        'total_weight' => 0
                    ];
                }

                // Track unique components
                if (!isset($componentsList[$componentId])) {
                    $componentsList[$componentId] = [
                        'id' => $componentId,
                        'name' => $row['name'],
                        'type' => $row['type'],
                        'weight' => (float)$row['weight']
                    ];
                }

                $studentComparison[$sid]['components'][$componentId] = [
                    'name' => $row['name'],
                    'mark' => $row['mark'] !== null ? (float)$row['mark'] : null,
                    'percentage' => $row['percentage'] !== null ? (float)$row['percentage'] : null,
                    'weight' => (float)$row['weight']
                ];

                // Calculate weighted total if mark exists
                if ($row['mark'] !== null && $row['percentage'] !== null) {
                    $studentComparison[$sid]['total_weighted_score'] += (float)$row['percentage'] * (float)$row['weight'];
                    $studentComparison[$sid]['total_weight'] += (float)$row['weight'];
                }
            }

            // Calculate final totals for each student
            foreach ($studentComparison as &$student) {
                if ($student['total_weight'] > 0) {
                    $student['total_percentage'] = round($student['total_weighted_score'] / $student['total_weight'], 2);
                } else {
                    $student['total_percentage'] = null;
                }
            }

            // Convert numeric fields for student marks
            foreach ($studentMarks as &$mark) {
                $mark['component_id'] = (int)$mark['component_id'];
                $mark['max_mark'] = (float)$mark['max_mark'];
                $mark['weight'] = (float)$mark['weight'];
                $mark['mark'] = $mark['mark'] !== null ? (float)$mark['mark'] : null;
                $mark['percentage'] = $mark['percentage'] !== null ? (float)$mark['percentage'] : null;
            }

            // Convert numeric fields for class averages
            foreach ($classAverages as &$avg) {
                $avg['component_id'] = (int)$avg['component_id'];
                $avg['max_mark'] = (float)$avg['max_mark'];
                $avg['weight'] = (float)$avg['weight'];
                $avg['average_mark'] = $avg['average_mark'] !== null ? (float)$avg['average_mark'] : null;
                $avg['average_percentage'] = $avg['average_percentage'] !== null ? (float)$avg['average_percentage'] : null;
                $avg['student_count'] = (int)$avg['student_count'];
            }

            $response->getBody()->write(json_encode([
                'data' => [
                    'student_marks' => $studentMarks,
                    'class_averages' => $classAverages,
                    'anonymous_comparison' => array_values($studentComparison),
                    'components_list' => array_values($componentsList)
                ],
                'status' => 'success',
                'message' => 'Performance analytics retrieved successfully'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            error_log("Database error fetching analytics for student {$studentId}, course {$courseId}: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("General error fetching analytics: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });


    /** e)
     * Route: GET /api/v1/students/{studentId}/courses/{courseId}/ranking
     * Description: Retrieves the class ranking data for a specific student and course.
     * Parameters:
     * - studentId (int): The unique identifier of the student.
     * - courseId (int): The unique identifier of the course.
     * Returns:
     * - 200 OK: JSON object with ranking data including student's position and class distribution.
     * - 404 Not Found: JSON message if no ranking data found.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/{studentId}/courses/{courseId}/ranking', function (Request $request, Response $response, array $args) {
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

            // Get all students' total weighted scores for the course
            $stmt = $pdo->prepare('
            WITH student_scores AS (
                SELECT 
                    s.id as student_id,
                    s.name as student_name,
                    s.matric_no,
                    COALESCE(SUM(
                        CASE 
                            WHEN sm.mark IS NOT NULL THEN 
                                (sm.mark / mc.max_mark) * mc.weight
                            ELSE 0 
                        END
                    ), 0) as total_weighted_score
                FROM students s
                INNER JOIN enrollments e ON s.id = e.student_id
                LEFT JOIN mark_components mc ON e.course_id = mc.course_id
                LEFT JOIN student_marks sm ON s.id = sm.student_id AND mc.id = sm.component_id
                WHERE e.course_id = :course_id AND e.status = "enrolled"
                GROUP BY s.id, s.name, s.matric_no
            ),
            ranked_students AS (
                SELECT 
                    *,
                    ROW_NUMBER() OVER (ORDER BY total_weighted_score DESC) as rank_position,
                    COUNT(*) OVER () as total_students
                FROM student_scores
            )
            SELECT 
                student_id,
                student_name,
                matric_no,
                total_weighted_score,
                rank_position,
                total_students,
                CASE 
                    WHEN student_id = :student_id THEN 1 
                    ELSE 0 
                END as is_current_student
            FROM ranked_students
            ORDER BY rank_position
        ');

            $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rankings)) {
                $response->getBody()->write(json_encode([
                    'message' => 'No ranking data found for this course',
                    'data' => null,
                    'status' => 'success'
                ]));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }

            // Find current student's data
            $currentStudentData = null;
            foreach ($rankings as $ranking) {
                if ($ranking['is_current_student'] == 1) {
                    $currentStudentData = $ranking;
                    break;
                }
            }

            if (!$currentStudentData) {
                $response->getBody()->write(json_encode([
                    'message' => 'Current student not found in rankings',
                    'status' => 'error'
                ]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Calculate percentile and position data
            $totalStudents = intval($currentStudentData['total_students']);
            $currentPosition = intval($currentStudentData['rank_position']);
            $studentsAbove = $currentPosition - 1;
            $studentsBelow = $totalStudents - $currentPosition;

            // Calculate percentile (higher score = higher percentile)
            $percentile = $totalStudents > 1 ?
                round((($totalStudents - $currentPosition) / ($totalStudents - 1)) * 100) : 100;

            // Determine ordinal suffix for position
            $ordinalSuffix = 'th';
            if ($currentPosition % 100 < 11 || $currentPosition % 100 > 13) {
                switch ($currentPosition % 10) {
                    case 1:
                        $ordinalSuffix = 'st';
                        break;
                    case 2:
                        $ordinalSuffix = 'nd';
                        break;
                    case 3:
                        $ordinalSuffix = 'rd';
                        break;
                }
            }

            // Prepare response data
            $rankingData = [
                'current_student' => [
                    'position' => $currentPosition,
                    'position_text' => $currentPosition . $ordinalSuffix,
                    'total_score' => round(floatval($currentStudentData['total_weighted_score']), 2),
                    'percentile' => $percentile,
                    'percentile_text' => "Top " . (100 - $percentile) . "% ({$percentile}th Percentile)"
                ],
                'class_distribution' => [
                    'total_students' => $totalStudents,
                    'students_above' => $studentsAbove,
                    'students_below' => $studentsBelow,
                    'above_percentage' => $totalStudents > 0 ? round(($studentsAbove / $totalStudents) * 100, 1) : 0,
                    'current_percentage' => $totalStudents > 0 ? round((1 / $totalStudents) * 100, 1) : 100,
                    'below_percentage' => $totalStudents > 0 ? round(($studentsBelow / $totalStudents) * 100, 1) : 0
                ],
                'all_rankings' => array_map(function ($ranking) {
                    return [
                        'student_id' => intval($ranking['student_id']),
                        'student_name' => $ranking['student_name'],
                        'matric_no' => $ranking['matric_no'],
                        'total_score' => round(floatval($ranking['total_weighted_score']), 2),
                        'position' => intval($ranking['rank_position']),
                        'is_current_student' => intval($ranking['is_current_student']) === 1
                    ];
                }, $rankings)
            ];

            // Return the ranking data
            $response->getBody()->write(json_encode([
                'data' => $rankingData,
                'status' => 'success',
                'message' => 'Ranking data retrieved successfully'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            error_log("Database error fetching ranking for student {$studentId}, course {$courseId}: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("General error fetching ranking: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => 'Internal Server Error',
                'status' => 'error'
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });



    // 3. For Remark Request Page
    //a) post remark request 
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
            SELECT * 
            FROM student_marks AS sm
            JOIN mark_components AS mc ON sm.component_id = mc.id
            WHERE student_id = :student_id AND component_id = :component_id
        ');
            $markStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $markStmt->bindParam(':component_id', $componentId, PDO::PARAM_INT);
            $markStmt->execute();

            $markResult = $markStmt->fetch(PDO::FETCH_ASSOC);
            $currentMark = $markResult ? $markResult['mark'] : null;
            $componentName = $markResult ? $markResult['name'] : null;

            // Insert the remark request with the current mark
            $stmt = $pdo->prepare('
            INSERT INTO remark_requests (student_id, course_id, component, current_mark, justification, status, requested_at)
            VALUES (:student_id, :course_id, :component, :current_mark, :justification, "pending", NOW())
        ');

            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $stmt->bindParam(':component', $componentName, PDO::PARAM_STR);
            $stmt->bindParam(':current_mark', $currentMark, PDO::PARAM_STR);
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

    //b) get remark requests for a student
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

    // c) get all course of student with their the related component of each course
    $group->get('/{studentId}/courses-with-components', function (Request $request, Response $response, array $args) {
        // Mock Student ID - in production, get this from JWT token or session
        $studentId = (int)$args['studentId'];

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
