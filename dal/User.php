<?php

require_once 'Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Register new user
    public function register($full_name, $email, $password) {
        $stmt = $this->db->prepare("CALL sp_RegisterUser(?, ?, ?)");
        $stmt->bind_param('sss', $full_name, $email, $password);
        $result = $stmt->execute();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $result;
    }

    // Get user by email (raw — BL handles password verification)
    public function getByEmail($email) {
        $stmt = $this->db->prepare("CALL sp_LoginUser(?)");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $result->free();
        $stmt->close();
        // Free any extra result sets from stored procedure
        while ($this->db->more_results()) {
            $this->db->next_result();
        }
        return $user;
    }

    // Get user by ID
    public function getById($id) {
        $stmt = $this->db->prepare("CALL sp_GetUserById(?)");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $user;
    }

    // Get all users
    public function getAll() {
        $result = $this->db->query("CALL sp_GetAllUsers()");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return $rows;
    }

    // Return raw email count — BL decides if it's a duplicate
    public function getEmailCount($email) {
        $stmt = $this->db->prepare("CALL sp_EmailExists(?)");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        while ($this->db->more_results()) { $this->db->next_result(); }
        return (int)($row['count'] ?? 0);
    }
}
