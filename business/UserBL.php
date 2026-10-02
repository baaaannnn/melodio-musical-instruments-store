<?php

require_once __DIR__ . '/../dal/User.php';

class UserBL {
    private $userDAL;

    public function __construct() {
        $this->userDAL = new User();
    }

    // Register — validation + hashing here in BL
    public function register($full_name, $email, $password, $confirm_password) {
        if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }
        if (strlen(trim($full_name)) < 3) {
            return ['success' => false, 'message' => 'Full name must be at least 3 characters.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }
        if ($password !== $confirm_password) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }
        if ($this->userDAL->getEmailCount($email) > 0) {
            return ['success' => false, 'message' => 'This email is already registered.'];
        }

        // Sanitize in BL
        $full_name = htmlspecialchars(strip_tags(trim($full_name)));
        $email     = strtolower(trim($email));

        // Hash password in BL before passing to DAL
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $result = $this->userDAL->register($full_name, $email, $hashed);
        if ($result) {
            return ['success' => true, 'message' => 'Account created successfully.'];
        }
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    // Login — BL verifies password, DAL only fetches user
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }

        // DAL fetches raw user data (including hashed password)
        $user = $this->userDAL->getByEmail(strtolower(trim($email)));

        // BL verifies password
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    // Get user by ID
    public function getUserById($id) {
        if (!is_numeric($id) || $id <= 0) return null;
        return $this->userDAL->getById(intval($id));
    }

    // Get all users (Admin)
    public function getAllUsers() {
        return $this->userDAL->getAll();
    }
}
