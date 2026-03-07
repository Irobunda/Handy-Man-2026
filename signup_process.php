
<?php
// signup_process.php - Hardened User Onboarding
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Define allowed roles (White-listing)
    $allowed_roles = ['customer', 'handyman'];
    
    // 2. Collect and Sanitize
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone     = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? ''); // Clean phone number
    $password  = $_POST['password'] ?? '';
    
    // 3. Logic: Default to 'customer' if role is invalid or tampered with
    $role = in_array($_POST['role'], $allowed_roles) ? $_POST['role'] : 'customer';

    // 4. Basic Validation
    if (!$email || strlen($password) < 8 || empty($full_name)) {
        header("Location: signup.php?error=invalid_input");
        exit();
    }

    try {
        // 5. Start Transaction: Atomic "All or Nothing" write
        $pdo->beginTransaction();

        // 6. Check for Duplicate Email/Phone BEFORE inserting
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
        $checkStmt->execute([$email, $phone]);
        if ($checkStmt->fetch()) {
            throw new Exception("User already exists", 409);
        }

        // 7. Secure Hashing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 8. Insert User
        $sqlUser = "INSERT INTO users (full_name, email, phone, password, role, created_at) 
                    VALUES (:name, :email, :phone, :pass, :role, NOW())";
        $stmtUser = $pdo->prepare($sqlUser);
        $stmtUser->execute([
            ':name'  => $full_name,
            ':email' => $email,
            ':phone' => $phone,
            ':pass'  => $hashed_password,
            ':role'  => $role
        ]);

        $user_id = $pdo->lastInsertId();

        // 9. Standardized Role Logic: Creating Profile for 'handyman'
        if ($role === 'handyman') {
            // Standardizing naming: Use 'handyman_profiles' and 'user_id'
            $sqlProfile = "INSERT INTO handyman_profiles (user_id, business_phone, status) 
                           VALUES (:uid, :phone, 'pending_verification')";
            $stmtProfile = $pdo->prepare($sqlProfile);
            $stmtProfile->execute([
                ':uid'   => $user_id,
                ':phone' => $phone
            ]);
        }

        // 10. Commit changes
        $pdo->commit();

        header("Location: login.php?signup=success");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }

        if ($e->getCode() === 409) {
            header("Location: signup.php?error=user_exists");
        } else {
            error_log("Signup Error: " . $e->getMessage());
            header("Location: signup.php?error=system_failure");
        }
        exit();
    }
}