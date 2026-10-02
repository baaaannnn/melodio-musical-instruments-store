<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn  = isset($_SESSION['user_id']);
$userRole    = $_SESSION['role'] ?? '';
$cartCount   = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Melodio' : 'Melodio' ?></title>
    <link rel="stylesheet" href="/melodio/assets/style.css">
</head>
<body>

<header>
    <div class="container">
        <!-- Logo with guitar icon -->
        <a href="/melodio/index.php" class="logo">
            <img src="/melodio/assets/images/melodio_logo.png" alt="Melodio" width="30" style="object-fit:contain;">
            Melodio
        </a>

        <!-- Navigation -->
        <nav>
            <?php if ($userRole === 'admin'): ?>
                <a href="/melodio/index.php"     class="<?= $currentPage==='index.php'     ?'active':'' ?>">Home</a>
                <a href="/melodio/dashboard.php" class="<?= $currentPage==='dashboard.php' ?'active':'' ?>">Dashboard</a>
                <a href="/melodio/orders.php"    class="<?= $currentPage==='orders.php'    ?'active':'' ?>">Orders</a>
            <?php elseif ($userRole === 'delivery'): ?>
                <a href="/melodio/orders.php" class="<?= $currentPage==='orders.php' ?'active':'' ?>">My Deliveries</a>
            <?php else: ?>
                <a href="/melodio/index.php"    class="<?= $currentPage==='index.php'    ?'active':'' ?>">Home</a>
                <a href="/melodio/products.php" class="<?= $currentPage==='products.php' ?'active':'' ?>">Products</a>
                <a href="/melodio/orders.php"   class="<?= $currentPage==='orders.php'   ?'active':'' ?>">Orders</a>
            <?php endif; ?>
        </nav>

        <!-- Actions -->
        <div class="nav-actions">
            <!-- Search icon (no function) -->
            <span class="nav-icon" title="Search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </span>

            <!-- Cart icon -->
            <?php if ($userRole === 'customer' || !$isLoggedIn): ?>
                <a href="/melodio/cart.php" class="nav-icon cart-icon" title="Cart">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <!-- Login / Logout -->
            <?php if ($isLoggedIn): ?>
                <a href="/melodio/logout.php" class="login-btn">Logout</a>
            <?php else: ?>
                <a href="/melodio/login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
