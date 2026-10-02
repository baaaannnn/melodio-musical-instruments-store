<?php

require_once 'Database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Get all products
    public function getAll() {
        $result = $this->db->query("CALL sp_GetAllProducts()");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $rows;
    }

    // Get products by category
    public function getByCategory($category) {
        $stmt = $this->db->prepare("CALL sp_GetProductsByCategory(?)");
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $rows;
    }

    // Get product by ID
    public function getById($id) {
        $stmt = $this->db->prepare("CALL sp_GetProductById(?)");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $product;
    }

    // Return raw stock count — BL decides if it's "in stock"
    public function getStock($id) {
        $stmt = $this->db->prepare("CALL sp_CheckStock(?)");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $row ? (int)$row['stock'] : 0;
    }


    // Decrease stock after order
    public function decreaseStock($id, $quantity) {
        $stmt = $this->db->prepare("CALL sp_DecreaseStock(?, ?)");
        $stmt->bind_param('ii', $id, $quantity);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return (int)($row['affected'] ?? 0);
    }
}
