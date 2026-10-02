<?php

require_once __DIR__ . '/../dal/Order.php';
require_once __DIR__ . '/../dal/OrderItem.php';
require_once __DIR__ . '/../dal/Product.php';
require_once __DIR__ . '/../dal/User.php';
require_once __DIR__ . '/../dal/Database.php';

class OrderBL {
    private $orderDAL;
    private $orderItemDAL;
    private $productDAL;
    private $userDAL;

    public function __construct() {
        $this->orderDAL     = new Order();
        $this->orderItemDAL = new OrderItem();
        $this->productDAL   = new Product();
        $this->userDAL      = new User();
    }

    // Place order — all validation in BL
    public function placeOrder($user_id, $phone, $cart) {
        $db = Database::connect();

        if (!is_numeric($user_id) || $user_id <= 0) {
            return ['success' => false, 'message' => 'Invalid user. Please login again.'];
        }
        $user = $this->userDAL->getById(intval($user_id));
        if (!$user) {
            return ['success' => false, 'message' => 'User not found. Please login again.'];
        }
        if (empty($phone)) {
            return ['success' => false, 'message' => 'Phone number is required.'];
        }
        if (strlen(preg_replace('/\D/', '', $phone)) < 7) {
            return ['success' => false, 'message' => 'Invalid phone number.'];
        }
        if (empty($cart)) {
            return ['success' => false, 'message' => 'Your cart is empty.'];
        }

        // Validate stock for all items in BL
        foreach ($cart as $item) {
            if (!is_numeric($item['product_id']) || !is_numeric($item['quantity'])
                || intval($item['product_id']) <= 0 || intval($item['quantity']) <= 0) {
                return ['success' => false, 'message' => 'Invalid cart data.'];
            }
            $product = $this->productDAL->getById(intval($item['product_id']));
            if (!$product) {
                return ['success' => false, 'message' => 'One or more products not found.'];
            }
            if ($product['stock'] < intval($item['quantity'])) {
                return ['success' => false, 'message' => '"' . $product['name'] . '" is out of stock.'];
            }
        }

        $db->begin_transaction();
        try {
            $order_id = $this->orderDAL->create(intval($user_id), htmlspecialchars(trim($phone)));
            if (!$order_id) throw new Exception('Failed to create order.');

            foreach ($cart as $item) {
                $product = $this->productDAL->getById(intval($item['product_id']));
                $added = $this->orderItemDAL->add($order_id, intval($item['product_id']), intval($item['quantity']), floatval($product['price']));
                if (!$added) throw new Exception('Failed to add order item.');
                $decreased = $this->productDAL->decreaseStock(intval($item['product_id']), intval($item['quantity']));
                if (!$decreased) throw new Exception('Stock update failed for: ' . $product['name']);
            }

            $db->commit();
            return ['success' => true, 'message' => 'Order placed successfully!', 'order_id' => $order_id];
        } catch (Exception $e) {
            $db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Get orders by user
    public function getOrdersByUser($user_id) {
        if (!is_numeric($user_id) || $user_id <= 0) return [];
        return $this->orderDAL->getByUser(intval($user_id));
    }

    // Get all orders (Admin)
    public function getAllOrders() {
        return $this->orderDAL->getAll();
    }

    // Get order by ID
    public function getOrderById($id) {
        if (!is_numeric($id) || $id <= 0) return null;
        return $this->orderDAL->getById(intval($id));
    }

    // Get order items
    public function getOrderItems($order_id) {
        if (!is_numeric($order_id) || $order_id <= 0) return [];
        return $this->orderItemDAL->getByOrder(intval($order_id));
    }

    // Get order total
    public function getOrderTotal($order_id) {
        if (!is_numeric($order_id) || $order_id <= 0) return 0;
        return $this->orderItemDAL->getOrderTotal(intval($order_id));
    }

    // Update order status — all validation in BL, DAL just executes
    public function updateStatus($order_id, $status, $role) {
        if (!is_numeric($order_id) || $order_id <= 0) {
            return ['success' => false, 'message' => 'Invalid order ID.'];
        }

        if (!in_array($role, ['admin', 'delivery'], true)) {
            return ['success' => false, 'message' => 'Unauthorized status update.'];
        }

        // Validate status in BL
        $allowed = ['pending', 'picked_up', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        // Role-based restriction in BL
        if ($role === 'delivery' && !in_array($status, ['picked_up', 'delivered', 'cancelled'])) {
            return ['success' => false, 'message' => 'Unauthorized status update.'];
        }

        $result = $this->orderDAL->updateStatus(intval($order_id), $status);
        if ($result > 0) {
            return ['success' => true, 'message' => 'Status updated successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to update status.'];
    }
}
