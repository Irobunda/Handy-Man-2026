<?php
// trigger_dispute.php - Customer Safety Brake
require_once 'db_config.php';
session_start();

// 1. Security Guard: Ensure only logged-in customers can trigger a dispute
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// 2. Data Collection & Sanitization
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$customer_id = $_SESSION['user_id'];
$dispute_reason = mysqli_real_escape_string($conn, $_POST['dispute_reason']);

// 3. State Transition: Move Job to 'disputed'
// We ensure the job belongs to the customer and is currently in 'completed' status
$stmt = $conn->prepare("UPDATE jobs SET 
                        status = 'disputed', 
                        dispute_notes = ?, 
                        updated_at = NOW() 
                        WHERE id = ? AND customer_id = ? AND status = 'completed'");

$stmt->bind_param("sii", $dispute_reason, $job_id, $customer_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    // 4. Success: Redirect to Dashboard with a confirmation message
    header("Location: job_history.php?status=dispute_filed");
    exit();
} else {
    // Failure: Likely the job isn't in 'completed' status or unauthorized
    die("Error: This job cannot be disputed at this time. Please contact support.");
}
?>