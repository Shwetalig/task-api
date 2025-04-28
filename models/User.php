<?php
require_once(__DIR__ . '/../core/Database.php');

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function createUser($username, $passwordHash, $email, $ipAddress = null)
    {
        $stmt = $this->db->conn->prepare("
            INSERT INTO users (username, password_hash, email, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$username, $passwordHash, $email, $ipAddress]);
        return $this->db->conn->lastInsertId();
    }

    public function getById($id) {
        $stmt = $this->db->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } 

    public function update($id, $username, $email) {
        $stmt = $this->db->conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $id]);
    }

    
    public function getUserByUsername($username) {
        $stmt = $this->db->conn->prepare("
            SELECT * FROM users WHERE username = ?
        ");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($username, $newHash) {
        $stmt = $this->db->conn->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$newHash, $username]);
    }
    
}
?>
