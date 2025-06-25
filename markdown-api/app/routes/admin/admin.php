<?php
// app/routes/admin.php
// This file defines API routes for administrator functions, specifically user management.

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {

    /**
     * Route: GET /api/v1/admin/users
     * Description: Retrieves a comprehensive list of all users in the system,
     * joining data from role-specific tables (students, lecturers, advisors).
     * Returns:
     * - 200 OK: JSON array of user objects with detailed information.
     * - 500 Internal Server Error: JSON message for database or unexpected errors.
     */
    $group->get('/users', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');

        try {
            // Join users with students, lecturers, and advisors using LEFT JOIN
            // to get all relevant data for each user.
            $stmt = $pdo->prepare('
                SELECT
                    u.id AS user_id,
                    u.email,
                    u.role,
                    u.is_active,
                    u.created_at AS user_created_at,
                    u.updated_at AS user_updated_at,
                    s.id AS student_profile_id,
                    s.matric_no,
                    s.name AS student_name,
                    s.program,
                    s.year_of_study,
                    s.advisor_id AS student_advisor_id,
                    l.id AS lecturer_profile_id,
                    l.lecturer_id,
                    l.name AS lecturer_name,
                    l.department AS lecturer_department,
                    a.id AS advisor_profile_id,
                    a.advisor_id,
                    a.name AS advisor_name,
                    a.department AS advisor_department
                FROM
                    users AS u
                LEFT JOIN
                    students AS s ON u.id = s.user_id AND u.role = "student"
                LEFT JOIN
                    lecturers AS l ON u.id = l.user_id AND u.role = "lecturer"
                LEFT JOIN
                    advisors AS a ON u.id = a.user_id AND u.role = "advisor"
            ');
            $stmt->execute();
            $rawUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Reformat the data to match the frontend's expected structure
            $users = [];
            foreach ($rawUsers as $row) {
                $user = [
                    'id' => (int)$row['user_id'],
                    'email' => $row['email'],
                    'role' => $row['role'],
                    'is_active' => (bool)$row['is_active'],
                    'created_at' => $row['user_created_at'],
                    'updated_at' => $row['user_updated_at'],
                ];

                // Conditionally add role-specific data and assign the correct 'name'
                switch ($row['role']) {
                    case 'student':
                        $user['name'] = $row['student_name'];
                        $user['matric_no'] = $row['matric_no'];
                        $user['program'] = $row['program'];
                        $user['year_of_study'] = $row['year_of_study'];
                        $user['advisor_id'] = $row['student_advisor_id'];
                        break;
                    case 'lecturer':
                        $user['name'] = $row['lecturer_name'];
                        $user['lecturer_id'] = $row['lecturer_id'];
                        $user['department'] = $row['lecturer_department'];
                        break;
                    case 'advisor':
                        $user['name'] = $row['advisor_name'];
                        $user['advisor_id'] = $row['advisor_profile_id'];
                        $user['advisor_matric_no'] = $row['advisor_id'];
                        $user['department'] = $row['advisor_department'];
                        break;
                    case 'admin':
                        // For admin, 'name' might just be based on a default or derived from email
                        // If you have a separate admin profile table, join it here
                        // For now, let's just use email as a fallback for name if no specific name provided
                        $user['name'] = $user['email']; // Or fetch from a dedicated admin_profiles table if it exists
                        break;
                }
                $users[] = $user;
            }

            $response->getBody()->write(json_encode(['data' => $users, 'status' => 'success']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            error_log("Database error fetching all users: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("Application error fetching all users: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: POST /api/v1/admin/users
     * Description: Adds a new user to the system, creating entries in `users` table
     * and the corresponding role-specific table.
     * Request Body: JSON object with user details including `email`, `password`, `role`,
     * `name`, and other role-specific fields (e.g., `matric_no`, `lecturer_id`, `department`).
     * Returns:
     * - 201 Created: JSON object of the newly created user.
     * - 400 Bad Request: If required fields are missing or validation fails.
     * - 409 Conflict: If email already exists.
     * - 500 Internal Server Error: For database or unexpected errors.
     */
    $group->post('/users', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $data = $request->getParsedBody();

        // Basic validation for common fields
        $requiredFields = ['email', 'password', 'role', 'name'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $response->getBody()->write(json_encode(['message' => "Bad Request: '{$field}' is required."]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }

        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password
        $role = $data['role'];
        $name = $data['name'];
        $is_active = $data['is_active'] ?? 1; // Default to active

        // Validate role enum
        $allowedRoles = ['student', 'lecturer', 'advisor', 'admin'];
        if (!in_array($role, $allowedRoles)) {
            $response->getBody()->write(json_encode(['message' => "Bad Request: Invalid role specified."]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $pdo->beginTransaction();

            // Check if email already exists
            $stmtCheckEmail = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmtCheckEmail->bindParam(':email', $email);
            $stmtCheckEmail->execute();
            if ($stmtCheckEmail->fetchColumn() > 0) {
                $pdo->rollBack();
                $response->getBody()->write(json_encode(['message' => "Conflict: User with this email already exists."]));
                return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
            }

            // 1. Insert into `users` table
            $stmtUser = $pdo->prepare('INSERT INTO users (email, password_hash, role, is_active) VALUES (:email, :password_hash, :role, :is_active)');
            $stmtUser->bindParam(':email', $email);
            $stmtUser->bindParam(':password_hash', $password);
            $stmtUser->bindParam(':role', $role);
            $stmtUser->bindParam(':is_active', $is_active, PDO::PARAM_INT);
            $stmtUser->execute();
            $user_id = $pdo->lastInsertId();

            // 2. Insert into role-specific table
            $profileData = [];
            switch ($role) {
                case 'student':
                    $requiredStudentFields = ['matric_no', 'program', 'year_of_study'];
                    foreach ($requiredStudentFields as $field) {
                        if (empty($data[$field])) {
                             $pdo->rollBack();
                            $response->getBody()->write(json_encode(['message' => "Bad Request: Student '{$field}' is required."]));
                            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                        }
                    }
                    $stmtProfile = $pdo->prepare('
                        INSERT INTO students (user_id, matric_no, name, program, year_of_study, advisor_id)
                        VALUES (:user_id, :matric_no, :name, :program, :year_of_study, :advisor_id)
                    ');
                    $stmtProfile->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmtProfile->bindParam(':matric_no', $data['matric_no']);
                    $stmtProfile->bindParam(':name', $name);
                    $stmtProfile->bindParam(':program', $data['program']);
                    $stmtProfile->bindParam(':year_of_study', $data['year_of_study'], PDO::PARAM_INT);
                    $advisorId = $data['advisor_id'] ?? null;
                    $stmtProfile->bindParam(':advisor_id', $advisorId, PDO::PARAM_INT);
                    $stmtProfile->execute();
                    $profileData = ['matric_no' => $data['matric_no'], 'program' => $data['program'], 'year_of_study' => $data['year_of_study'], 'advisor_id' => $advisorId];
                    break;
                case 'lecturer':
                    if (empty($data['lecturer_id'])) {
                         $pdo->rollBack();
                        $response->getBody()->write(json_encode(['message' => "Bad Request: Lecturer 'lecturer_id' is required."]));
                        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                    }
                    $stmtProfile = $pdo->prepare('
                        INSERT INTO lecturers (user_id, lecturer_id, name, department)
                        VALUES (:user_id, :lecturer_id, :name, :department)
                    ');
                    $stmtProfile->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmtProfile->bindParam(':lecturer_id', $data['lecturer_id']);
                    $stmtProfile->bindParam(':name', $name);
                    $department = $data['department'] ?? null;
                    $stmtProfile->bindParam(':department', $department);
                    $stmtProfile->execute();
                    $profileData = ['lecturer_id' => $data['lecturer_id'], 'department' => $department];
                    break;
                case 'advisor':
                     if (empty($data['advisor_id'])) {
                         $pdo->rollBack();
                        $response->getBody()->write(json_encode(['message' => "Bad Request: Advisor 'advisor_id' is required."]));
                        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                    }
                    $stmtProfile = $pdo->prepare('
                        INSERT INTO advisors (user_id, advisor_id, name, department)
                        VALUES (:user_id, :advisor_id, :name, :department)
                    ');
                    $stmtProfile->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmtProfile->bindParam(':advisor_id', $data['advisor_id']);
                    $stmtProfile->bindParam(':name', $name);
                    $department = $data['department'] ?? null;
                    $stmtProfile->bindParam(':department', $department);
                    $stmtProfile->execute();
                    $profileData = ['advisor_id' => $data['advisor_id'], 'department' => $department];
                    break;
                case 'admin':
                    // Admins might not have a separate profile table
                    // You might add specific fields here if your admin table has more columns
                    break;
            }

            $pdo->commit();

            $newUser = array_merge([
                'id' => (int)$user_id,
                'email' => $email,
                'role' => $role,
                'name' => $name,
                'is_active' => (bool)$is_active,
                'created_at' => date('Y-m-d H:i:s'), // Current timestamp
                'updated_at' => date('Y-m-d H:i:s')  // Current timestamp
            ], $profileData);

            $response->getBody()->write(json_encode(['data' => $newUser, 'message' => 'User created successfully.', 'status' => 'success']));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            $pdo->rollBack();
            // Check for duplicate entry error specifically (e.g., for matric_no, lecturer_id, advisor_id uniqueness)
            if ($e->getCode() == 23000) { // Integrity constraint violation
                 if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errorMessage = "Duplicate entry error. Please check for existing Matric No., Lecturer ID, or Advisor ID.";
                 } else {
                    $errorMessage = "Database constraint violation.";
                 }
                error_log("Database constraint error adding user: " . $e->getMessage());
                $response->getBody()->write(json_encode(['message' => $errorMessage, 'error' => $e->getMessage()]));
                return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
            }
            error_log("Database error adding user: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Application error adding user: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: PATCH /api/v1/admin/users/{user_id}
     * Description: Updates an existing user's details and/or active status.
     * Request Body: JSON object with fields to update (e.g., `name`, `email`, `password`, `is_active`, role-specific fields).
     * Returns:
     * - 200 OK: JSON message confirming successful update.
     * - 400 Bad Request: If invalid data is provided.
     * - 404 Not Found: If the user does not exist.
     * - 409 Conflict: If the updated email already exists for another user.
     * - 500 Internal Server Error: For database or unexpected errors.
     */
    $group->patch('/users/{user_id}', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $userId = $args['user_id'];
        $data = $request->getParsedBody();

        try {
            $pdo->beginTransaction();

            // 1. Fetch current user data to determine role and existing email
            $stmtUser = $pdo->prepare('SELECT email, role FROM users WHERE id = :user_id');
            $stmtUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtUser->execute();
            $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$currentUser) {
                $pdo->rollBack();
                $response->getBody()->write(json_encode(['message' => "User ID {$userId} not found."]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Check for duplicate email if email is being updated
            if (isset($data['email']) && $data['email'] !== $currentUser['email']) {
                $stmtCheckEmail = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND id != :user_id');
                $stmtCheckEmail->bindParam(':email', $data['email']);
                $stmtCheckEmail->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmtCheckEmail->execute();
                if ($stmtCheckEmail->fetchColumn() > 0) {
                    $pdo->rollBack();
                    $response->getBody()->write(json_encode(['message' => "Conflict: New email already exists for another user."]));
                    return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
                }
            }

            // 2. Update `users` table
            $updateUserFields = [];
            $updateUserParams = [];

            if (isset($data['email'])) {
                $updateUserFields[] = 'email = :email';
                $updateUserParams[':email'] = $data['email'];
            }
            if (isset($data['password']) && !empty($data['password'])) {
                $updateUserFields[] = 'password_hash = :password_hash';
                $updateUserParams[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            if (isset($data['is_active'])) {
                $updateUserFields[] = 'is_active = :is_active';
                $updateUserParams[':is_active'] = $data['is_active'];
            }

            if (!empty($updateUserFields)) {
                $updateUserFields[] = 'updated_at = NOW()'; // Always update timestamp
                $sql = 'UPDATE users SET ' . implode(', ', $updateUserFields) . ' WHERE id = :user_id';
                $stmtUpdateUser = $pdo->prepare($sql);
                $stmtUpdateUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
                foreach ($updateUserParams as $param => $value) {
                    $stmtUpdateUser->bindValue($param, $value);
                }
                $stmtUpdateUser->execute();
            }

            // 3. Update role-specific table based on current role
            $profileTableName = null;
            $profileIdColumn = null;
            $profileUpdateFields = [];
            $profileUpdateParams = [];

            switch ($currentUser['role']) {
                case 'student':
                    $profileTableName = 'students';
                    $profileIdColumn = 'user_id';
                    if (isset($data['name'])) {
                        $profileUpdateFields[] = 'name = :name';
                        $profileUpdateParams[':name'] = $data['name'];
                    }
                    if (isset($data['matric_no'])) {
                        $profileUpdateFields[] = 'matric_no = :matric_no';
                        $profileUpdateParams[':matric_no'] = $data['matric_no'];
                    }
                    if (isset($data['program'])) {
                        $profileUpdateFields[] = 'program = :program';
                        $profileUpdateParams[':program'] = $data['program'];
                    }
                    if (isset($data['year_of_study'])) {
                        $profileUpdateFields[] = 'year_of_study = :year_of_study';
                        $profileUpdateParams[':year_of_study'] = $data['year_of_study'];
                    }
                    if (isset($data['advisor_id'])) {
                        $profileUpdateFields[] = 'advisor_id = :advisor_id';
                        $profileUpdateParams[':advisor_id'] = $data['advisor_id'];
                    }
                    break;
                case 'lecturer':
                    $profileTableName = 'lecturers';
                    $profileIdColumn = 'user_id';
                    if (isset($data['name'])) {
                        $profileUpdateFields[] = 'name = :name';
                        $profileUpdateParams[':name'] = $data['name'];
                    }
                    if (isset($data['lecturer_id'])) {
                        $profileUpdateFields[] = 'lecturer_id = :lecturer_id';
                        $profileUpdateParams[':lecturer_id'] = $data['lecturer_id'];
                    }
                    if (isset($data['department'])) {
                        $profileUpdateFields[] = 'department = :department';
                        $profileUpdateParams[':department'] = $data['department'];
                    }
                    break;
                case 'advisor':
                    $profileTableName = 'advisors';
                    $profileIdColumn = 'user_id';
                    if (isset($data['name'])) {
                        $profileUpdateFields[] = 'name = :name';
                        $profileUpdateParams[':name'] = $data['name'];
                    }
                    if (isset($data['advisor_id'])) { // This refers to the 'advisor_id' column like 'A001'
                        $profileUpdateFields[] = 'advisor_id = :advisor_id_col';
                        $profileUpdateParams[':advisor_id_col'] = $data['advisor_id'];
                    }
                    if (isset($data['department'])) {
                        $profileUpdateFields[] = 'department = :department';
                        $profileUpdateParams[':department'] = $data['department'];
                    }
                    break;
                case 'admin':
                    // Admins typically only update user table fields (email, password, is_active)
                    break;
            }

            if ($profileTableName && !empty($profileUpdateFields)) {
                $profileUpdateFields[] = 'updated_at = NOW()'; // Always update timestamp
                $sqlProfile = "UPDATE {$profileTableName} SET " . implode(', ', $profileUpdateFields) . " WHERE {$profileIdColumn} = :profile_user_id";
                $stmtUpdateProfile = $pdo->prepare($sqlProfile);
                $stmtUpdateProfile->bindParam(':profile_user_id', $userId, PDO::PARAM_INT);
                foreach ($profileUpdateParams as $param => $value) {
                    $stmtUpdateProfile->bindValue($param, $value);
                }
                $stmtUpdateProfile->execute();
            }

            $pdo->commit();

            $response->getBody()->write(json_encode(['message' => 'User updated successfully.', 'status' => 'success']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) { // Integrity constraint violation (e.g., unique ID conflict)
                 if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errorMessage = "Duplicate entry error. Please check for existing Matric No., Lecturer ID, or Advisor ID.";
                 } else {
                    $errorMessage = "Database constraint violation.";
                 }
                error_log("Database constraint error updating user: " . $e->getMessage());
                $response->getBody()->write(json_encode(['message' => $errorMessage, 'error' => $e->getMessage()]));
                return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
            }
            error_log("Database error updating user: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Application error updating user: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    /**
     * Route: DELETE /api/v1/admin/users/{user_id}
     * Description: Deactivates a user account by setting `is_active` to 0 in the `users` table.
     * Parameters:
     * - user_id (int): The ID of the user to deactivate.
     * Returns:
     * - 200 OK: JSON message confirming successful deactivation.
     * - 404 Not Found: If the user does not exist.
     * - 500 Internal Server Error: For database or unexpected errors.
     */
    $group->delete('/users/{user_id}', function (Request $request, Response $response, array $args) {
        $pdo = $this->get('db');
        $userId = $args['user_id'];

        try {
            // Check if user exists
            $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id = :user_id');
            $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ($stmtCheck->fetchColumn() === 0) {
                $response->getBody()->write(json_encode(['message' => "User ID {$userId} not found."]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Deactivate the user (soft delete)
            $stmt = $pdo->prepare('UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = :user_id');
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $response->getBody()->write(json_encode(['message' => 'User account deactivated successfully.', 'status' => 'success']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            error_log("Database error deactivating user {$userId}: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'Internal Server Error: Database operation failed.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("Application error deactivating user {$userId}: " . $e->getMessage());
            $response->getBody()->write(json_encode(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

};