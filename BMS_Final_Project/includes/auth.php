<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit();
        }
    }

    public function requireRole($role) {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== $role) {
            header('Location: ../unauthorized.php');
            exit();
        }
    }

    public function resetPassword($email) {
        try {
            $query = "SELECT id FROM users WHERE email = :email AND status = 'active'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token (you might want to create a separate table for this)
                // For now, we'll just return true
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize auth
$auth = new Auth($db);
?> 