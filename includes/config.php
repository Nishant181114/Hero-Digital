<?php
// Database configuration
class Database {
    private $host = 'localhost';
    private $db_name = 'hero_digital';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
        
        return $this->conn;
    }
}

// Site configuration
class Config {
    public static function get($key) {
        $settings = [
            'site_name' => 'Hero Digital',
            'site_email' => 'info@herodigital.com',
            'currency' => 'USD',
            'tax_rate' => 0.00,
            'shipping_cost' => 0.00,
            'downloads_expire_days' => 30,
            'base_url' => 'http://localhost/Hero%20Digital/',
            'upload_path' => 'uploads/',
            'max_file_size' => 50 * 1024 * 1024, // 50MB
        ];
        
        return isset($settings[$key]) ? $settings[$key] : null;
    }
}

// Session management
class Session {
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
    public static function destroy() {
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

// Utility functions
class Utils {
    public static function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }
    
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    public static function slugify($text) {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return $text;
    }
    
    public static function formatPrice($price) {
        return '$' . number_format($price, 2);
    }
    
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    public static function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize session
Session::init();
?>
