<?php
class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function generateToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->db->conn->prepare("
            INSERT INTO auth_tokens (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");

        try {
            $stmt->execute([$userId, $token, $expires]);
            error_log("Token inserted successfully. User ID: " . $userId . ", Token: " . $token . ", Expires: " . $expires);
        } catch (PDOException $e) {
            error_log("Error inserting token: " . $e->getMessage());
            return false; 
        }

        return $token;
    }

    public function validateToken($token) {
        try {
            $stmt = $this->db->conn->prepare("
                SELECT user_id FROM auth_tokens
                WHERE token = ?
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result) {
                // Now fetch user details
                $stmt = $this->db->conn->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
                $stmt->execute([$result['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    return [
                        'user_id' => $user['id'],            // 👈 Add this line
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'created_at' => $user['created_at']
                    ];
                }
            }
        } catch (PDOException $e) {
            return null;
        }
    
        return null;
    }
    
    
    

    public function generateRefreshToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days')); 
        
        $stmt = $this->db->conn->prepare("
            INSERT INTO refresh_tokens (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $token, $expires]);
        
        return $token;
    }
    
    public function validateRefreshToken($token) {
        $stmt = $this->db->conn->prepare("
            SELECT user_id FROM refresh_tokens
            WHERE token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }   
    public function revokeRefreshToken($token) {
        $stmt = $this->db->conn->prepare("
            DELETE FROM refresh_tokens WHERE token = ?
        ");
        return $stmt->execute([$token]);
    }  

    public static function check()
{
    $headers = apache_request_headers(); // Use apache_request_headers() if getallheaders() not working
    if (!isset($headers['Authorization'])) {
        return null;
    }

    $authHeader = $headers['Authorization'];

    if (strpos($authHeader, 'Bearer ') !== 0) {
        return null;
    }

    $token = trim(str_replace('Bearer', '', $authHeader));

    // Call validateToken
    $authInstance = new self();
    $payload = $authInstance->validateToken($token);

    if (!$payload || !isset($payload['user_id'])) {
        return null;
    }
    
    return ['id' => $payload['user_id']];
    
}

}
?>
