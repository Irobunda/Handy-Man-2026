<?php
// admin_dashboard.php - Secured Oversight
require_once 'db_config.php';
session_start();

// 1. Critical Security Guard: Ensure only Admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Fetch Metrics using the established database connection
$metrics_query = "SELECT 
    (SELECT COUNT(*) FROM jobs WHERE status = 'open') as open_jobs,
    (SELECT COUNT(*) FROM handymen_profiles WHERE is_verified = 0) as pending_pros,
    (SELECT SUM(budget) FROM jobs WHERE status IN ('accepted', 'completed')) as escrow_balance,
    (SELECT SUM(commission_earned) FROM jobs WHERE status = 'released') as total_revenue";

$result = mysqli_query($conn, $metrics_query);
$metrics = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Command Center | Handy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="fw-bold mb-4">Platform Overview</h2>
    
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold">Open Jobs</small>
                <h2 class="fw-bold"><?php echo (int)$metrics['open_jobs']; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3 border-start border-warning border-5">
                <small class="text-muted text-uppercase fw-bold">Pending Pros</small>
                <h2 class="fw-bold text-warning"><?php echo (int)$metrics['pending_pros']; ?></h2>
                <a href="admin_verify_pros.php" class="small">Review Now →</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold">In Escrow</small>
                <h2 class="fw-bold text-success">₦<?php echo number_format($metrics['escrow_balance'] ?? 0); ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3 bg-dark text-white">
                <small class="text-white-50 text-uppercase fw-bold">Net Revenue</small>
                <h2 class="fw-bold">₦<?php echo number_format($metrics['total_revenue'] ?? 0); ?></h2>
            </div>
        </div>
    </div>
</div>
</body>
</html>