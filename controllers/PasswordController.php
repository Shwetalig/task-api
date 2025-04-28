<?php
require_once(__DIR__ . '/../core/Database.php');
require_once(__DIR__ . '/../core/Response.php');
require_once(__DIR__ . '/../models/User.php');

class PasswordController {
    public static function forgotPassword($data) {
        $username = $data['username'] ?? null;
        if (!$username) {
            return Response::json(['error' => 'Username is required'], 400);
        }

        $user = new User();
        $userData = $user->getUserByUsername($username);

        if (!$userData) {
            return Response::json(['error' => 'User not found'], 404);
        }

        $token = bin2hex(random_bytes(16)); // 32 characters random token

        $db = new Database();
        $stmt = $db->conn->prepare("INSERT INTO password_resets (username, token) VALUES (?, ?)");
        $stmt->execute([$username, $token]);

        return Response::json([
            'message' => 'Reset token generated',
            'token' => $token
        ]);
    }

    public static function resetPassword($data) {
        $username = $data['username'] ?? null;
        $token = $data['token'] ?? null;
        $newPassword = $data['new_password'] ?? null;

        if (!$username || !$token || !$newPassword) {
            return Response::json(['error' => 'Username, token, and new password are required'], 400);
        }

        $db = new Database();
       $stmt = $db->conn->prepare("SELECT * FROM password_resets WHERE username = ? AND token = ?");
        $stmt->execute([$username, $token]);
        $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resetRequest) {
            return Response::json(['error' => 'Invalid token or username'], 400);
        }

        // Update password
        $user = new User();
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $user->updatePassword($username, $hashed);

        // Delete the reset token
        $stmt = $db->conn->prepare("DELETE FROM password_resets WHERE username = ?");
        $stmt->execute([$username]);

        return Response::json(['message' => 'Password reset successful']);
    }
}
