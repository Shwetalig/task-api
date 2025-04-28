<?php
require_once(__DIR__ . '/../core/Auth.php');
require_once(__DIR__ . '/../models/Task.php');

class TaskController {
    private $taskModel;
    private $auth;

    public function __construct() {
        $this->taskModel = new Task();
        $this->auth = new Auth();
    }
    private function getAuthenticatedUser() {
        $headers = getallheaders();
        error_log("Headers: " . print_r($headers, true)); // Log headers
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        error_log("Token extracted: " . $token); // Log extracted token

        $auth = new Auth();
        $user = $auth->validateToken($token);

        if (!$user) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        return $user['user_id'];
    }


    public function getAllTasks()
     {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 10;       
        $result = $this->taskModel->getAllTasks(null, $page, $perPage); // Pass null
        Response::json($result);
    }
    
    public function createTask() 
    {
        $userId = $this->getAuthenticatedUser();
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['title']) || !isset($data['description'])) {
            Response::json(['error' => 'Invalid input'], 400);
        }
        $taskId = $this->taskModel->createTask(
            $userId,
            $data['title'],
            $data['description'],
            $data['status'] ?? 'pending'
        );
        Response::json(['id' => $taskId], 201);
    }

    public function getTask($id) {
        $userId = $this->getAuthenticatedUser();
        $task = $this->taskModel->getTask($id, $userId);
        if (!$task) {
            Response::json(['error' => 'Task not found'], 404);
        }
        Response::json($task, 200);
    }

    public function updateTask($id) {
        $userId = $this->getAuthenticatedUser();
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['title']) || !isset($data['description'])) {
            Response::json(['error' => 'Invalid input'], 400);
        }
        $updated = $this->taskModel->updateTask(
            $id,
            $userId,
            $data['title'],
            $data['description'],
            $data['status'] ?? 'pending'
        );
        if ($updated === 0) {
            Response::json(['error' => 'Task not found or not authorized'], 404);
        }
        Response::json(['message' => 'Task updated'], 200);
    }

    public static function delete($taskId) {
        $user = Auth::check();
        if (!$user) return Response::unauthorized();
    
        $task = new Task();
        $task->softDelete($user['id'], $taskId);
    
        return Response::json(['message' => 'Task moved to trash']);
    }
    

    public static function trashed() {
        $user = Auth::check();
        if (!$user) return Response::unauthorized();    
        $task = new Task();
        $tasks = $task->getTrashedByUser($user['id']);    
        return Response::json($tasks);
    }
       
    public static function restore($taskId) {
        $user = Auth::check();
        if (!$user) return Response::unauthorized();
    
        $task = new Task();
        $task->restore($user['id'], $taskId);
    
        return Response::json(['message' => 'Task restored successfully']);
    }
    
    
}
?>
