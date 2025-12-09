<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/config.php';
require_once '../includes/Cart.php';

$cart = new Cart();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['product_id']) || !isset($data['quantity'])) {
                Utils::jsonResponse(['success' => false, 'message' => 'Product ID and quantity required'], 400);
            }
            
            if ($data['quantity'] < 1) {
                Utils::jsonResponse(['success' => false, 'message' => 'Quantity must be at least 1'], 400);
            }
            
            // Check if user is logged in
            if (Session::isLoggedIn()) {
                $result = $cart->addItem(Session::get('user_id'), $data['product_id'], $data['quantity']);
            } else {
                // Use session ID for guest users
                $session_id = session_id();
                $result = $cart->addItemToSession($session_id, $data['product_id'], $data['quantity']);
            }
            
            Utils::jsonResponse($result);
        }
        break;
        
    case 'get':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (Session::isLoggedIn()) {
                $cart_items = $cart->getCart(Session::get('user_id'));
                $total = $cart->getCartTotal(Session::get('user_id'));
                $count = $cart->getItemCount(Session::get('user_id'));
            } else {
                $session_id = session_id();
                $cart_items = $cart->getCart(null, $session_id);
                $total = $cart->getCartTotal(null, $session_id);
                $count = $cart->getItemCount(null, $session_id);
            }
            
            Utils::jsonResponse([
                'success' => true,
                'items' => $cart_items,
                'total' => $total,
                'count' => $count
            ]);
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            if (!Session::isLoggedIn()) {
                Utils::jsonResponse(['success' => false, 'message' => 'Login required to update cart'], 401);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['product_id']) || !isset($data['quantity'])) {
                Utils::jsonResponse(['success' => false, 'message' => 'Product ID and quantity required'], 400);
            }
            
            if ($data['quantity'] < 1) {
                Utils::jsonResponse(['success' => false, 'message' => 'Quantity must be at least 1'], 400);
            }
            
            $result = $cart->updateQuantity(Session::get('user_id'), $data['product_id'], $data['quantity']);
            
            if ($result) {
                Utils::jsonResponse(['success' => true, 'message' => 'Cart updated successfully']);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Failed to update cart'], 500);
            }
        }
        break;
        
    case 'remove':
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if (!Session::isLoggedIn()) {
                Utils::jsonResponse(['success' => false, 'message' => 'Login required to remove items'], 401);
            }
            
            $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
            
            if (!$product_id) {
                Utils::jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
            }
            
            $result = $cart->removeItem(Session::get('user_id'), $product_id);
            
            if ($result) {
                Utils::jsonResponse(['success' => true, 'message' => 'Item removed from cart']);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Failed to remove item'], 500);
            }
        }
        break;
        
    case 'clear':
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if (Session::isLoggedIn()) {
                $result = $cart->clear(Session::get('user_id'));
            } else {
                $session_id = session_id();
                $result = $cart->clear(null, $session_id);
            }
            
            if ($result) {
                Utils::jsonResponse(['success' => true, 'message' => 'Cart cleared successfully']);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Failed to clear cart'], 500);
            }
        }
        break;
        
    case 'count':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (Session::isLoggedIn()) {
                $count = $cart->getItemCount(Session::get('user_id'));
            } else {
                $session_id = session_id();
                $count = $cart->getItemCount(null, $session_id);
            }
            
            Utils::jsonResponse([
                'success' => true,
                'count' => $count
            ]);
        }
        break;
        
    default:
        Utils::jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
