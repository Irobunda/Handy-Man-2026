
<?php
// admin/login.php
require_once '../config/db.php'; // Ensure this has your $pdo connection
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Fetch admin from the users table with a specific 'admin' role
    $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // 2. Verify password against the hashed version in DB
    if ($admin && password_verify($password, $admin['password'])) {
        
        // 3. Security: Prevent Session Hijacking
        session_regenerate_id(true);

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        
        header('Location: dashboard.php');
        exit;
    } else {
        // Generic error to prevent username harvesting
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Access</title></head>
<body>
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="login_process.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
</body>
</html>