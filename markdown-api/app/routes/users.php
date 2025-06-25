<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

return function (RouteCollectorProxy $group){
  $group->post('/login', function(Request $request, Response $response, array $args){
    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    // Here you would typically validate the credentials against a database
    try {
      $pdo = $this->get('db');
      $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND password_hash = :password');
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':password', $password);
      $stmt->execute();
      $user = $stmt->fetch();

      if ($user) {
          $response->getBody()->write(json_encode(['message' => 'Login successful', 'data' => $user, 'status' => 'success']));
          return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
      } else {
          $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
          return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
      }

    } catch (\Throwable $th) {
      //throw $th;
    }
    if ($username === 'admin' && $password === 'password') {
        $response->getBody()->write(json_encode(['message' => 'Login successful']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
  });
}
?>