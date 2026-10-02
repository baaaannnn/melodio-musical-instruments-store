<?php
require_once __DIR__ . '/business/ProductBL.php';
require_once __DIR__ . '/business/OrderBL.php';
require_once __DIR__ . '/business/UserBL.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /melodio/login.php');
    exit;
}

$productBL = new ProductBL();
$orderBL   = new OrderBL();
$userBL    = new UserBL();
$message = ''; $msgType = '';

// Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $result = $productBL->addProduct(
        trim($_POST['name'] ?? ''), trim($_POST['description'] ?? ''),
        floatval($_POST['price'] ?? 0), intval($_POST['stock'] ?? 0),
        trim($_POST['category'] ?? ''), trim($_POST['image'] ?? 'default.jpg')
    );
    $message = $result['message'];
    $msgType = $result['success'] ? 'success' : 'error';
}

// Delete Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $blResult = $productBL->deleteProduct($_POST['product_id']);
    $message  = $blResult['message'];
    $msgType  = $blResult['success'] ? 'success' : 'error';
}

// Update Order Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $blResult = $orderBL->updateStatus($_POST['order_id'], $_POST['status'], $_SESSION['role']);
    $message  = $blResult['message'];
    $msgType  = $blResult['success'] ? 'success' : 'error';
}

// Update Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $blResult = $productBL->updateProduct(
        $_POST['product_id'], trim($_POST['name'] ?? ''),
        trim($_POST['description'] ?? ''), $_POST['price'] ?? 0,
        $_POST['stock'] ?? 0, trim($_POST['category'] ?? ''),
        trim($_POST['image'] ?? 'default.jpg')
    );
    $message = $blResult['message'];
    $msgType = $blResult['success'] ? 'success' : 'error';
}

// Stats
$allOrders    = $orderBL->getAllOrders();
$allProducts  = $productBL->getAllProducts();
$allUsers     = $userBL->getAllUsers();
$totalOrders  = count($allOrders);
$pendingOrders = count(array_filter($allOrders, fn($o) => strtolower($o['status']) === 'pending'));
$allUsers = $userBL->getAllUsers();
$totalCustomers = count(array_filter($allUsers, fn($u) => $u['role'] === 'customer'));

$totalRevenue = 0;
foreach ($allOrders as $o) { if ($o['status'] === 'delivered') { $totalRevenue += $orderBL->getOrderTotal($o['id']); } }

$categories = ['Guitars', 'Keyboards', 'Drums'];
$pageTitle  = 'Admin Dashboard';
include 'header.php';
?>

<main>
<div style="max-width:1100px; margin:30px auto; padding:0 30px;">

    <?php if ($message): ?>
        <div style="background:<?= $msgType==='success'?'#d5f5e3':'#fadbd8' ?>; color:<?= $msgType==='success'?'#1e8449':'#922b21' ?>; border-radius:8px; padding:12px 16px; margin-bottom:20px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Stats Bar -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); background:#e8e0d4; border-radius:12px; overflow:hidden; margin-bottom:24px; text-align:center;">
        <?php
        $stats = [
            ['Total Orders', number_format($totalOrders)],
            ['Pending Orders', number_format($pendingOrders)],
            ['Total Customer', number_format($totalCustomers)],
            ['Total Revenue', '$' . number_format($totalRevenue)],
        ];
        foreach ($stats as $i => $s): ?>
        <div style="padding:16px 10px; <?= $i < 3 ? 'border-right:1px solid #d0c8bc;' : '' ?>">
            <p style="font-size:13px; color:#777; margin-bottom:4px;"><?= $s[0] ?></p>
            <p style="font-size:22px; font-weight:bold; color:#333;"><?= $s[1] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Layout -->
    <div style="display:flex; gap:20px; align-items:flex-start;">

        <!-- Right: Manage Orders -->
        <div style="flex:1; background:#ede5da; border-radius:12px; padding:20px;">
            <h3 style="font-size:15px; font-weight:bold; margin-bottom:16px;">Manage Orders</h3>

            <!-- Orders Table Header -->
            <div style="display:grid; grid-template-columns:100px 1fr 80px 120px; gap:10px; padding:8px 12px; font-size:13px; color:#666; font-weight:600;">
                <span>Order ID</span>
                <span>Customer</span>
                <span>No.Items</span>
                <span>Status</span>
            </div>

            <?php foreach ($allOrders as $order):
                $itemCount = count($orderBL->getOrderItems($order['id']));
                $status = strtolower($order['status']);
                $statusColor = match($status) {
                    'pending'   => '#4da6ff',
                    'picked_up' => '#f39c12',
                    'delivered' => '#4CAF50',
                    'cancelled' => '#e74c3c',
                    default     => '#999'
                };
            ?>
            <div style="display:grid; grid-template-columns:100px 1fr 80px 120px; gap:10px; align-items:center; background:#d9c9b5; border-radius:8px; padding:10px 12px; margin-bottom:6px; font-size:14px;">
                <span style="font-weight:600;">#<?= $order['id'] ?></span>
                <span><?= htmlspecialchars($order['full_name']) ?></span>
                <span style="text-align:center;"><?= $itemCount ?></span>
                <span>
                    <form method="POST" style="display:flex; gap:6px; align-items:center;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="update_status" value="1">
                        <span style="background:<?= $statusColor ?>; color:#fff; padding:4px 12px; border-radius:6px; font-size:12px; font-weight:bold; cursor:pointer;"
                              onclick="toggleStatusSelect(<?= $order['id'] ?>)">
                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                        </span>
                        <select id="sel_<?= $order['id'] ?>" name="status"
                                style="display:none; padding:4px; border:1px solid #ccc; border-radius:6px; font-size:12px;"
                                onchange="this.form.submit()">
                            <option value="pending"   <?= $status==='pending'   ?'selected':''?>>Pending</option>
                            <option value="picked_up" <?= $status==='picked_up' ?'selected':''?>>Picked Up</option>
                            <option value="delivered" <?= $status==='delivered' ?'selected':''?>>Delivered</option>
                            <option value="cancelled" <?= $status==='cancelled' ?'selected':''?>>Cancelled</option>
                        </select>
                    </form>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</main>

<!-- Delete List Modal -->
<div class="modal-overlay" id="deleteListModal">
    <div class="modal">
        <h3>Select Product to Delete</h3>
        <div style="max-height:300px; overflow-y:auto;">
            <?php foreach ($allProducts as $p): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; border-bottom:1px solid #eee;">
                <span><?= htmlspecialchars($p['name']) ?></span>
                <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars($p['name']) ?>?')">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <button type="submit" name="delete_product"
                            style="background:#e74c3c; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:13px;">Delete</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" onclick="toggleModal('deleteListModal')">Close</button>
        </div>
    </div>
</div>

<script>
function toggleModal(id) {
    document.getElementById(id).classList.toggle('active');
}
function openEditModal(p) {
    document.getElementById('edit_id').value          = p.id;
    document.getElementById('edit_name').value        = p.name;
    document.getElementById('edit_description').value = p.description;
    document.getElementById('edit_price').value       = p.price;
    document.getElementById('edit_stock').value       = p.stock;
    document.getElementById('edit_category').value    = p.category;
    document.getElementById('edit_image').value       = p.image;
    toggleModal('editModal');
}
function toggleStatusSelect(id) {
    const sel = document.getElementById('sel_' + id);
    sel.style.display = sel.style.display === 'none' ? 'block' : 'none';
}
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});
</script>

<?php include 'footer.php'; ?>
