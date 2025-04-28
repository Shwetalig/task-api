<?php
require_once(__DIR__ . '/../core/Response.php');  // Ensure Response is included
require_once(__DIR__ . '/../core/Database.php');
require_once(__DIR__ . '/../models/User.php');
require_once(__DIR__ . '/../controllers/AuthController.php');
require_once(__DIR__ . '/../controllers/TaskController.php');

$db = new Database();
$userModel = new User($db);
$responseHandler = new Response();  // Corrected from ResponseHandler to Response

$authController = new AuthController($db, $userModel, $responseHandler);
$taskController = new TaskController($db);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$basePath = '/task-api/public';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Get body content for POST and PUT requests
$body = json_decode(file_get_contents('php://input'), true);

// Authentication routes
if ($requestUri === '/api/register' && $method === 'POST') {
    $authController->register();
} elseif ($requestUri === '/api/login' && $method === 'POST') {
    $authController->login();
} elseif ($requestUri === '/api/refresh' && $method === 'POST') {
    $authController->refreshToken();
} elseif ($requestUri === '/api/logout' && $method === 'POST') {
    $authController->logout();
} elseif ($requestUri === '/api/profile' && $method === 'GET') {
    $authController->getProfile();
} elseif ($requestUri === '/api/profile/update' && $method === 'PUT') {
    $authController->updateProfile($body);
}

if ($requestUri === '/api/forgot-password' && $method === 'POST') {
    require_once '../controllers/PasswordController.php';
    PasswordController::forgotPassword($body);
} elseif ($requestUri === '/api/reset-password' && $method === 'POST') {
    require_once '../controllers/PasswordController.php';
    PasswordController::resetPassword($body);
}

elseif ($requestUri === '/api/tasks/trashed' && $method === 'GET') {
    TaskController::trashed();
} elseif (preg_match("#^/api/tasks/restore/(\d+)$#", $requestUri, $matches) && $method === 'PUT') {
    TaskController::restore($matches[1]);
} elseif (preg_match("#^/api/tasks/(\d+)$#", $requestUri, $matches) && $method === 'DELETE') {
    TaskController::delete($matches[1]);
}



// Task routes
elseif (preg_match('/^\/api\/tasks\/(\d+)$/', $requestUri, $matches)) {
    $taskId = $matches[1];
    if ($method === 'GET') {
        $taskController->getTask($taskId);
    } elseif ($method === 'PUT') {
        $taskController->updateTask($taskId, $body);
    } elseif ($method === 'DELETE') {
        $taskController->deleteTask($taskId);
    } else {
        Response::json(['error' => 'Method not allowed'], 405);
    }
} elseif ($requestUri === '/api/tasks' && $method === 'GET') {
    $taskController->getAllTasks();
} elseif ($requestUri === '/api/tasks' && $method === 'POST') {
    $taskController->createTask($body);
} else {
    Response::json(['error' => 'Endpoint not found'], 404);
}
?>
