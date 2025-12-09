<?php
require_once '../includes/config.php';

class User {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function register($data) {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':username', $data['username']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email or username already exists'];
            }
            
            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, phone) 
                                      VALUES (:username, :email, :password_hash, :first_name, :last_name, :phone)");
            
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Registration successful'];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND is_active = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session
                Session::set('user_id', $user['id']);
                Session::set('user_email', $user['email']);
                Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
                Session::set('user_role', $user['role']);
                
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function logout() {
        Session::destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    public function getProfile($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, first_name, last_name, phone, role, created_at 
                                      FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function updateProfile($user_id, $data) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, 
                                      phone = :phone WHERE id = :id");
            
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':id', $user_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($current_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
            $stmt->bindParam(':password_hash', $new_password_hash);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Password updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Password update failed'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
