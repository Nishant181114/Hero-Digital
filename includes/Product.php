<?php
require_once '../includes/config.php';

class Product {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function getAll($limit = 10, $offset = 0, $category_id = null) {
        try {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.status = 'active'";
            
            if ($category_id) {
                $sql .= " AND p.category_id = :category_id";
            }
            
            $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            if ($category_id) {
                $stmt->bindParam(':category_id', $category_id);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    public function getFeatured($limit = 5) {
        try {
            $stmt = $this->db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                                      FROM products p 
                                      LEFT JOIN categories c ON p.category_id = c.id 
                                      WHERE p.status = 'active' AND p.is_featured = 1 
                                      ORDER BY p.created_at DESC LIMIT :limit");
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                                      FROM products p 
                                      LEFT JOIN categories c ON p.category_id = c.id 
                                      WHERE p.id = :id AND p.status = 'active'");
            
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function getBySlug($slug) {
        try {
            $stmt = $this->db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                                      FROM products p 
                                      LEFT JOIN categories c ON p.category_id = c.id 
                                      WHERE p.slug = :slug AND p.status = 'active'");
            
            $stmt->bindParam(':slug', $slug);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function search($query, $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.status = 'active' AND (p.name LIKE :query OR p.description LIKE :query OR p.short_description LIKE :query)
                   ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            
            $search_term = '%' . $query . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':query', $search_term);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    public function create($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO products (name, description, short_description, sku, price, 
                                      sale_price, category_id, image_url, gallery_images, file_url, file_type, 
                                      file_size, download_limit, stock_quantity, is_digital, is_featured, status) 
                                      VALUES (:name, :description, :short_description, :sku, :price, 
                                      :sale_price, :category_id, :image_url, :gallery_images, :file_url, :file_type, 
                                      :file_size, :download_limit, :stock_quantity, :is_digital, :is_featured, :status)");
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':short_description', $data['short_description']);
            $stmt->bindParam(':sku', $data['sku']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':sale_price', $data['sale_price']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':image_url', $data['image_url']);
            $stmt->bindParam(':gallery_images', $data['gallery_images']);
            $stmt->bindParam(':file_url', $data['file_url']);
            $stmt->bindParam(':file_type', $data['file_type']);
            $stmt->bindParam(':file_size', $data['file_size']);
            $stmt->bindParam(':download_limit', $data['download_limit']);
            $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
            $stmt->bindParam(':is_digital', $data['is_digital']);
            $stmt->bindParam(':is_featured', $data['is_featured']);
            $stmt->bindParam(':status', $data['status']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("UPDATE products SET name = :name, description = :description, 
                                      short_description = :short_description, sku = :sku, price = :price, 
                                      sale_price = :sale_price, category_id = :category_id, image_url = :image_url, 
                                      gallery_images = :gallery_images, file_url = :file_url, file_type = :file_type, 
                                      file_size = :file_size, download_limit = :download_limit, 
                                      stock_quantity = :stock_quantity, is_digital = :is_digital, 
                                      is_featured = :is_featured, status = :status 
                                      WHERE id = :id");
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':short_description', $data['short_description']);
            $stmt->bindParam(':sku', $data['sku']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':sale_price', $data['sale_price']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':image_url', $data['image_url']);
            $stmt->bindParam(':gallery_images', $data['gallery_images']);
            $stmt->bindParam(':file_url', $data['file_url']);
            $stmt->bindParam(':file_type', $data['file_type']);
            $stmt->bindParam(':file_size', $data['file_size']);
            $stmt->bindParam(':download_limit', $data['download_limit']);
            $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
            $stmt->bindParam(':is_digital', $data['is_digital']);
            $stmt->bindParam(':is_featured', $data['is_featured']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function getTotalCount($category_id = null) {
        try {
            $sql = "SELECT COUNT(*) FROM products WHERE status = 'active'";
            
            if ($category_id) {
                $sql .= " AND category_id = :category_id";
            }
            
            $stmt = $this->db->prepare($sql);
            
            if ($category_id) {
                $stmt->bindParam(':category_id', $category_id);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            return 0;
        }
    }
}
?>
