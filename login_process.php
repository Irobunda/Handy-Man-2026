<?php
// login_process.php - Secure Session Management via PDO
require_once 'db_config.php';

// session_start() must come AFTER the security headers in db_config.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- NEW: CSRF Token Check ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: login.php?error=csrf_failure");
        exit();
    }
    // 1. Sanitize and Capture Input
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty_fields");
        exit();
    }

    try {
        // 2. Prepared Statement using PDO
        // We only select what we actually need
        $stmt = $pdo->prepare("SELECT id, full_name, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 3. Verify Password and User Existence
        if ($user && password_verify($password, $user['password'])) {
            
            // 4. Prevent Session Hijacking
            // This kills the old session ID and issues a brand new one
            session_regenerate_id(true);

            // 5. Populate Session (Avoid storing sensitive data like passwords)
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['last_login'] = time();

            // 6. Role-based Redirect Logic
            if ($user['role'] === 'handyman') {
                header("Location: handyman_dashboard.php");
            } else {
                header("Location: service.html");
            }
            exit();

        } else {
            // 7. Security: Generic error prevents "User Enumeration"
            // Don't tell them if the email was right but the password was wrong.
            header("Location: login.php?error=invalid_credentials");
            exit();
        }

    } catch (PDOException $e) {
        // Log the actual error for the dev, show a generic one to the user
        error_log("Login Error: " . $e->getMessage());
        header("Location: login.php?error=system_error");
        exit();
    }
} else {
    // Redirect if they try to access this file directly via GET
    header("Location: login.php");
    exit();
}