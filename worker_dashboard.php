<?php
// handyman_dashboard.php
require_once 'db_config.php';
// In production, check session: if ($_SESSION['role'] !== 'handyman') die('Access Denied');

// Fetch Open Jobs
$query = "SELECT * FROM jobs WHERE status = 'open' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handyman Dashboard | Handy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --handy-green: #0a7a2a; }
        .navbar { background-color: var(--handy-green); }
        .job-card { border-left: 5px solid var(--handy-green); transition: 0.3s; }
        .job-card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .badge-budget { background-color: #eef9f1; color: var(--handy-green); font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">HANDY PRO</a>
        <div class="d-flex">
            <span class="navbar-text text-white me-3">Verified Professional</span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Available Jobs</h2>
    
    <div class="row">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 mb-3">
                    <div class="card job-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title fw-bold"><?php echo $row['category']; ?></h5>
                                <span class="badge badge-budget p-2">₦<?php echo number_format($row['budget']); ?></span>
                            </div>
                            <p class="card-text text-muted"><?php echo substr($row['description'], 0, 100); ?>...</p>
                            
                            <div class="mb-3 small">
                                <strong>📍 Location:</strong> <?php echo $row['state']; ?> <br>
                                <strong>🏢 Landmark:</strong> <?php echo $row['landmark']; ?> </div>

                            <form action="api/accept_job.php" method="POST">
                                <input type="hidden" name="job_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-success w-100 fw-bold">Accept Job</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">No jobs currently available. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>