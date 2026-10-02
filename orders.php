<?php
require_once __DIR__ . '/business/OrderBL.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /melodio/login.php');
    exit;
}

$role         = $_SESSION['role'];
$userId       = $_SESSION['user_id'];
$orderBL = new OrderBL();
$message      = '';
$msgType      = '';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId   = intval($_POST['order_id']);
    // Delivery uses button value directly; Admin uses select
    $rawVal = $_POST['update_status'] ?? '';
    $newStatus = (!empty($rawVal) && $rawVal !== '1') ? trim($rawVal) : trim($_POST['status'] ?? '');
    $blResult = $orderBL->updateStatus($orderId, $newStatus, $role);
    $message  = $blResult['message'];
    $msgType  = $blResult['success'] ? 'success' : 'error';
}

// Fetch orders
if ($role === 'admin')         $orders = $orderBL->getAllOrders();
elseif ($role === 'delivery')  $orders = $orderBL->getAllOrders();
else                           $orders = $orderBL->getOrdersByUser($userId);

$pageTitle = $role === 'delivery' ? 'My Deliveries' : 'Orders';
include 'header.php';
?>

<main>
<div style="max-width:900px; margin:30px auto; padding:0 30px;">

    <?php if (isset($_GET['success'])): ?>
        <div style="background:#d5f5e3; color:#1e8449; border:1px solid #a9dfbf; border-radius:8px; padding:12px 16px; margin-bottom:20px;">
            ✅ Your order has been placed successfully!
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div style="background:<?= $msgType==='success'?'#d5f5e3':'#fadbd8' ?>; color:<?= $msgType==='success'?'#1e8449':'#922b21' ?>; border-radius:8px; padding:12px 16px; margin-bottom:20px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <h2 style="font-size:20px; font-weight:bold; margin-bottom:20px;">
        <?= $role==='delivery' ? 'Delivery Orders' : ($role==='admin' ? 'All Orders' : 'My Orders') ?>
    </h2>

    <?php if (empty($orders)): ?>
        <div style="text-align:center; padding:60px; background:#fff; border-radius:12px;">
            <p style="color:#999; margin-bottom:16px;">No orders found.</p>
            <?php if ($role === 'customer'): ?>
                <a href="/melodio/products.php" style="background:#c7a17a; color:#fff; padding:10px 24px; border-radius:8px; text-decoration:none;">Start Shopping</a>
            <?php endif; ?>
        </div>

    <?php elseif ($role === 'delivery'): ?>
        <!-- Delivery View -->
        <?php if (isset($_GET['id'])): ?>
            <?php
            $selOrder = null;
            foreach ($orders as $o) { if ($o['id'] == intval($_GET['id'])) { $selOrder = $o; break; } }
            ?>
            <?php if ($selOrder): ?>
            <?php $total = $orderBL->getOrderTotal($selOrder['id']); ?>

            <a href="/melodio/orders.php" style="color:#c7a17a; font-size:14px; display:inline-block; margin-bottom:16px;">← Back to Orders</a>

            <div style="background:#f0ebe3; border-radius:12px; overflow:hidden; max-width:500px; margin:0 auto;">
                <div style="background:#d9c9b5; padding:14px 20px;">
                    <span style="font-size:15px; font-weight:600;">Order &nbsp; #<?= $selOrder['id'] ?></span>
                </div>
                <div style="padding:20px;">
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:16px;">
                        <span style="font-size:32px;">🎸</span>
                        <div>
                            <p style="font-weight:600; font-size:15px;"><?= htmlspecialchars($selOrder['full_name']) ?></p>
                            <p style="color:#666; font-size:14px;">+966 <?= htmlspecialchars($selOrder['phone']) ?></p>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; border:1px solid #ccc; border-radius:8px; overflow:hidden; width:fit-content; margin-bottom:16px; background:#fff;">
                        <span style="padding:8px 12px; border-right:1px solid #ccc; font-size:16px;">📞</span>
                        <span style="padding:8px 16px; font-size:14px; color:#888;">Contact Customer</span>
                    </div>
                    <hr style="border:none; border-top:1px solid #c7a17a; margin-bottom:16px;">
                    <p style="font-size:15px; margin-bottom:16px;">Total: <strong>$<?= number_format($total, 2) ?></strong></p>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= $selOrder['id'] ?>">
                        <button type="submit" name="update_status" value="picked_up"
                                style="width:100%; background:#4da6ff; color:#fff; border:none; border-radius:20px; padding:12px; font-size:14px; cursor:pointer; margin-bottom:10px;">
                            Mark as Picked Up
                        </button>
                        <div style="display:flex; gap:10px;">
                            <button type="submit" name="update_status" value="delivered"
                                    style="flex:1; background:#5a8a5a; color:#fff; border:none; border-radius:20px; padding:12px; font-size:14px; cursor:pointer;">
                                Mark as Delivered
                            </button>
                            <button type="submit" name="update_status" value="cancelled"
                                    style="flex:1; background:#c0392b; color:#fff; border:none; border-radius:20px; padding:12px; font-size:14px; cursor:pointer;">
                                Cancel Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <?php $hasOrders = false; foreach ($orders as $order):
                if (in_array(strtolower($order['status']), ['delivered', 'cancelled'])) continue;
                $hasOrders = true;
                $total = $orderBL->getOrderTotal($order['id']);
            ?>
            <a href="/melodio/orders.php?id=<?= $order['id'] ?>" style="text-decoration:none; color:inherit; display:block;">
                <div style="background:#fdfaf6; border-radius:12px; padding:16px 20px; margin-bottom:12px; box-shadow:0 1px 4px rgba(0,0,0,0.07); display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="font-weight:bold; font-size:15px; margin-bottom:4px;">Order #<?= $order['id'] ?></p>
                        <p style="font-size:13px; color:#666;">👤 <?= htmlspecialchars($order['full_name']) ?></p>
                        <p style="font-size:13px; color:#666;">💰 $<?= number_format($total, 2) ?></p>
                    </div>
                    <span style="background:#4da6ff; color:#fff; padding:4px 14px; border-radius:8px; font-size:12px; font-weight:bold;">
                        <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (!$hasOrders): ?>
                <div style="text-align:center; padding:40px; color:#999;">No active delivery orders.</div>
            <?php endif; ?>
        <?php endif; ?>

    <?php else: ?>
        <!-- Customer / Admin View -->
        <?php foreach ($orders as $order):
            $items = $orderBL->getOrderItems($order['id']);
            $total = $orderBL->getOrderTotal($order['id']);
        ?>
        <div style="background:#fdfaf6; border-radius:12px; padding:20px; margin-bottom:16px; box-shadow:0 1px 4px rgba(0,0,0,0.07);">

            <!-- Order Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <div>
                    <span style="font-weight:bold; font-size:15px;">Order #<?= $order['id'] ?></span>
                    <?php if ($role === 'admin'): ?>
                        <span style="color:#999; font-size:13px; margin-left:10px;">— <?= htmlspecialchars($order['full_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:<?= $order['status']==='pending'?'#4da6ff':($order['status']==='picked_up'?'#f39c12':($order['status']==='delivered'?'#4CAF50':'#e74c3c')) ?>; color:#fff; padding:4px 12px; border-radius:8px; font-size:12px; font-weight:bold;">
                        <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                    </span>
                    <span style="color:#999; font-size:13px;"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                </div>
            </div>

            <!-- Order Items -->
            <?php foreach ($items as $item): ?>
            <div style="display:flex; align-items:center; gap:14px; background:#f0ebe3; border-radius:8px; padding:10px 14px; margin-bottom:8px;">
                <img src="/melodio/assets/images/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>"
                     alt="<?= htmlspecialchars($item['name']) ?>"
                     style="width:50px; height:50px; object-fit:contain; background:#fff; border-radius:6px; padding:4px;">
                <div style="flex:1;">
                    <p style="font-size:14px; font-weight:600;"><?= htmlspecialchars($item['name']) ?></p>
                    <p style="font-size:12px; color:#999;">Qty: <?= $item['quantity'] ?></p>
                </div>
                <p style="font-weight:bold; font-size:14px;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
            </div>
            <?php endforeach; ?>

            <!-- Order Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:12px; border-top:1px solid #e8e2d9;">
                <span style="font-size:13px; color:#999;"><?= count($items) ?> item(s)</span>
                <span style="font-weight:bold; font-size:15px;">Total: $<?= number_format($total, 2) ?></span>
            </div>

            <!-- Admin status update -->
            <?php if ($role === 'admin'): ?>
            <form method="POST" style="display:flex; gap:8px; align-items:center; margin-top:12px;">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <select name="status" style="padding:6px 10px; border:1px solid #ddd; border-radius:6px; font-size:13px;">
                    <option value="pending"   <?= $order['status']==='pending'   ?'selected':''?>>Pending</option>
                    <option value="picked_up" <?= $order['status']==='picked_up' ?'selected':''?>>Picked Up</option>
                    <option value="delivered" <?= $order['status']==='delivered' ?'selected':''?>>Delivered</option>
                    <option value="cancelled" <?= $order['status']==='cancelled' ?'selected':''?>>Cancelled</option>
                </select>
                <button type="submit" name="update_status" style="background:#333; color:#fff; border:none; padding:6px 16px; border-radius:6px; cursor:pointer; font-size:13px;">Save</button>
            </form>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
</main>

<?php include 'footer.php'; ?>
