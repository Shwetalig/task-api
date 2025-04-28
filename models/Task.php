<?php
require_once(__DIR__ . '/../core/Database.php');

class Task {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllTasks($userId = null, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
    
        if ($userId !== null) {
            $stmt = $this->db->conn->prepare("
                SELECT * FROM tasks 
                WHERE user_id = ?
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute([$userId]);
        } else {
            // No user_id filter -> get all tasks
            $stmt = $this->db->conn->prepare("
                SELECT * FROM tasks 
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute();
        }
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    


    public function createTask($userId, $title, $description, $status) {
        $stmt = $this->db->conn->prepare("
            INSERT INTO tasks (user_id, title, description, status)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $description, $status]);
        return $this->db->conn->lastInsertId();
    }


    public function getTask($taskId, $userId) {
        $stmt = $this->db->conn->prepare("
            SELECT * FROM tasks WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$taskId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateTask($taskId, $userId, $title, $description, $status) {
        $stmt = $this->db->conn->prepare("
            UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$title, $description, $status, $taskId, $userId]);
        return $stmt->rowCount();
    }

    public function deleteTask($taskId, $userId) {
        $stmt = $this->db->conn->prepare("
            DELETE FROM tasks WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$taskId, $userId]);
        return $stmt->rowCount();
    }

    public function softDelete($userId, $taskId) {
        $stmt = $this->db->conn->prepare("UPDATE tasks SET is_deleted = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$taskId, $userId]);
    }

    public function getTrashedByUser($userId) {
        $stmt = $this->db->conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND is_deleted = 1");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function restore($userId, $taskId) {
        $stmt = $this->db->conn->prepare("UPDATE tasks SET is_deleted = 0 WHERE id = ? AND user_id = ?");
        $stmt->execute([$taskId, $userId]);
    }
    
    
}
?>
