<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/api/v1', function (Group $group) {
        $group->group('/users', require __DIR__ . '/routes/users.php');
        $group->group('/lecturers', require __DIR__ . '/routes/lecturers.php');
        $group->group('/courses', require __DIR__ . '/routes/courses.php');
        $group->group('/students', require __DIR__ . '/routes/students.php');
        $group->group('/advisors', require __DIR__ . '/routes/advisors.php');
        $group->group('/admin', function (Group $adminGroup) {
            (require __DIR__ . '/routes/admin/admin.php')($adminGroup);
            (require __DIR__ . '/routes/admin/admin_courses.php')($adminGroup);
            (require __DIR__ . '/routes/admin/admin_manage_courses.php')($adminGroup);
        });
    });
};
