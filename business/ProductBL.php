<?php

require_once __DIR__ . '/../dal/Product.php';

class ProductBL {
    private $productDAL;

    public function __construct() {
        $this->productDAL = new Product();
    }

    // Get all products
    public function getAllProducts() {
        return $this->productDAL->getAll();
    }

    // Get products by category
    public function getProductsByCategory($category) {
        if (empty($category)) {
            return $this->productDAL->getAll();
        }
        $allowed = ['Guitars', 'Keyboards', 'Drums'];
        if (!in_array($category, $allowed)) {
            return [];
        }
        return $this->productDAL->getByCategory($category);
    }

    // Get product by ID
    public function getProductById($id) {
        if (!is_numeric($id) || $id <= 0) {
            return null;
        }
        return $this->productDAL->getById(intval($id));
    }

    // Check stock
    public function isInStock($id) {
        if (!is_numeric($id) || $id <= 0) return false;
        return $this->productDAL->getStock(intval($id)) > 0;
    }

    // Add product - with server-side validation
    public function addProduct($name, $description, $price, $stock, $category, $image) {
        // Validate required fields
        if (empty($name) || empty($description) || empty($price) || empty($category)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        // Validate name
        if (strlen(trim($name)) < 2) {
            return ['success' => false, 'message' => 'Product name must be at least 2 characters.'];
        }

        // Validate price
        if (!is_numeric($price) || floatval($price) <= 0) {
            return ['success' => false, 'message' => 'Price must be a positive number.'];
        }

        // Validate stock
        if (!is_numeric($stock) || intval($stock) < 0) {
            return ['success' => false, 'message' => 'Stock must be a non-negative number.'];
        }

        // Validate category
        $allowed = ['Guitars', 'Keyboards', 'Drums'];
        if (!in_array($category, $allowed)) {
            return ['success' => false, 'message' => 'Invalid category.'];
        }

        // Sanitize
        $name        = htmlspecialchars(strip_tags(trim($name)));
        $description = htmlspecialchars(strip_tags(trim($description)));
        $image       = empty($image) ? 'default.jpg' : htmlspecialchars(trim($image));

        $result = $this->productDAL->add($name, $description, floatval($price), intval($stock), $category, $image);
        if ($result) {
            return ['success' => true, 'message' => 'Product added successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to add product.'];
    }

    // Update product - with server-side validation
    public function updateProduct($id, $name, $description, $price, $stock, $category, $image) {
        if (!is_numeric($id) || $id <= 0) {
            return ['success' => false, 'message' => 'Invalid product ID.'];
        }

        if (empty($name) || empty($price) || empty($category)) {
            return ['success' => false, 'message' => 'Required fields are missing.'];
        }

        if (!is_numeric($price) || floatval($price) <= 0) {
            return ['success' => false, 'message' => 'Price must be a positive number.'];
        }

        if (!is_numeric($stock) || intval($stock) < 0) {
            return ['success' => false, 'message' => 'Stock must be a non-negative number.'];
        }

        $allowed = ['Guitars', 'Keyboards', 'Drums'];
        if (!in_array($category, $allowed)) {
            return ['success' => false, 'message' => 'Invalid category.'];
        }

        $name        = htmlspecialchars(strip_tags(trim($name)));
        $description = htmlspecialchars(strip_tags(trim($description)));
        $image       = empty($image) ? 'default.jpg' : htmlspecialchars(trim($image));

        $result = $this->productDAL->update(intval($id), $name, $description, floatval($price), intval($stock), $category, $image);
        if ($result > 0) {
            return ['success' => true, 'message' => 'Product updated successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to update product.'];
    }

    // Delete product
    public function deleteProduct($id) {
        if (!is_numeric($id) || $id <= 0) {
            return ['success' => false, 'message' => 'Invalid product ID.'];
        }
        $result = $this->productDAL->delete(intval($id));
        if ($result > 0) {
            return ['success' => true, 'message' => 'Product deleted successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to delete product.'];
    }

    // Decrease stock
    public function decreaseStock($id, $quantity) {
        if (!is_numeric($id) || $id <= 0) return false;
        if (!is_numeric($quantity) || intval($quantity) <= 0) return false;
        return $this->productDAL->decreaseStock(intval($id), intval($quantity)) > 0;
    }
}
