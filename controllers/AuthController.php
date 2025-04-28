<?php
require_once(__DIR__ . '/../core/Database.php');
require_once(__DIR__ . '/../core/Auth.php');
require_once(__DIR__ . '/../models/User.php');

class AuthController
{
    private $db;
    private $userModel;

    public function __construct($db, $userModel)
    {
        $this->db = $db;
        $this->userModel = $userModel;
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $email = $data['email'] ?? '';

        // Validate input
        if (!$username || !$password || !$email) {
            return Response::json(['error' => 'Username, password, and email are required'], 400);
        }

        $ip_address = $_SERVER['REMOTE_ADDR'];

        // Create user
        $userId = $this->userModel->createUser(
            $username,
            password_hash($password, PASSWORD_BCRYPT),
            $email,
            $ip_address
        );

        return Response::json(['id' => $userId], 201);
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['username']) || !isset($data['password'])) {
            return Response::json(['error' => 'Username and password are required'], 400);
        }

        $ip_address = $_SERVER['REMOTE_ADDR'];
        $username = $data['username'];

        // Rate limiting
        $stmt = $this->db->conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > NOW() - INTERVAL 1 HOUR");
        $stmt->execute([$ip_address]);
        $attempt_count = $stmt->fetchColumn();

        if ($attempt_count >= 5) {
            return Response::json(['error' => 'Too many login attempts. Please try again after an hour.'], 429);
        }

        $user = $this->userModel->getUserByUsername($username);

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            $stmt = $this->db->conn->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
            $stmt->execute([$ip_address, $username]);
            return Response::json(['error' => 'Invalid credentials'], 401);
        }

        // Generate tokens
        $auth = new Auth();
        $accessToken = $auth->generateToken($user['id']);
        $refreshToken = $auth->generateRefreshToken($user['id']);

        return Response::json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ]);
    }

    public function refreshToken()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['refresh_token'])) {
            return Response::json(['error' => 'Refresh token is required'], 400);
        }

        $refreshToken = $data['refresh_token'];
        $auth = new Auth();
        $result = $auth->validateRefreshToken($refreshToken);

        if (!$result) {
            return Response::json(['error' => 'Invalid or expired refresh token'], 401);
        }

        // Generate new access token
        $userId = $result['user_id'];
        $accessToken = $auth->generateToken($userId);

        return Response::json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ]);
    }

    public function getProfile()
    {
        $userData = Auth::check();
        if (!$userData) return Response::unauthorized();

        $user = new User();
        $fullData = $user->getById($userData['id']);

        if (!$fullData) {
            return Response::json(['error' => 'User not found'], 404);
        }

        return Response::json([
            'id' => $fullData['id'],
            'username' => $fullData['username'],
            'email' => $fullData['email'],
            'created_at' => $fullData['created_at']
        ]);
    }

    public function updateProfile($body)
    {
        $userData = Auth::check();
        if (!$userData) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $newUsername = isset($data['username']) ? trim($data['username']) : null;
        $newEmail = isset($data['email']) ? trim($data['email']) : null;

        if (!$newUsername && !$newEmail) {
            return Response::json(['error' => 'Nothing to update'], 400);
        }

        try {
            $fields = [];
            $params = [];

            if ($newUsername) {
                $fields[] = "username = ?";
                $params[] = $newUsername;
            }
            if ($newEmail) {
                $fields[] = "email = ?";
                $params[] = $newEmail;
            }

            $params[] = $userData['id']; // For WHERE condition

            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $this->db->conn->prepare($sql);
            $stmt->execute($params);

            return Response::json(['message' => 'Profile updated successfully'], 200);

        } catch (PDOException $e) {
            return Response::json(['error' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    public function logout()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['refresh_token'])) {
            $auth = new Auth();
            $auth->revokeRefreshToken($data['refresh_token']);
        }

        return Response::json(['message' => 'Successfully logged out']);
    }
}
