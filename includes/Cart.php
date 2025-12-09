<?php
require_once '../includes/config.php';

class Cart {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function addItem($user_id, $product_id, $quantity = 1) {
        try {
            // Check if product exists and is active
            $stmt = $this->db->prepare("SELECT id, name, price, stock_quantity FROM products 
                                      WHERE id = :product_id AND status = 'active'");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                return ['success' => false, 'message' => 'Product not found'];
            }
            
            if ($product['stock_quantity'] < $quantity) {
                return ['success' => false, 'message' => 'Insufficient stock'];
            }
            
            // Check if item already in cart
            $stmt = $this->db->prepare("SELECT id, quantity FROM cart 
                                      WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_item) {
                // Update quantity
                $new_quantity = $existing_item['quantity'] + $quantity;
                
                if ($product['stock_quantity'] < $new_quantity) {
                    return ['success' => false, 'message' => 'Insufficient stock'];
                }
                
                $stmt = $this->db->prepare("UPDATE cart SET quantity = :quantity 
                                          WHERE user_id = :user_id AND product_id = :product_id");
                $stmt->bindParam(':quantity', $new_quantity);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':product_id', $product_id);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Cart updated successfully'];
                }
            } else {
                // Add new item
                $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) 
                                          VALUES (:user_id, :product_id, :quantity)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':quantity', $quantity);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Item added to cart'];
                }
            }
            
            return ['success' => false, 'message' => 'Failed to add item to cart'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function addItemToSession($session_id, $product_id, $quantity = 1) {
        try {
            // Check if product exists and is active
            $stmt = $this->db->prepare("SELECT id, name, price, stock_quantity FROM products 
                                      WHERE id = :product_id AND status = 'active'");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                return ['success' => false, 'message' => 'Product not found'];
            }
            
            if ($product['stock_quantity'] < $quantity) {
                return ['success' => false, 'message' => 'Insufficient stock'];
            }
            
            // Check if item already in cart
            $stmt = $this->db->prepare("SELECT id, quantity FROM cart 
                                      WHERE session_id = :session_id AND product_id = :product_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_item) {
                // Update quantity
                $new_quantity = $existing_item['quantity'] + $quantity;
                
                if ($product['stock_quantity'] < $new_quantity) {
                    return ['success' => false, 'message' => 'Insufficient stock'];
                }
                
                $stmt = $this->db->prepare("UPDATE cart SET quantity = :quantity 
                                          WHERE session_id = :session_id AND product_id = :product_id");
                $stmt->bindParam(':quantity', $new_quantity);
                $stmt->bindParam(':session_id', $session_id);
                $stmt->bindParam(':product_id', $product_id);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Cart updated successfully'];
                }
            } else {
                // Add new item
                $stmt = $this->db->prepare("INSERT INTO cart (session_id, product_id, quantity) 
                                          VALUES (:session_id, :product_id, :quantity)");
                $stmt->bindParam(':session_id', $session_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':quantity', $quantity);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Item added to cart'];
                }
            }
            
            return ['success' => false, 'message' => 'Failed to add item to cart'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getCart($user_id = null, $session_id = null) {
        try {
            if ($user_id) {
                $stmt = $this->db->prepare("SELECT c.*, p.name, p.price, p.image_url, p.short_description 
                                          FROM cart c 
                                          JOIN products p ON c.product_id = p.id 
                                          WHERE c.user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id);
            } else {
                $stmt = $this->db->prepare("SELECT c.*, p.name, p.price, p.image_url, p.short_description 
                                          FROM cart c 
                                          JOIN products p ON c.product_id = p.id 
                                          WHERE c.session_id = :session_id");
                $stmt->bindParam(':session_id', $session_id);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    public function updateQuantity($user_id, $product_id, $quantity) {
        try {
            // Check stock
            $stmt = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = :product_id");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product['stock_quantity'] < $quantity) {
                return ['success' => false, 'message' => 'Insufficient stock'];
            }
            
            $stmt = $this->db->prepare("UPDATE cart SET quantity = :quantity 
                                      WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':product_id', $product_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function removeItem($user_id, $product_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart 
                                      WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':product_id', $product_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function clear($user_id = null, $session_id = null) {
        try {
            if ($user_id) {
                $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id);
            } else {
                $stmt = $this->db->prepare("DELETE FROM cart WHERE session_id = :session_id");
                $stmt->bindParam(':session_id', $session_id);
            }
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function getCartTotal($user_id = null, $session_id = null) {
        try {
            if ($user_id) {
                $stmt = $this->db->prepare("SELECT SUM(c.quantity * p.price) as total 
                                          FROM cart c 
                                          JOIN products p ON c.product_id = p.id 
                                          WHERE c.user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id);
            } else {
                $stmt = $this->db->prepare("SELECT SUM(c.quantity * p.price) as total 
                                          FROM cart c 
                                          JOIN products p ON c.product_id = p.id 
                                          WHERE c.session_id = :session_id");
                $stmt->bindParam(':session_id', $session_id);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?: 0;
        } catch(PDOException $e) {
            return 0;
        }
    }
    
    public function getItemCount($user_id = null, $session_id = null) {
        try {
            if ($user_id) {
                $stmt = $this->db->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id);
            } else {
                $stmt = $this->db->prepare("SELECT SUM(quantity) as count FROM cart WHERE session_id = :session_id");
                $stmt->bindParam(':session_id', $session_id);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?: 0;
        } catch(PDOException $e) {
            return 0;
        }
    }
}
?>
