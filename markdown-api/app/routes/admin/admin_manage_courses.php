<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {
  /**
     * Route: POST /api/v1/admin/courses/create
     * Description: Creates a new course.
     * Request Body: JSON object with course details (course_code, course_name, credit_hours, lecturer_id, semester, academic_year, is_active).
     * Returns:
     * - 201 Created: JSON object of the newly created course.
     * - 400 Bad Request: If required fields are missing or validation fails.
     * - 409 Conflict: If course_code already exists.
     * - 500 Internal Server Error: For database or unexpected errors.
     */
    $group->post('/courses/create', function (Request $request, Response $response, array $args) {
      $pdo = $this->get('db');
      $data = $request->getParsedBody();

      $requiredFields = ['course_code', 'course_name', 'credit_hours'];
      foreach ($requiredFields as $field) {
          if (empty($data[$field])) {
              $response->getBody()->write(json_encode(['message' => "Bad Request: '{$field}' is required."]));
              return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
          }
      }

      $courseCode = $data['course_code'];
      $courseName = $data['course_name'];
      $creditHours = (int)$data['credit_hours'];
      $lecturerId = $data['lecturer_id'] ?? null;
      $semester = $data['semester'] ?? null;
      $academicYear = $data['academic_year'] ?? null;
      $isActive = $data['is_active'] ?? 1;

      if ($creditHours <= 0) {
          $response->getBody()->write(json_encode(['message' => "Bad Request: Credit hours must be a positive number."]));
          return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
      }

      try {
          $pdo->beginTransaction();

          // Check if course code already exists
          $stmtCheckCode = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE course_code = :course_code');
          $stmtCheckCode->bindParam(':course_code', $courseCode);
          $stmtCheckCode->execute();
          if ($stmtCheckCode->fetchColumn() > 0) {
              $pdo->rollBack();
              $response->getBody()->write(json_encode(['message' => "Conflict: Course with code '{$courseCode}' already exists."]));
              return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
          }

          // Verify lecturer_id if provided
          if ($lecturerId !== null) {
              $stmtLecturer = $pdo->prepare('SELECT COUNT(*) FROM lecturers WHERE id = :lecturer_id');
              $stmtLecturer->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
              $stmtLecturer->execute();
              if ($stmtLecturer->fetchColumn() === 0) {
                  $pdo->rollBack();
                  $response->getBody()->write(json_encode(['message' => "Bad Request: Assigned Lecturer ID {$lecturerId} not found."]));
                  return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
              }
          }

          // Insert new course
          $stmtInsert = $pdo->prepare('
              INSERT INTO courses (course_code, course_name, credit_hours, lecturer_id, semester, academic_year, is_active)
              VALUES (:course_code, :course_name, :credit_hours, :lecturer_id, :semester, :academic_year, :is_active)
          ');
          $stmtInsert->bindParam(':course_code', $courseCode);
          $stmtInsert->bindParam(':course_name', $courseName);
          $stmtInsert->bindParam(':credit_hours', $creditHours, PDO::PARAM_INT);
          if ($lecturerId === null) {
              $stmtInsert->bindValue(':lecturer_id', null, PDO::PARAM_NULL);
          } else {
              $stmtInsert->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
          }
          $stmtInsert->bindParam(':semester', $semester);
          $stmtInsert->bindParam(':academic_year', $academicYear);
          $stmtInsert->bindParam(':is_active', $isActive, PDO::PARAM_INT);
          $stmtInsert->execute();

          $newCourseId = $pdo->lastInsertId();

          $pdo->commit();

          // Fetch the newly created course with lecturer name for consistent frontend display
          $stmtFetch = $pdo->prepare('
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
                  0 AS student_count -- New courses have 0 students initially
              FROM
                  courses AS c
              LEFT JOIN
                  lecturers AS l ON c.lecturer_id = l.id
              WHERE c.id = :id
          ');
          $stmtFetch->bindParam(':id', $newCourseId, PDO::PARAM_INT);
          $stmtFetch->execute();
          $newCourse = $stmtFetch->fetch(PDO::FETCH_ASSOC);


          $response->getBody()->write(json_encode(['data' => $newCourse, 'message' => 'Course created successfully.', 'status' => 'success']));
          return $response->withStatus(201)->withHeader('Content-Type', 'application/json');

      } catch (PDOException $e) {
          $pdo->rollBack();
          // Catch unique constraint violation specifically for course_code
          if ($e->getCode() == 23000 && strpos($e->getMessage(), 'course_code') !== false) {
               error_log("Duplicate course code attempt: " . $e->getMessage());
               $response->getBody()->write(json_encode(['message' => 'Course code already exists.', 'error' => $e->getMessage()]));
               return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
          }
          error_log("Database error creating course: " . $e->getMessage());
          $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
          return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
      } catch (Exception $e) {
          $pdo->rollBack();
          error_log("Application error creating course: " . $e->getMessage());
          $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
          return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
      }
  });

  /**
   * Route: PATCH /api/v1/admin/courses/{course_id}
   * Description: Updates an existing course's details.
   * Parameters:
   * - course_id (int): The ID of the course.
   * Request Body: JSON object with fields to update (course_name, credit_hours, semester, academic_year, lecturer_id, is_active).
   * Returns:
   * - 200 OK: JSON message confirming successful update.
   * - 400 Bad Request: If invalid data is provided.
   * - 404 Not Found: If the course does not exist.
   * - 500 Internal Server Error: For database or unexpected errors.
   */
  $group->patch('/courses/{course_id}', function (Request $request, Response $response, array $args) {
      $pdo = $this->get('db');
      $courseId = $args['course_id'];
      $data = $request->getParsedBody();

      try {
          // Check if course exists
          $stmtCourse = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id');
          $stmtCourse->bindParam(':course_id', $courseId, PDO::PARAM_INT);
          $stmtCourse->execute();
          if ($stmtCourse->fetchColumn() === 0) {
              $response->getBody()->write(json_encode(['message' => "Course ID {$courseId} not found."]));
              return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
          }

          $updateFields = [];
          $updateParams = [];

          if (isset($data['course_name'])) {
              $updateFields[] = 'course_name = :course_name';
              $updateParams[':course_name'] = $data['course_name'];
          }
          if (isset($data['credit_hours'])) {
              $creditHours = (int)$data['credit_hours'];
              if ($creditHours <= 0) {
                  $response->getBody()->write(json_encode(['message' => "Bad Request: Credit hours must be a positive number."]));
                  return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
              }
              $updateFields[] = 'credit_hours = :credit_hours';
              $updateParams[':credit_hours'] = $creditHours;
          }
          if (isset($data['lecturer_id'])) {
              $lecturerId = $data['lecturer_id'] ?? null; // Can be null to unassign
              // Verify lecturer_id if not null
              if ($lecturerId !== null) {
                  $stmtLecturer = $pdo->prepare('SELECT COUNT(*) FROM lecturers WHERE id = :lecturer_id');
                  $stmtLecturer->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
                  $stmtLecturer->execute();
                  if ($stmtLecturer->fetchColumn() === 0) {
                      $response->getBody()->write(json_encode(['message' => "Bad Request: Assigned Lecturer ID {$lecturerId} not found."]));
                      return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                  }
              }
              $updateFields[] = 'lecturer_id = :lecturer_id';
              if ($lecturerId === null) {
                  $updateParams[':lecturer_id'] = null; // Bind as NULL
              } else {
                  $updateParams[':lecturer_id'] = $lecturerId;
              }
          }
          if (isset($data['semester'])) {
              $updateFields[] = 'semester = :semester';
              $updateParams[':semester'] = $data['semester'];
          }
          if (isset($data['academic_year'])) {
              $updateFields[] = 'academic_year = :academic_year';
              $updateParams[':academic_year'] = $data['academic_year'];
          }
          if (isset($data['is_active'])) {
              $updateFields[] = 'is_active = :is_active';
              $updateParams[':is_active'] = (int)$data['is_active'];
          }

          if (empty($updateFields)) {
              $response->getBody()->write(json_encode(['message' => 'No fields to update.', 'status' => 'info']));
              return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
          }

          $updateFields[] = 'updated_at = NOW()';
          $sql = 'UPDATE courses SET ' . implode(', ', $updateFields) . ' WHERE id = :course_id';
          $stmtUpdate = $pdo->prepare($sql);
          $stmtUpdate->bindParam(':course_id', $courseId, PDO::PARAM_INT);
          foreach ($updateParams as $param => $value) {
              // Special handling for NULL binding
              if ($value === null && $param === ':lecturer_id') {
                  $stmtUpdate->bindValue($param, null, PDO::PARAM_NULL);
              } else {
                  $stmtUpdate->bindValue($param, $value);
              }
          }
          $stmtUpdate->execute();

          $response->getBody()->write(json_encode(['message' => 'Course updated successfully.', 'status' => 'success']));
          return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

      } catch (PDOException $e) {
          error_log("Database error updating course {$courseId}: " . $e->getMessage());
          $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
          return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
      } catch (Exception $e) {
          error_log("Application error updating course {$courseId}: " . $e->getMessage());
          $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
          return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
      }
  });

  /**
   * Route: PATCH /api/v1/admin/courses/{course_id}/toggle-active
   * Description: Toggles the `is_active` status of a course.
   * Parameters:
   * - course_id (int): The ID of the course.
   * Request Body: JSON object with `is_active` (0 or 1).
   * Returns:
   * - 200 OK: JSON message confirming status update.
   * - 400 Bad Request: If `is_active` is missing or invalid.
   * - 404 Not Found: If the course does not exist.
   * - 500 Internal Server Error: For database or unexpected errors.
   */
  $group->patch('/courses/{course_id}/toggle-active', function (Request $request, Response $response, array $args) {
      $pdo = $this->get('db');
      $courseId = $args['course_id'];
      $data = $request->getParsedBody();
      $isActive = $data['is_active'] ?? null;

      if (!in_array($isActive, [0, 1], true)) {
          $response->getBody()->write(json_encode(['message' => "Bad Request: 'is_active' must be 0 or 1."]));
          return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
      }

      try {
          // Check if course exists
          $stmtCourse = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :course_id');
          $stmtCourse->bindParam(':course_id', $courseId, PDO::PARAM_INT);
          $stmtCourse->execute();
          if ($stmtCourse->fetchColumn() === 0) {
              $response->getBody()->write(json_encode(['message' => "Course ID {$courseId} not found."]));
              return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
          }

          // Update is_active status
          $stmtUpdate = $pdo->prepare('UPDATE courses SET is_active = :is_active, updated_at = NOW() WHERE id = :course_id');
          $stmtUpdate->bindParam(':is_active', $isActive, PDO::PARAM_INT);
          $stmtUpdate->bindParam(':course_id', $courseId, PDO::PARAM_INT);
          $stmtUpdate->execute();

          $response->getBody()->write(json_encode(['message' => 'Course status updated successfully.', 'status' => 'success']));
          return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

      } catch (PDOException $e) {
          error_log("Database error toggling course active status {$courseId}: " . $e->getMessage());
          $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
          return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
      } catch (Exception $e) {
          error_log("Application error toggling course active status {$courseId}: " . $e->getMessage());
          $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
          return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
      }
  });
}
?>