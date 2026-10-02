<?php
require_once __DIR__ . '/business/ProductBL.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: /melodio/products.php'); exit; }

$productBL = new ProductBL();
$product   = $productBL->getProductById($id);
if (!$product) { header('Location: /melodio/products.php'); exit; }

$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /melodio/login.php');
        exit;
    }
    $qty = intval($_POST['quantity'] ?? 1);
    if ($qty < 1) $qty = 1;

    if (!$productBL->isInStock($id)) {
        $message = 'This item is out of stock.';
        $msgType = 'error';
    } else {
        $found = false;
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $id) {
                $item['quantity'] += $qty;
                $found = true;
                break;
            }
        }
        unset($item);
        if (!$found) {
            $_SESSION['cart'][] = ['product_id' => $id, 'quantity' => $qty];
        }
        $message = 'Item added to cart successfully!';
        $msgType = 'success';
    }
}

$pageTitle = $product['name'];
include 'header.php';
?>

<main>
<div style="max-width:1100px; margin:30px auto; padding:0 30px;">

    <!-- Back link -->
    <a href="/melodio/products.php" style="color:#555; font-size:14px; text-decoration:none; display:inline-block; margin-bottom:20px;">
        ← Back to Products
    </a>

    <!-- Product Layout -->
    <div style="display:flex; gap:50px; align-items:flex-start;">

        <!-- Image -->
        <div style="flex:0 0 380px; background:#f0ebe3; border-radius:14px; padding:30px; display:flex; align-items:center; justify-content:center;">
            <img src="/melodio/assets/images/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 style="width:100%; max-height:400px; object-fit:contain;">
        </div>

        <!-- Info -->
        <div style="flex:1; padding-top:10px;">

            <h1 style="font-size:28px; font-weight:bold; margin-bottom:16px; color:#222;">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <p style="font-size:20px; color:#555; margin-bottom:16px;">
                $<?= number_format($product['price'], 2) ?>
            </p>

            <hr style="border:none; border-top:1px solid #ddd; margin-bottom:16px;">

            <p style="font-size:14px; color:#777; line-height:1.8; margin-bottom:24px;">
                <?= htmlspecialchars($product['description']) ?>
            </p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>" style="margin-bottom:16px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($product['stock'] > 0): ?>
                <form method="POST">
                    <!-- Add to Cart button -->
                    <button type="submit" name="add_to_cart"
                            style="width:100%; padding:14px; background:#c7a17a; color:#fff; border:none; border-radius:8px; font-size:16px; cursor:pointer; margin-bottom:16px;">
                        Add to Cart
                    </button>

                    <!-- Quantity control -->
                    <div style="display:flex; align-items:center; gap:12px;">
                        <button type="button" onclick="changeQty(-1)"
                                style="width:32px; height:32px; border:1px solid #ccc; background:#f5f0e8; border-radius:4px; font-size:18px; cursor:pointer; line-height:1;">−</button>
                        <input type="number" id="quantity" name="quantity"
                               value="1" min="1" max="<?= $product['stock'] ?>"
                               style="width:50px; text-align:center; border:1px solid #ccc; border-radius:4px; padding:6px; font-size:15px;">
                        <button type="button" onclick="changeQty(1)"
                                style="width:32px; height:32px; border:1px solid #ccc; background:#f5f0e8; border-radius:4px; font-size:18px; cursor:pointer; line-height:1;">+</button>
                        <small style="color:#999;"><?= $product['stock'] ?> in stock</small>
                    </div>
                </form>
            <?php else: ?>
                <div style="background:#fde8e8; border:1px solid #f5c6c6; border-radius:8px; padding:10px 16px; font-size:13px; color:#c0392b; display:flex; align-items:center; gap:8px;">⚠️ This item is out of stock.</div>
            <?php endif; ?>

        </div>
    </div>
</div>
</main>

<script>
function changeQty(delta) {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    let val = parseInt(input.value) + delta;
    if (val < 1)   val = 1;
    if (val > max) val = max;
    input.value = val;
}
</script>

<?php include 'footer.php'; ?>
