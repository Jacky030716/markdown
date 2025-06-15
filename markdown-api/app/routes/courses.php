<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group){
  /**
   * Route: POST /api/v1/courses/{course_id}/marks
   * Description: Adds a new marks component to the specified course.
   * Parameters:
   * - course_id (int): The unique identifier of the course.
   * Request Body:
   * - name (string): The name of the marks component.
   * - type (string): The type of the marks component (e.g., "assignment", "exam").
   * - max_mark (float): The maximum mark for the component.
   * - weight (float): The weight of the component in the overall course assessment.
   * Returns:
   * - 201 Created: JSON message indicating the marks component was added successfully.
   * - 400 Bad Request: JSON message if the course ID is invalid.
   * - 404 Not Found: JSON message if no marks component was added for the course.
   * - 500 Internal Server Error: JSON message for database or unexpected errors.
   */
  $group->post('/{course_id}/marks', function (Request $request, Response $response, array $args) {
    $courseId = $args['course_id'];

    if (!is_numeric($courseId) || $courseId <= 0) {
      $response->getBody()->write(json_encode(['message' => 'Invalid course ID']));
      return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    
    try {
      $pdo = $this->get('db');
      
      // Retrieve the JSON body from the request
      $data = json_decode($request->getBody()->getContents(), true);

      // Prepare the SQL statement to insert the marks component to the specified course
      $stmt = $pdo->prepare('INSERT INTO mark_components (course_id, name, type, max_mark, weight) VALUES (:course_id, :name, :type, :max_mark, :weight)');

      $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
      $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
      $stmt->bindParam(':max_mark', $data['max_mark'], PDO::PARAM_STR);
      $stmt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);

      // Execute the statement
      $stmt->execute();

      // Check if the insert was successful
      if ($stmt->rowCount() === 0) {
        $response->getBody()->write(json_encode(['message' => "No marks component was added for course ID: {$courseId}"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }

      $response->getBody()->write(json_encode(['message' => "Marks component added successfully for course ID: {$courseId}", 'status' => 'success']));
      return $response->withStatus(201)->withHeader('Content-Type', 'application/json');


    } catch (PDOException $e) {
      error_log("Database error updating course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
      error_log("Application error updating course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } 
  });

  /**
   * Route: PATCH /api/v1/courses/{course_id}/marks/{component_id}
   * Description: Updates an existing marks component for the specified course.
   * Parameters:
   * - course_id (int): The unique identifier of the course.
   * - component_id (int): The unique identifier of the marks component to be updated.
   * Request Body:
   * - name (string): The new name of the marks component.
   * - type (string): The new type of the marks component (e.g., "assignment", "exam"). 
   * - max_mark (float): The new maximum mark for the component.
   * - weight (float): The new weight of the component in the overall course assessment.
   * Returns:
   * - 200 OK: JSON message indicating the marks component was updated successfully.
   * - 400 Bad Request: JSON message if the course or component ID is invalid.
   * - 404 Not Found: JSON message if no marks component was found for the course.
   */
  $group->put('/{course_id}/marks/{component_id}', function (Request $request, Response $response, array $args) {
    $courseId = $args['course_id'];
    $componentId = $args['component_id'];

    if (!is_numeric($courseId) || !is_numeric($componentId) || $courseId <= 0 || $componentId <= 0) {
      $response->getBody()->write(json_encode(['message' => 'Invalid course or component ID']));
      return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
      $pdo = $this->get('db');

      // Retrieve the JSON body from the request
      $data = json_decode($request->getBody()->getContents(), true);

      // Prepare the SQL statement to update the marks component
      $stmt = $pdo->prepare('UPDATE mark_components SET name = :name, type = :type, max_mark = :max_mark, weight = :weight WHERE course_id = :course_id AND id = :component_id');
      $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $stmt->bindParam(':component_id', $componentId, PDO::PARAM_INT);
      $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
      $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
      $stmt->bindParam(':max_mark', $data['max_mark'], PDO::PARAM_STR);
      $stmt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);

      // Execute the statement
      $stmt->execute();

      // Check if any rows were affected
      if ($stmt->rowCount() === 0) {
        $response->getBody()->write(json_encode(['message' => "No marks component found for course ID: {$courseId} and component ID: {$componentId}"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }

      $response->getBody()->write(json_encode(['message' => "Marks component updated successfully for course ID: {$courseId} and component ID: {$componentId}", 'status' => 'success']));
      return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
      error_log("Database error updating component {$componentId} for course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
      error_log("Application error updating component {$componentId} for course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
  });

  /**
   * Route: DELETE /api/v1/courses/{course_id}/marks/{component_id}
   * Description: Deletes a marks component from the specified course.
   * Parameters:
   * - course_id (int): The unique identifier of the course.
   * - component_id (int): The unique identifier of the marks component to be deleted.
   * Returns:
   * - 200 OK: JSON message indicating the marks component was deleted successfully.
   * - 400 Bad Request: JSON message if the course or component ID is invalid.
   * - 404 Not Found: JSON message if no marks component was found for the course.
   * - 500 Internal Server Error: JSON message for database or unexpected errors.
   */
  $group->delete('/{course_id}/marks/{component_id}', function (Request $request, Response $response, array $args) {
    $courseId = $args['course_id'];
    $componentId = $args['component_id'];

    if (!is_numeric($courseId) || !is_numeric($componentId) || $courseId <= 0 || $componentId <= 0) {
      $response->getBody()->write(json_encode(['message' => 'Invalid course or component ID']));
      return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
      $pdo = $this->get('db');

      // Prepare the SQL statement to delete the marks component
      $stmt = $pdo->prepare('DELETE FROM mark_components WHERE course_id = :course_id AND id = :component_id');
      $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
      $stmt->bindParam(':component_id', $componentId, PDO::PARAM_INT);

      // Execute the statement
      $stmt->execute();

      // Check if any rows were affected
      if ($stmt->rowCount() === 0) {
        $response->getBody()->write(json_encode(['message' => "No marks component found for course ID: {$courseId} and component ID: {$componentId}"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
      }

      $response->getBody()->write(json_encode(['message' => "Marks component deleted successfully for course ID: {$courseId} and component ID: {$componentId}", 'status' => 'success']));
      return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
      error_log("Database error deleting component {$componentId} for course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
      error_log("Application error deleting component {$componentId} for course {$courseId}: " . $e->getMessage());
      $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred', 'error' => $e->getMessage()]));
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
  });
}
?>