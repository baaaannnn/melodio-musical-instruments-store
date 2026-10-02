<?php
require_once __DIR__ . '/business/UserBL.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin')        header('Location: /melodio/dashboard.php');
    elseif ($role === 'delivery') header('Location: /melodio/orders.php');
    else                          header('Location: /melodio/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $userBL = new UserBL();
    $result = $userBL->login($email, $password);

    if ($result['success']) {
        $user = $result['user'];
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role']      = $user['role'];

        if ($user['role'] === 'admin')        header('Location: /melodio/dashboard.php');
        elseif ($user['role'] === 'delivery') header('Location: /melodio/orders.php');
        else                                  header('Location: /melodio/index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Login';
include 'header.php';
?>
<main>
<div class="auth-container">
    <div class="auth-box">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form id="loginForm" method="POST">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <span class="validation-error" id="email_error"></span>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password">
            <span class="validation-error" id="password_error"></span>

            <div class="forgot"><a href="#">Forgot password?</a></div>
            <button type="submit" class="submit-btn">Login</button>
        </form>
        <p>Don't have an account? <a href="/melodio/register.php">Sign Up</a></p>
    </div>
</div>
</main>
<?php include 'footer.php'; ?>
