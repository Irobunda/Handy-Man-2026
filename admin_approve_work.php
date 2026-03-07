<?php
// approve_work.php - Customer Review & Release Page
require_once 'db_config.php';

$job_id = intval($_GET['id']);
// In production, verify the customer owns this job: WHERE id = $job_id AND customer_id = $_SESSION['user_id']
$query = "SELECT * FROM jobs WHERE id = '$job_id'";
$result = mysqli_query($conn, $query);
$job = mysqli_fetch_assoc($result);

// Decode the completion photos we saved as JSON in the previous step
$photos = json_decode($job['completion_photos'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Finished Work | Handy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .proof-img { width: 100%; height: 250px; object-fit: cover; border-radius: 8px; margin-bottom: 15px; }
        .btn-release { background-color: #0a7a2a; color: white; padding: 15px; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="fw-bold mb-3">Job Completed!</h2>
                    <p class="text-muted">Your handyman has marked the job as finished. Please review the photos below to confirm you are satisfied.</p>
                    
                    <div class="row mt-4">
                        <?php if($photos): foreach($photos as $img): ?>
                            <div class="col-md-6">
                                <img src="<?php echo $img; ?>" class="proof-img shadow-sm" alt="Work Proof">
                            </div>
                        <?php endforeach; else: ?>
                            <p class="italic">No photos uploaded.</p>
                        <?php endif; ?>
                    </div>

                    <div class="alert alert-info mt-4">
                        <strong>Security Note:</strong> Clicking "Approve & Release" will immediately authorize the transfer of <strong>₦<?php echo number_format($job['budget']); ?></strong> to the professional.
                    </div>

                    <form action="process_payout.php" method="POST">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-release btn-lg">Approve & Release Funds</button>
                            <a href="contact_support.php?dispute=<?php echo $job['id']; ?>" class="btn btn-outline-danger">I have an issue with this work</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>