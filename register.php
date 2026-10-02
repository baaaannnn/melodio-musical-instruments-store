<?php
require_once __DIR__ . '/business/UserBL.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) { header('Location: /melodio/index.php'); exit; }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userBL = new UserBL();
    $result = $userBL->register(
        $_POST['full_name']        ?? '',
        $_POST['email']            ?? '',
        $_POST['password']         ?? '',
        $_POST['confirm_password'] ?? ''
    );

    if ($result['success']) {
        $success = $result['message'] . ' <a href="/melodio/login.php">Login now</a>';
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Sign Up';
include 'header.php';
?>
<main>
<div class="auth-container">
    <div class="auth-box">
        <h2>Sign Up</h2>
        <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <form id="registerForm" method="POST">
            <label>Full Name</label>
            <input type="text" id="full_name" name="full_name" placeholder="Enter your full name"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            <span class="validation-error" id="full_name_error"></span>

            <label>Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <span class="validation-error" id="email_error"></span>

            <label>Password</label>
            <input type="password" id="password" name="password" placeholder="At least 6 characters">
            <span class="validation-error" id="password_error"></span>

            <label>Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password">
            <span class="validation-error" id="confirm_password_error"></span>

            <button type="submit" class="submit-btn">Sign Up</button>
        </form>
        <p>Already have an account? <a href="/melodio/login.php">Login</a></p>
    </div>
</div>
</main>
<?php include 'footer.php'; ?>
