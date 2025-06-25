<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}


return function (RouteCollectorProxy $group) {
  /**
   * Route: GET /api/v1/advisors/{advisors_id}/advisees
   * Description: Retrieves all advisees assigned to a specific advisor.
   * Parameters:
   * - advisors_id (int): The unique identifier of the advisor.
   * Returns:
   * - 200 OK: JSON array of advisee objects if advisees are found.
   * - 404 Not Found: JSON message if no active advisees are found for the advisor.
   * - 500 Internal Server Error: JSON message for database or unexpected errors.
   */
  $group->get('/{advisors_id}/advisees', function (Request $request, Response $response, array $args) {
    $advisorsId = $args['advisors_id'];

    try {
      $pdo = $this->get('db');

      // First, get all students under this advisor
      $stmt = $pdo->prepare('SELECT * FROM students WHERE advisor_id = :advisor_id');
      $stmt->bindParam(':advisor_id', $advisorsId, PDO::PARAM_INT);
      $stmt->execute();
      $advisees = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Check if any advisees were found
      if (empty($advisees)) {
        $response->getBody()->write(json_encode(['message' => "No active advisees found for advisor ID: {$advisorsId}"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }

      $result = [];

      foreach ($advisees as $student) {
        $studentId = $student['id'];

        // Get all enrolled courses for this student with course details
        $coursesStmt = $pdo->prepare(
          'SELECT 
                    c.id AS course_id,
                    c.course_code,
                    c.course_name,
                    c.credit_hours,
                    e.status
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.student_id = :student_id AND e.status = "enrolled"'
        );
        $coursesStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $coursesStmt->execute();
        $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

        $coursesEnrolled = [];
        $totalCreditHours = 0;
        $totalWeightedGrade = 0.0;

        foreach ($courses as $course) {
          $courseId = $course['course_id'];
          $creditHours = (int)$course['credit_hours'];
          $totalCreditHours += $creditHours;

          // Get all components and marks for this course and student
          $marksStmt = $pdo->prepare(
            'SELECT 
                        mc.id AS component_id,
                        mc.name AS component_name,
                        mc.type AS component_type,
                        mc.max_mark,
                        mc.weight,
                        COALESCE(sm.mark, 0) AS student_mark
                    FROM mark_components mc
                    LEFT JOIN student_marks sm ON mc.id = sm.component_id AND sm.student_id = :student_id
                    WHERE mc.course_id = :course_id
                    ORDER BY mc.id ASC'
          );
          $marksStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
          $marksStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
          $marksStmt->execute();
          $marks = $marksStmt->fetchAll(PDO::FETCH_ASSOC);

          // Calculate total mark for this course
          $weightedTotal = 0;
          $totalWeight = 0;

          foreach ($marks as $mark) {
            $weight = (float)$mark['weight'];
            $maxMark = (float)$mark['max_mark'];
            $studentMark = $mark['student_mark'] !== null ? (float)$mark['student_mark'] : 0;

            if ($maxMark > 0) {
              $weightedTotal += ($studentMark / $maxMark) * $weight;
              $totalWeight += $weight;
            }
          }

          $totalMark = round($weightedTotal, 1);

          // Determine letter grade
          $letterGrade = '';
          if ($totalMark >= 90) $letterGrade = 'A+';
          else if ($totalMark >= 80) $letterGrade = 'A';
          else if ($totalMark >= 75) $letterGrade = 'A-';
          else if ($totalMark >= 70) $letterGrade = 'B+';
          else if ($totalMark >= 65) $letterGrade = 'B';
          else if ($totalMark >= 60) $letterGrade = 'B-';
          else if ($totalMark >= 55) $letterGrade = 'C+';
          else if ($totalMark >= 50) $letterGrade = 'C';
          else if ($totalMark >= 45) $letterGrade = 'C-';
          else if ($totalMark >= 40) $letterGrade = 'D+';
          else if ($totalMark >= 35) $letterGrade = 'D';
          else if ($totalMark >= 30) $letterGrade = 'D-';
          else $letterGrade = 'E';

          // Convert to numeric grade for GPA calculation
          $numericGrade = 0.00;
          if ($totalMark >= 90) $numericGrade = 4.00;
          else if ($totalMark >= 80) $numericGrade = 4.00;
          else if ($totalMark >= 75) $numericGrade = 3.67;
          else if ($totalMark >= 70) $numericGrade = 3.33;
          else if ($totalMark >= 65) $numericGrade = 3.00;
          else if ($totalMark >= 60) $numericGrade = 2.67;
          else if ($totalMark >= 55) $numericGrade = 2.33;
          else if ($totalMark >= 50) $numericGrade = 2.00;
          else if ($totalMark >= 45) $numericGrade = 1.67;
          else if ($totalMark >= 40) $numericGrade = 1.33;
          else if ($totalMark >= 35) $numericGrade = 1.00;
          else if ($totalMark >= 30) $numericGrade = 0.67;
          else $numericGrade = 0.00;

          // Add weighted grade points (numeric grade * credit hours)
          $totalWeightedGrade += $numericGrade * $creditHours;

          // Add course to enrolled courses array
          $coursesEnrolled[] = [
            'course_code' => $course['course_code'],
            'course_name' => $course['course_name'],
            'credits' => $creditHours,
            'totalMark' => $totalMark,
            'grade' => $letterGrade
          ];
        }

        // Calculate GPA
        $gpa = $totalCreditHours > 0 ? round($totalWeightedGrade / $totalCreditHours, 2) : 0.00;

        // Build student data with courses and GPA
        $studentData = [
          'id' => (int)$student['id'],
          'user_id' => (int)$student['user_id'],
          'matric_no' => $student['matric_no'],
          'name' => $student['name'],
          'program' => $student['program'],
          'year_of_study' => (int)$student['year_of_study'],
          'advisor_id' => (int)$student['advisor_id'],
          'created_at' => $student['created_at'],
          'updated_at' => $student['updated_at'],
          'courses_enrolled' => $coursesEnrolled,
          'gpa' => $gpa
        ];

        $result[] = $studentData;
      }

      $response->getBody()->write(json_encode(['data' => $result, 'status' => 'success']));
      return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
      error_log("Database error fetching advisees for advisor {$advisorsId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
      error_log("Application error fetching advisees for advisor {$advisorsId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
  });

  /**
   * Route: GET /api/v1/advisors/{advisors_id}/advisees/${advisee_id}
   * Description: Retrieves the marks components for a specific advisee assigned to an advisor.
   * Parameters:
   * - lecturer_id (int): The unique identifier of the lecturer.
   * - course_id (int): The unique identifier of the course.
   * Returns:
   * - 200 OK: JSON array of marks component objects if components are found.
   * - 404 Not Found: JSON message if no components are found for the course.
   * - 500 Internal Server Error: JSON message for database or unexpected errors.
   */
  $group->get('/course/{course_id}', function (Request $request, Response $response, array $args) {
    $courseId = $args['course_id'];

    try {
      $pdo = $this->get('db');

      // Fetch the course detail to ensure it exists
      $courseStmt = $pdo->prepare('SELECT * FROM courses WHERE id = :course_id AND is_active = 1');
      $courseStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $courseStmt->execute();

      $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

      if (!$course) {
        $response->getBody()->write(json_encode(['message' => "Course with ID: {$courseId} not found or is inactive"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }

      // Fetch the mark components for the course
      $stmt = $pdo->prepare('SELECT id, name, type, max_mark, weight FROM mark_components WHERE course_id = :course_id');
      $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $stmt->execute();

      $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (empty($components)) {
        $response->getBody()->write(json_encode(['message' => "No components found for course ID: {$courseId}"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }

      $course['components'] = $components;

      $response->getBody()->write(json_encode($course));
      return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
      error_log("Database error fetching components for course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Internal Server Error']));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
      error_log("Application error fetching components for course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred']));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
  });

  /**
   * Route: GET /api/v1/lecturers/{lecturer_id}/courses/{course_id}/students/marks
   * Description: Retrieves all students enrolled in a specific course along with their marks for each component.
   * Parameters:
   * - lecturer_id (int): The unique identifier of the lecturer.
   * - course_id (int): The unique identifier of the course.
   * Returns:
   * - 200 OK: JSON array of student objects with their marks for each component.
   * - 403 Forbidden: JSON message if the lecturer is not assigned to the course or the course is inactive.
   * - 404 Not Found: JSON message if no students or marks are found for the course.
   * * - 500 Internal Server Error: JSON message for database or unexpected errors.
   */
  $group->get('/{lecturer_id}/courses/{course_id}/students/marks', function (Request $request, Response $response, array $args) {
    $lecturerId = $args['lecturer_id'];
    $courseId = $args['course_id'];

    try {
      $pdo = $this->get('db');

      // 1. Verify that the lecturer is indeed assigned to this course.
      $verifyAssignmentStmt = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id AND lecturer_id = :lecturer_id AND is_active = 1');
      $verifyAssignmentStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $verifyAssignmentStmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
      $verifyAssignmentStmt->execute();
      $isAssigned = (bool) $verifyAssignmentStmt->fetchColumn();

      if (!$isAssigned) {
        $response->getBody()->write(json_encode(['message' => "Unauthorized or inactive course."]));
        return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
      }

      // 2. Get all components for this course first.
      // This is still useful to have a definitive list of components for structuring the output
      // and for the total mark calculation, even if the primary data comes from the JOIN.
      $componentsStmt = $pdo->prepare(
        'SELECT id AS component_id, name AS component_name, type AS component_type, max_mark, weight
            FROM mark_components
            WHERE course_id = :course_id
            ORDER BY id ASC'
      );
      $componentsStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $componentsStmt->execute();
      $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

      // Create a lookup map for components by ID for easy access during processing
      $componentMap = [];
      foreach ($components as $comp) {
        $componentMap[$comp['component_id']] = $comp;
      }

      // 3. Fetch all enrolled students for the course and their marks for *all* components.
      // We use LEFT JOIN for student_marks so that students who haven't received marks for all
      // components are still included, with a NULL mark where missing.
      // Assuming 'students' table has 'id', 'name', 'matric_no' (from your query)
      $studentsMarksStmt = $pdo->prepare(
        'SELECT
                s.id AS student_id,
                s.name AS student_name,
                s.matric_no,
                c.id AS component_id,
                c.name AS component_name,
                c.type AS component_type,
                c.max_mark, 
                c.weight,   
                sm.mark AS student_mark
            FROM
                enrollments AS e
            JOIN
                students AS s ON e.student_id = s.id
            JOIN
                mark_components AS c ON e.course_id = c.course_id
            LEFT JOIN
                student_marks AS sm ON s.id = sm.student_id AND sm.component_id = c.id
            WHERE
                e.course_id = :course_id AND e.status = "enrolled"
            ORDER BY
                s.name ASC, c.id ASC'
      );
      $studentsMarksStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $studentsMarksStmt->execute();
      $rawStudentMarks = $studentsMarksStmt->fetchAll(PDO::FETCH_ASSOC);

      // If no students are found (or no marks), return a 404.
      if (empty($rawStudentMarks)) {
        $response->getBody()->write(json_encode(['message' => "No enrolled students or marks found for Course ID: {$courseId}."]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }

      $studentsData = [];

      // Process the raw data to group marks by student.
      foreach ($rawStudentMarks as $row) {
        $studentId = $row['student_id'];

        // Initialize student entry if not already present
        if (!isset($studentsData[$studentId])) {
          $studentsData[$studentId] = [
            'id' => (int)$row['student_id'],
            'name' => $row['student_name'],
            'matricId' => $row['matric_no'],
            'marks' => [],
            'totalMark' => 0, // Will be calculated later
            'grade' => ''     // Will be calculated later
          ];

          // Initialize all components for the student with null marks
          // This ensures all components appear for every student, even if no mark exists
          foreach ($components as $comp) {
            $key = strtolower(str_replace([' ', '-'], '', $comp['component_name']));
            $studentsData[$studentId]['marks'][$key] = [
              'component_id' => (int)($comp['component_id'] ?? 0),
              'component_name' => $comp['component_name'] ?? 'N/A',
              'component_type' => $comp['component_type'] ?? 'N/A',
              'max_mark' => (float)($comp['max_mark'] ?? 0.0), // Include max_mark
              'weight' => (float)($comp['weight'] ?? 0.0),     // Include weight
              'student_mark' => null // Default to null
            ];
          }
        }

        // Update the actual student mark for the specific component if it exists
        $componentKey = strtolower(str_replace([' ', '-'], '', $row['component_name']));
        if (isset($studentsData[$studentId]['marks'][$componentKey])) {
          $studentsData[$studentId]['marks'][$componentKey]['student_mark'] = $row['student_mark'] !== null ? (float)$row['student_mark'] : null;
        }
      }

      // 4. Calculate totalMark and grade for each student.
      foreach ($studentsData as &$student) {
        $weightedTotal = 0;
        $totalWeight = 0;

        foreach ($student['marks'] as $markEntry) { // Iterate through the student's actual mark entries
          $weight = (float)($markEntry['weight'] ?? 0.0);
          $max = (float)($markEntry['max_mark'] ?? 0.0);
          $mark = $markEntry['student_mark'];

          if ($mark !== null && $max > 0) {
            $weightedTotal += ($mark / $max) * $weight;
            $totalWeight += $weight; // Accumulate total weight only for components with marks
          }
        }
        // If no components or no marks, total mark remains 0
        $student['totalMark'] = round($weightedTotal, 1);

        // Simple grade logic (unchanged)
        if ($student['totalMark'] >= 90) $student['grade'] = 'A+';
        else if ($student['totalMark'] >= 80) $student['grade'] = 'A';
        else if ($student['totalMark'] >= 75) $student['grade'] = 'A-';
        else if ($student['totalMark'] >= 70) $student['grade'] = 'B+';
        else if ($student['totalMark'] >= 65) $student['grade'] = 'B';
        else if ($student['totalMark'] >= 60) $student['grade'] = 'B-';
        else if ($student['totalMark'] >= 55) $student['grade'] = 'C+';
        else if ($student['totalMark'] >= 50) $student['grade'] = 'C';
        else if ($student['totalMark'] >= 45) $student['grade'] = 'C-';
        else if ($student['totalMark'] >= 40) $student['grade'] = 'D+';
        else if ($student['totalMark'] >= 35) $student['grade'] = 'D';
        else if ($student['totalMark'] >= 30) $student['grade'] = 'D-';
        else $student['grade'] = 'E';
      }

      $finalResult = array_values($studentsData); // reset keys for JSON array

      // Return success response with data
      $response->getBody()->write(json_encode(['data' => $finalResult, 'status' => 'success']));
      return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
      error_log("DB Error fetching student marks for lecturer {$lecturerId} in course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Database error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
      error_log("General Error fetching student marks for lecturer {$lecturerId} in course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Unexpected error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
  });

  /**
   * Route: GET /api/v1/lecturers/{lecturer_id}/courses/{course_id}/students/analysis
   * Description: Retrieves all students enrolled in a specific course taught by a given lecturer,
   * along with their marks for each component in that course, and their overall CGPA.
   * Parameters:
   * - lecturer_id (int): The ID of the lecturer.
   * - course_id (int): The ID of the course.
   * Returns:
   * - 200 OK: JSON array of student objects, each containing their details, course marks, and CGPA.
   * - 403 Forbidden: JSON message if the lecturer is not assigned to the course.
   * - 404 Not Found: JSON message if the lecturer, course, or students/marks are not found.
   * - 500 Internal Server Error: JSON message for database or unexpected errors.
   */
  $group->get('/{lecturer_id}/courses/{course_id}/students/analysis', function (Request $request, Response $response, array $args) {
    $lecturerId = $args['lecturer_id'];
    $courseId = $args['course_id'];

    try {
      $pdo = $this->get('db');

      // 1. Verify that the lecturer is indeed assigned to this course.
      $verifyLecturerCourseStmt = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id AND lecturer_id = :lecturer_id AND is_active = 1');
      $verifyLecturerCourseStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $verifyLecturerCourseStmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
      $verifyLecturerCourseStmt->execute();
      if (!$verifyLecturerCourseStmt->fetchColumn()) {
        $response->getBody()->write(json_encode(['message' => "Forbidden: Lecturer not assigned to this course, or course is inactive."]));
        return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
      }

      // 2. Get all components for this course first.
      $componentsStmt = $pdo->prepare(
        'SELECT id AS component_id, name AS component_name, type AS component_type, max_mark, weight
              FROM mark_components
              WHERE course_id = :course_id
              ORDER BY id ASC'
      );
      $componentsStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $componentsStmt->execute();
      $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

      // Create a map for quick lookup of component details
      $componentMap = [];
      foreach ($components as $comp) {
        $componentMap[$comp['component_id']] = $comp;
      }

      // 3. Fetch all enrolled students for the course and their marks for *all* components.
      // We use LEFT JOIN for student_marks so that students who haven't received marks for all
      // components are still included, with a NULL mark where missing.
      // Assuming 'students' table has 'id', 'name', 'matric_no'
      $studentsMarksStmt = $pdo->prepare(
        'SELECT
                  s.id AS student_id,
                  s.name AS student_name,
                  s.matric_no,
                  c.id AS component_id,
                  c.name AS component_name,
                  c.type AS component_type,
                  c.max_mark,
                  c.weight,
                  sm.mark AS student_mark
              FROM
                  enrollments AS e
              JOIN
                  students AS s ON e.student_id = s.id
              JOIN
                  mark_components AS c ON e.course_id = c.course_id
              LEFT JOIN
                  student_marks AS sm ON sm.student_id = s.id AND sm.component_id = c.id
              WHERE
                  e.course_id = :course_id AND e.status = "enrolled"
              ORDER BY
                  s.name ASC, c.id ASC'
      );
      $studentsMarksStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $studentsMarksStmt->execute();
      $rawStudentMarks = $studentsMarksStmt->fetchAll(PDO::FETCH_ASSOC);

      if (empty($rawStudentMarks)) {
        $response->getBody()->write(json_encode(['data' => [], 'message' => "No enrolled students or marks found for Course ID: {$courseId}."]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json'); // Return 200 with empty data
      }

      $studentsData = [];

      // Temporary map to build student data with marks for current course
      $tempStudentMarksData = [];

      foreach ($rawStudentMarks as $row) {
        $studentId = $row['student_id'];

        if (!isset($tempStudentMarksData[$studentId])) {
          $tempStudentMarksData[$studentId] = [
            'id' => (int)$row['student_id'],
            'name' => $row['student_name'],
            'matricId' => $row['matric_no'],
            'marks' => [], // Initialize marks for current course components
            'totalMark' => 0,
            'grade' => '',
            // Removed 'cgpa' and 'cgpaCoursesCount' initialization
            'allMarksGiven' => true // Tentatively true, will be set to false if any mark is missing
          ];
        }

        $componentKey = strtolower(str_replace([' ', '-'], '', $row['component_name']));
        $tempStudentMarksData[$studentId]['marks'][$componentKey] = [
          'component_id' => (int)($row['component_id'] ?? 0),
          'component_name' => $row['component_name'] ?? 'N/A',
          'component_type' => $row['component_type'] ?? 'N/A',
          'max_mark' => (float)($row['max_mark'] ?? 0.0),
          'weight' => (float)($row['weight'] ?? 0.0),
          'student_mark' => $row['student_mark'] !== null ? (float)$row['student_mark'] : null
        ];
      }

      // Now, populate $studentsData with complete structure and correct 'allMarksGiven' flag
      foreach ($tempStudentMarksData as $studentId => $student) {
        $studentsData[$studentId] = $student;
        $allMarksGivenForCurrentCourse = true;

        // Ensure all components for the current course are present in student's marks
        foreach ($components as $comp) {
          $key = strtolower(str_replace([' ', '-'], '', $comp['component_name']));
          // If component slot is missing OR if the student_mark for it is null
          if (!isset($studentsData[$studentId]['marks'][$key]) || $studentsData[$studentId]['marks'][$key]['student_mark'] === null) {
            $allMarksGivenForCurrentCourse = false;
            // Also, ensure the missing component slot is initialized
            $studentsData[$studentId]['marks'][$key] = $studentsData[$studentId]['marks'][$key] ?? [
              'component_id' => (int)($comp['component_id'] ?? 0),
              'component_name' => $comp['component_name'] ?? 'N/A',
              'component_type' => $comp['component_type'] ?? 'N/A',
              'max_mark' => (float)($comp['max_mark'] ?? 0.0),
              'weight' => (float)($comp['weight'] ?? 0.0),
              'student_mark' => null
            ];
          }
        }
        $studentsData[$studentId]['allMarksGiven'] = $allMarksGivenForCurrentCourse;
      }

      // Grade to GPA point mapping and CGPA calculation logic removed from here
      // --- Current Course Total Mark Calculation ---
      foreach ($studentsData as &$student) {
        $currentCourseWeightedTotal = 0;
        foreach ($student['marks'] as $markEntry) {
          $weight = (float)($markEntry['weight'] ?? 0.0);
          $max = (float)($markEntry['max_mark'] ?? 0.0);
          $mark = $markEntry['student_mark'];

          // Only include marks that are not null and are valid numbers
          if ($mark !== null && !is_nan($mark) && $max > 0) {
            $currentCourseWeightedTotal += ($mark / $max) * $weight;
          }
        }
        $student['totalMark'] = round($currentCourseWeightedTotal, 1);
        $student['grade'] = calculateGrade($student['totalMark']);
      }

      $finalResult = array_values($studentsData);

      // Return success response with data
      $response->getBody()->write(json_encode(['data' => $finalResult, 'status' => 'success']));
      return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
      error_log("DB Error fetching student marks for lecturer {$lecturerId} in course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Database error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
      error_log("General Error fetching student marks for lecturer {$lecturerId} in course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
  });

  /**
   * Route: PATCH /api/v1/lecturers/{lecturer_id}/courses/{course_id}/students/{student_id}/marks/{component_id}
   * Description: Updates a specific student's mark for a given assessment component in a course.
   * Parameters:
   * - lecturer_id (int): The ID of the lecturer.
   * - course_id (int): The ID of the course.
   * - student_id (int): The ID of the student whose mark is being updated.
   * - component_id (int): The ID of the assessment component.
   * Request Body:
   * - JSON: {"mark": float|null} - The new mark value (can be null if mark is cleared).
   * Returns:
   * - 200 OK: JSON message confirming successful update.
   * - 400 Bad Request: JSON message if 'mark' is missing or invalid in the request body.
   * - 403 Forbidden: JSON message if the lecturer is not assigned to the course, or student is not enrolled.
   * - 404 Not Found: JSON message if the student, course, or component is not found.
   * - 500 Internal Server Error: JSON message for database or unexpected errors.
   */
  $group->patch('/{lecturer_id}/courses/{course_id}/students/{student_id}/marks/{component_id}', function (Request $request, Response $response, array $args) {
    $lecturerId = $args['lecturer_id'];
    $courseId = $args['course_id'];
    $studentId = $args['student_id'];
    $componentId = $args['component_id'];

    $parsedBody = $request->getParsedBody();
    $newMark = $parsedBody['mark'] ?? null;

    try {
      $pdo = $this->get('db');

      // --- Validation and Authorization ---

      // 1. Verify that the lecturer is indeed assigned to this course.
      $verifyLecturerCourseStmt = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id AND lecturer_id = :lecturer_id AND is_active = 1');
      $verifyLecturerCourseStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $verifyLecturerCourseStmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
      $verifyLecturerCourseStmt->execute();
      if (!$verifyLecturerCourseStmt->fetchColumn()) {
        $response->getBody()->write(json_encode(['message' => "Forbidden: Lecturer not assigned to this course, or course is inactive."]));
        return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
      }

      // 2. Verify that the student is enrolled in this course.
      $verifyStudentEnrollmentStmt = $pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE student_id = :student_id AND course_id = :course_id AND status = "enrolled"');
      $verifyStudentEnrollmentStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
      $verifyStudentEnrollmentStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $verifyStudentEnrollmentStmt->execute();
      if (!$verifyStudentEnrollmentStmt->fetchColumn()) {
        $response->getBody()->write(json_encode(['message' => "Not Found: Student ID {$studentId} is not enrolled in Course ID {$courseId}."]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }

      // 3. Verify that the component belongs to this course and get its max_mark.
      $getComponentInfoStmt = $pdo->prepare('SELECT max_mark FROM mark_components WHERE id = :component_id AND course_id = :course_id');
      $getComponentInfoStmt->bindParam(':component_id', $componentId, PDO::PARAM_INT);
      $getComponentInfoStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $getComponentInfoStmt->execute();
      $componentInfo = $getComponentInfoStmt->fetch(PDO::FETCH_ASSOC);

      if (!$componentInfo) {
        $response->getBody()->write(json_encode(['message' => "Not Found: Component ID {$componentId} not found or does not belong to Course ID {$courseId}."]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }
      $maxMark = (float)$componentInfo['max_mark'];

      // 4. Validate the new mark value.
      // Allow null mark (if user clears the input)
      if ($newMark !== null && $newMark !== '') {
        $newMarkFloat = filter_var($newMark, FILTER_VALIDATE_FLOAT);
        if ($newMarkFloat === false || $newMarkFloat < 0 || $newMarkFloat > $maxMark) {
          $response->getBody()->write(json_encode(['message' => "Bad Request: Mark must be a number between 0 and {$maxMark}."]));
          return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $newMark = $newMarkFloat; // Use the filtered float value
      } else {
        $newMark = null; // Ensure null if input is empty or just whitespace
      }

      // --- Database Update ---

      // Check if a mark already exists for this student and component
      $checkMarkStmt = $pdo->prepare('SELECT COUNT(*) FROM student_marks WHERE student_id = :student_id AND component_id = :component_id');
      $checkMarkStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
      $checkMarkStmt->bindParam(':component_id', $componentId, PDO::PARAM_INT);
      $checkMarkStmt->execute();
      $markExists = (bool) $checkMarkStmt->fetchColumn();

      if ($markExists) {
        // If mark exists, update it
        $updateStmt = $pdo->prepare('UPDATE student_marks SET mark = :mark, updated_at = NOW() WHERE student_id = :student_id AND component_id = :component_id');
      } else {
        // If mark does not exist, insert it
        $updateStmt = $pdo->prepare('INSERT INTO student_marks (student_id, component_id, mark, created_at, updated_at) VALUES (:student_id, :component_id, :mark, NOW(), NOW())');
      }

      $updateStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
      $updateStmt->bindParam(':component_id', $componentId, PDO::PARAM_INT);
      // Handle binding null if newMark is null
      if ($newMark === null) {
        $updateStmt->bindValue(':mark', null, PDO::PARAM_NULL);
      } else {
        $updateStmt->bindParam(':mark', $newMark, PDO::PARAM_STR); // Use STR for flexibility with float
      }

      $updateStmt->execute();

      // --- Success Response ---
      $response->getBody()->write(json_encode([
        'message' => 'Mark updated successfully.',
        'student_id' => (int)$studentId,
        'component_id' => (int)$componentId,
        'new_mark' => $newMark
      ]));
      return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
      error_log("Database error updating mark: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Database error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
      error_log("General error updating mark: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Unexpected error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
  });


  // function calculateGrade($totalMark) {
  //     if ($totalMark >= 90) return 'A+';
  //     if ($totalMark >= 80) return 'A';
  //     if ($totalMark >= 75) return 'A-';
  //     if ($totalMark >= 70) return 'B+';
  //     if ($totalMark >= 65) return 'B';
  //     if ($totalMark >= 60) return 'B-';
  //     if ($totalMark >= 55) return 'C+';
  //     if ($totalMark >= 50) return 'C';
  //     if ($totalMark >= 45) return 'C-';
  //     if ($totalMark >= 40) return 'D+';
  //     if ($totalMark >= 35) return 'D';
  //     if ($totalMark >= 30) return 'D-';
  //     return 'E';
  // }
};
