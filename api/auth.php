<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/config.php';
require_once '../includes/User.php';

$user = new User();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
                Utils::jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            }
            
            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Utils::jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
            }
            
            // Validate password
            if (strlen($data['password']) < 6) {
                Utils::jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
            }
            
            $result = $user->register($data);
            Utils::jsonResponse($result);
        }
        break;
        
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['email']) || !isset($data['password'])) {
                Utils::jsonResponse(['success' => false, 'message' => 'Email and password required'], 400);
            }
            
            $result = $user->login($data['email'], $data['password']);
            Utils::jsonResponse($result);
        }
        break;
        
    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $user->logout();
            Utils::jsonResponse($result);
        }
        break;
        
    case 'profile':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!Session::isLoggedIn()) {
                Utils::jsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
            }
            
            $profile = $user->getProfile(Session::get('user_id'));
            if ($profile) {
                Utils::jsonResponse(['success' => true, 'profile' => $profile]);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Profile not found'], 404);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            if (!Session::isLoggedIn()) {
                Utils::jsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $user->updateProfile(Session::get('user_id'), $data);
            
            if ($result) {
                Utils::jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Failed to update profile'], 500);
            }
        }
        break;
        
    case 'change-password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::isLoggedIn()) {
                Utils::jsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['current_password']) || !isset($data['new_password'])) {
                Utils::jsonResponse(['success' => false, 'message' => 'Current and new passwords required'], 400);
            }
            
            if (strlen($data['new_password']) < 6) {
                Utils::jsonResponse(['success' => false, 'message' => 'New password must be at least 6 characters'], 400);
            }
            
            $result = $user->changePassword(Session::get('user_id'), $data['current_password'], $data['new_password']);
            Utils::jsonResponse($result);
        }
        break;
        
    case 'check-auth':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (Session::isLoggedIn()) {
                Utils::jsonResponse([
                    'success' => true, 
                    'logged_in' => true,
                    'user' => [
                        'id' => Session::get('user_id'),
                        'email' => Session::get('user_email'),
                        'name' => Session::get('user_name'),
                        'role' => Session::get('user_role')
                    ]
                ]);
            } else {
                Utils::jsonResponse(['success' => true, 'logged_in' => false]);
            }
        }
        break;
        
    default:
        Utils::jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
