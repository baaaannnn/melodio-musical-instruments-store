<?php
require_once __DIR__ . '/business/OrderBL.php';
require_once __DIR__ . '/business/ProductBL.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: /melodio/login.php'); exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$productBL = new ProductBL();
$orderBL   = new OrderBL();
$message   = ''; $msgType = '';

// Remove item
if (isset($_POST['remove_item'])) {
    $removeId = intval($_POST['remove_item']);
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], fn($i) => $i['product_id'] != $removeId));
    header('Location: /melodio/cart.php'); exit;
}

// Update quantity
if (isset($_POST['update_qty'])) {
    $updateId = intval($_POST['update_qty']);
    $newQty   = intval($_POST['qty_' . $updateId] ?? 1);
    if ($newQty < 1) $newQty = 1;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $updateId) { $item['quantity'] = $newQty; break; }
    }
    unset($item);
    header('Location: /melodio/cart.php'); exit;
}

// Place Order — goes through BL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $phone  = trim($_POST['phone'] ?? '');
    $result = $orderBL->placeOrder($_SESSION['user_id'], $phone, $_SESSION['cart']);

    if ($result['success']) {
        $_SESSION['cart'] = [];
        header('Location: /melodio/orders.php?success=1'); exit;
    } else {
        $message = $result['message'];
        $msgType = 'error';
    }
}

// Build cart details
$cartDetails = []; $total = 0;
foreach ($_SESSION['cart'] as $item) {
    $product = $productBL->getProductById($item['product_id']);
    if ($product) {
        $subtotal      = $product['price'] * $item['quantity'];
        $total        += $subtotal;
        $cartDetails[] = array_merge($item, [
            'name' => $product['name'], 'price' => $product['price'],
            'image' => $product['image'], 'stock' => $product['stock'],
            'subtotal' => $subtotal,
        ]);
    }
}

$pageTitle = 'Your Order';
include 'header.php';
?>
<main>
<div style="max-width:700px; margin:30px auto; padding:0 20px;">
    <h2 style="font-size:20px; font-weight:bold; margin-bottom:20px;">Your Order</h2>

    <?php if ($message): ?>
        <div class="alert alert-error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($cartDetails)): ?>
        <div style="text-align:center; padding:60px 0; color:#999;">
            <p style="margin-bottom:16px;">Your cart is empty.</p>
            <a href="/melodio/products.php" style="background:#c7a17a; color:#fff; padding:10px 24px; border-radius:8px; text-decoration:none;">Browse Products</a>
        </div>
    <?php else: ?>
        <?php foreach ($cartDetails as $item): ?>
        <div class="cart-item" style="display:flex; align-items:center; background:#e8e2d9; border-radius:10px; padding:14px; margin-bottom:12px; gap:14px;">
            <img src="/melodio/assets/images/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>"
                 alt="<?= htmlspecialchars($item['name']) ?>"
                 style="width:65px; height:65px; object-fit:contain; border-radius:8px; background:#fff; padding:4px;">
            <div style="flex:1;">
                <p style="font-size:15px; font-weight:600; margin-bottom:4px;"><?= htmlspecialchars($item['name']) ?></p>
                <form method="POST" style="display:inline-flex; align-items:center; gap:6px;">
                    <input type="hidden" name="update_qty" value="<?= $item['product_id'] ?>">
                    <button type="button" onclick="changeQty(<?= $item['product_id'] ?>, -1)"
                            style="width:24px; height:24px; border:1px solid #bbb; background:#f5f0e8; border-radius:4px; cursor:pointer;">[−]</button>
                    <input type="number" id="qty_<?= $item['product_id'] ?>"
                           name="qty_<?= $item['product_id'] ?>"
                           value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>"
                           style="width:36px; text-align:center; border:1px solid #ccc; border-radius:4px; padding:3px; font-size:13px;">
                    <button type="button" onclick="changeQty(<?= $item['product_id'] ?>, 1)"
                            style="width:24px; height:24px; border:1px solid #bbb; background:#f5f0e8; border-radius:4px; cursor:pointer;">[+]</button>
                    <button type="submit" style="font-size:11px; color:#c7a17a; background:none; border:none; cursor:pointer; text-decoration:underline;">Update</button>
                </form>
            </div>
            <p style="font-size:15px; font-weight:bold; min-width:70px; text-align:right;">$<?= number_format($item['price'], 2) ?></p>
            <button onclick="confirmDelete(<?= $item['product_id'] ?>)"
                    style="background:none; border:none; cursor:pointer; font-size:18px; color:#999;">✕</button>
        </div>
        <?php endforeach; ?>

        <form id="checkoutForm" method="POST" style="margin-top:24px;">
            <h3 style="font-size:16px; font-weight:bold; margin-bottom:14px;">Delivery information</h3>
            <div style="display:flex; border:1px solid #ccc; border-radius:8px; overflow:hidden; margin-bottom:16px; background:#fff;">
                <span style="background:#e8e2d9; padding:10px 14px; font-size:14px; color:#555; border-right:1px solid #ccc;">+966</span>
                <input type="text" id="phone" name="phone" placeholder="Phone Number"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                       style="flex:1; border:none; outline:none; padding:10px 14px; font-size:14px;">
            </div>
            <span class="validation-error" id="phone_error"></span>
            <p style="font-size:14px; font-weight:bold; margin-bottom:4px;">Cash on Delivery</p>
            <p style="font-size:13px; color:#888; margin-bottom:24px;">Pay when your order is delivered.</p>
            <button type="submit" name="place_order"
                    style="width:100%; padding:14px; background:#c7a17a; color:#fff; border:none; border-radius:8px; font-size:16px; font-weight:bold; cursor:pointer;">
                Place Order
            </button>
        </form>
    <?php endif; ?>
</div>
</main>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:300; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:24px; max-width:300px; width:90%; position:relative;">
        <button onclick="closeModal()" style="position:absolute; top:10px; right:14px; background:none; border:none; font-size:18px; cursor:pointer; color:#999;">✕</button>
        <p style="font-size:14px; margin-bottom:20px;">Are you sure you want to delete this piece?</p>
        <form id="deleteForm" method="POST" style="text-align:right;">
            <input type="hidden" id="deleteProductId" name="remove_item" value="">
            <button type="submit" style="background:#222; color:#fff; border:none; padding:8px 24px; border-radius:8px; font-size:14px; cursor:pointer;">Yes</button>
        </form>
    </div>
</div>

<script>
function changeQty(productId, delta) {
    const input = document.getElementById('qty_' + productId);
    const max = parseInt(input.max);
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}
function confirmDelete(productId) {
    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
<?php include 'footer.php'; ?>
