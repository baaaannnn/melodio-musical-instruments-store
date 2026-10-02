<?php

require_once 'Database.php';

class OrderItem {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Add item to order
    public function add($order_id, $product_id, $quantity, $price) {
        $stmt = $this->db->prepare("CALL sp_AddOrderItem(?, ?, ?, ?)");
        $stmt->bind_param('iiid', $order_id, $product_id, $quantity, $price);
        $result = $stmt->execute();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $result;
    }

    // Get all items for an order
    public function getByOrder($order_id) {
        $stmt = $this->db->prepare("CALL sp_GetOrderItems(?)");
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $rows;
    }

    // Get order total price
    public function getOrderTotal($order_id) {
        $stmt = $this->db->prepare("CALL sp_GetOrderTotal(?)");
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $row['total'] ?? 0;
    }
}
