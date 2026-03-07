<?php
// job_history.php - Customer's Digital Service Ledger
require_once 'db_config.php';
// In production, use session: $customer_id = $_SESSION['user_id'];
$customer_id = 1; 

// Fetch all jobs that are finished (Released or Refunded)
// We also check if they have already been reviewed to hide the form
$query = "SELECT * FROM jobs 
          WHERE customer_id = '$customer_id' 
          AND status IN ('released', 'refunded', 'reviewed') 
          ORDER BY updated_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job History | Handy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --handy-green: #0a7a2a; }
        .history-card { border: none; border-left: 4px solid var(--handy-green); transition: 0.2s; border-radius: 12px; }
        .history-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .status-pill { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Star Rating Style */
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: start; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 2rem; color: #ddd; cursor: pointer; transition: 0.2s; }
        .star-rating input:checked ~ label { color: #ffc107; }
        .star-rating label:hover, .star-rating label:hover ~ label { color: #ffc107; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="fw-bold mb-4 text-dark">Service History</h2>

    <div class="row">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): 
                $photos = json_decode($row['completion_photos'], true);
                $is_reviewed = ($row['status'] == 'reviewed');
            ?>
                <div class="col-12 mb-3">
                    <div class="card history-card shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($row['category']); ?></h5>
                                    <p class="text-muted small mb-0"><?php echo date('M d, Y', strtotime($row['updated_at'])); ?></p>
                                    <span class="badge rounded-pill <?php echo $row['status'] == 'released' ? 'bg-success' : ($is_reviewed ? 'bg-info' : 'bg-danger'); ?> status-pill mt-2">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </div>

                                <div class="col-md-5">
                                    <p class="small mb-2"><strong>Task:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                                    <?php if($photos): ?>
                                        <div class="d-flex gap-2">
                                            <?php foreach(array_slice($photos, 0, 3) as $img): ?>
                                                <img src="<?php echo $img; ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #eee;">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 text-md-end">
                                    <h4 class="fw-bold text-dark mb-1">₦<?php echo number_format($row['budget']); ?></h4>
                                    <div class="mt-3">
                                        <?php if(!$is_reviewed && $row['status'] == 'released'): ?>
                                            <button class="btn btn-sm btn-warning fw-bold" data-bs-toggle="collapse" data-bs-target="#reviewForm<?php echo $row['id']; ?>">
                                                Rate Service
                                            </button>
                                        <?php endif; ?>
                                        <a href="generate_receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success">Receipt</a>
                                    </div>
                                </div>
                            </div>

                            <div class="collapse mt-3" id="reviewForm<?php echo $row['id']; ?>">
                                <div class="card card-body bg-white border-0 shadow-sm">
                                    <h6 class="fw-bold">Tell us how the Pro did:</h6>
                                    <form action="submit_review.php" method="POST">
                                        <input type="hidden" name="job_id" value="<?php echo $row['id']; ?>">
                                        <div class="star-rating mb-3">
                                            <input type="radio" name="rating" value="5" id="star5-<?php echo $row['id']; ?>"><label for="star5-<?php echo $row['id']; ?>">★</label>
                                            <input type="radio" name="rating" value="4" id="star4-<?php echo $row['id']; ?>"><label for="star4-<?php echo $row['id']; ?>">★</label>
                                            <input type="radio" name="rating" value="3" id="star3-<?php echo $row['id']; ?>"><label for="star3-<?php echo $row['id']; ?>">★</label>
                                            <input type="radio" name="rating" value="2" id="star2-<?php echo $row['id']; ?>"><label for="star2-<?php echo $row['id']; ?>">★</label>
                                            <input type="radio" name="rating" value="1" id="star1-<?php echo $row['id']; ?>"><label for="star1-<?php echo $row['id']; ?>">★</label>
                                        </div>
                                        <textarea name="review_text" class="form-control mb-3" placeholder="Explain your experience..."></textarea>
                                        <button type="submit" class="btn btn-success w-100">Submit Review</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                <p class="text-muted">No completed jobs yet. Your history will appear here once service is released.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>