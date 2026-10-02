<?php
require_once __DIR__ . '/business/ProductBL.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$productBL = new ProductBL();
$featured  = array_slice($productBL->getAllProducts(), 0, 3);

$pageTitle = 'Home';
include 'header.php';
?>

<main>
    <!-- Hero Banner - contained with rounded corners -->
    <div style="max-width:1100px; margin: 24px auto; padding: 0 30px;">
        <img src="/melodio/assets/images/melodio_banner.jpg"
             alt="Melodio Banner"
             style="width:100%; height:280px; object-fit:cover; border-radius:14px; display:block;">
    </div>

    <div class="page-layout">

        <!-- Sidebar -->
        <aside class="sidebar">
            <h3>Categories</h3>
            <a href="/melodio/products.php" class="cat-link">All Products</a>
            <a href="/melodio/products.php?category=Guitars"   class="cat-link">🎸 Guitars</a>
            <a href="/melodio/products.php?category=Keyboards" class="cat-link">🎹 Keyboards</a>
            <a href="/melodio/products.php?category=Drums"     class="cat-link">🥁 Drums</a>
        </aside>

        <!-- Products -->
        <div style="flex:1;">
            <h2 class="section-title">
                New Melodio
                <span>(Everything for $199.99)</span>
            </h2>

            <div class="cards">
                <?php foreach ($featured as $product): ?>
                    <div class="card">
                        <a href="/melodio/product-details.php?id=<?= $product['id'] ?>">
                            <img src="/melodio/assets/images/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <p>$<?= number_format($product['price'], 2) ?></p>
                        <?php if ($product['stock'] > 0): ?>
                            <a href="/melodio/product-details.php?id=<?= $product['id'] ?>" class="add-btn">
                                Add to Cart
                            </a>
                        <?php else: ?>
                            <button disabled style="background:#ccc; cursor:not-allowed;">Out of Stock</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>
