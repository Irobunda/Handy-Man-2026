<?php
// api/accept_job.php
require_once '../db_config.php';
session_start();

$job_id = $_POST['job_id'];
$handyman_id = 101; // Mocking the logged-in Handyman ID

// Update Job State
$sql = "UPDATE jobs SET 
        status = 'accepted', 
        handyman_id = '$handyman_id', 
        updated_at = NOW() 
        WHERE id = '$job_id' AND status = 'open'";

if (mysqli_query($conn, $sql)) {
    // Transition successful: Move to 'In Progress' view
    header("Location: ../job_details.php?id=" . $job_id);
} else {
    echo "Error: Job may have already been accepted by another pro.";
}
?>