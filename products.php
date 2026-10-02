<?php
require_once __DIR__ . '/business/ProductBL.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$productBL = new ProductBL();
$category  = $_GET['category'] ?? '';
$products  = $category ? $productBL->getProductsByCategory($category) : $productBL->getAllProducts();

$categories = [
    'Guitars'   => '🎸',
    'Keyboards' => '🎹',
    'Drums'     => '🥁',
];

$pageTitle = 'Products';
include 'header.php';
?>
<main>

    <!-- Hero Banner -->
    <div style="max-width:1100px; margin: 24px auto; padding: 0 30px;">
        <img src="/melodio/assets/images/melodio_banner.jpg"
             alt="Melodio Banner"
             style="width:100%; height:280px; object-fit:cover; border-radius:14px; display:block;">
    </div>

    <div class="page-layout">

        <!-- Sidebar -->
        <aside class="sidebar">
            <h3>Categories</h3>
            <a href="/melodio/products.php"
               class="cat-link <?= !$category ? 'active' : '' ?>"
               style="<?= !$category ? 'background:#c7a17a; color:#fff; padding:6px 10px; border-radius:6px;' : '' ?>">
                All Products
            </a>
            <?php foreach ($categories as $cat => $icon): ?>
                <a href="/melodio/products.php?category=<?= urlencode($cat) ?>"
                   class="cat-link"
                   style="<?= $category === $cat ? 'background:#c7a17a; color:#fff; padding:6px 10px; border-radius:6px;' : '' ?>">
                    <?= $icon ?> <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </aside>

        <!-- Products -->
        <div style="flex:1;">
            <h2 class="section-title"><?= $category ? htmlspecialchars($category) : 'Our Collection' ?></h2>

            <?php if (empty($products)): ?>
                <div class="alert alert-warning">No products found.</div>
            <?php else: ?>
                <div class="products-list">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <a href="/melodio/product-details.php?id=<?= $product['id'] ?>">
                                <img src="/melodio/assets/images/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            </a>
                            <h4><?= htmlspecialchars($product['name']) ?></h4>
                            <p>$<?= number_format($product['price'], 2) ?></p>
                            <?php if ($product['stock'] > 0): ?>
                                <a href="/melodio/product-details.php?id=<?= $product['id'] ?>">
                                    <button>Add to Cart</button>
                                </a>
                            <?php else: ?>
                                <button disabled style="background:#ccc; cursor:not-allowed;">Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>
<?php include 'footer.php'; ?>
