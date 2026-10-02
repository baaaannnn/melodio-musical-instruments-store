<?php

require_once 'Database.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Create new order — returns order_id or false
    public function create($user_id, $phone) {
        $stmt = $this->db->prepare("CALL sp_CreateOrder(?, ?)");
        $stmt->bind_param('is', $user_id, $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $row ? $row['order_id'] : false;
    }

    // Get orders by user
    public function getByUser($user_id) {
        $stmt = $this->db->prepare("CALL sp_GetOrdersByUser(?)");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $rows;
    }

    // Get all orders (Admin)
    public function getAll() {
        $result = $this->db->query("CALL sp_GetAllOrders()");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $rows;
    }

    // Get order by ID
    public function getById($id) {
        $stmt = $this->db->prepare("CALL sp_GetOrderById(?)");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $order;
    }

    // Update order status — no validation here, BL handles it
    public function updateStatus($order_id, $status) {
        $stmt = $this->db->prepare("CALL sp_UpdateOrderStatus(?, ?)");
        $stmt->bind_param('is', $order_id, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return (int)($row['affected'] ?? 0);
    }
}
