<?php
require_once __DIR__ . '/config/db.php';

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];

$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$name, $email, $password, $role]);

header("Location: login_process.php");
exit;

<!DOCTYPE html>
<html>
<head>
  <title>Become a Tradesperson - Handyman</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="auth-container">
  <h2>Become a Handyman Tradesperson</h2>

  <form method="POST" action="signup_process.php">

    <label>Full Name</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Primary Skill</label>
    <input type="text" name="skill" required>

    <input type="hidden" name="role" value="worker">

    <button type="submit" class="btn-primary">Apply</button>

  </form>

</div>

</body>
</html>