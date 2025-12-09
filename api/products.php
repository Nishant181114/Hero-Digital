<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/config.php';
require_once '../includes/Product.php';

$product = new Product();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            
            $products = $product->getAll($limit, $offset, $category_id);
            $total = $product->getTotalCount($category_id);
            
            Utils::jsonResponse([
                'success' => true,
                'products' => $products,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        break;
        
    case 'featured':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $products = $product->getFeatured($limit);
            
            Utils::jsonResponse([
                'success' => true,
                'products' => $products
            ]);
        }
        break;
        
    case 'detail':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$id) {
                Utils::jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
            }
            
            $product_data = $product->getById($id);
            
            if ($product_data) {
                Utils::jsonResponse([
                    'success' => true,
                    'product' => $product_data
                ]);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
            }
        }
        break;
        
    case 'search':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $query = isset($_GET['q']) ? trim($_GET['q']) : '';
            
            if (empty($query)) {
                Utils::jsonResponse(['success' => false, 'message' => 'Search query required'], 400);
            }
            
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $products = $product->search($query, $limit, $offset);
            
            Utils::jsonResponse([
                'success' => true,
                'products' => $products,
                'query' => $query,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        break;
        
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if user is admin
            if (!Session::isAdmin()) {
                Utils::jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['name']) || !isset($data['price']) || !isset($data['sku'])) {
                Utils::jsonResponse(['success' => false, 'message' => 'Name, price, and SKU are required'], 400);
            }
            
            // Set defaults
            $data['status'] = $data['status'] ?? 'active';
            $data['is_digital'] = $data['is_digital'] ?? true;
            $data['is_featured'] = $data['is_featured'] ?? false;
            $data['download_limit'] = $data['download_limit'] ?? 5;
            $data['stock_quantity'] = $data['stock_quantity'] ?? 0;
            $data['gallery_images'] = isset($data['gallery_images']) ? json_encode($data['gallery_images']) : null;
            
            $result = $product->create($data);
            
            if ($result) {
                Utils::jsonResponse(['success' => true, 'message' => 'Product created successfully']);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Failed to create product'], 500);
            }
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            // Check if user is admin
            if (!Session::isAdmin()) {
                Utils::jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
            }
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$id) {
                Utils::jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Handle gallery images
            if (isset($data['gallery_images'])) {
                $data['gallery_images'] = json_encode($data['gallery_images']);
            }
            
            $result = $product->update($id, $data);
            
            if ($result) {
                Utils::jsonResponse(['success' => true, 'message' => 'Product updated successfully']);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Failed to update product'], 500);
            }
        }
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // Check if user is admin
            if (!Session::isAdmin()) {
                Utils::jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
            }
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$id) {
                Utils::jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
            }
            
            $result = $product->delete($id);
            
            if ($result) {
                Utils::jsonResponse(['success' => true, 'message' => 'Product deleted successfully']);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Failed to delete product'], 500);
            }
        }
        break;
        
    default:
        Utils::jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
