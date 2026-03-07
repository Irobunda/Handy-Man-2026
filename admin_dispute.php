<?php
// admin_dispute.php - Strategic Oversight Panel
require_once 'db_config.php';
// In production, ensure admin session: if ($_SESSION['role'] !== 'admin') die('Unauthorized');

// Fetch only jobs that are 'completed' but have not had funds released, or are flagged as 'disputed'
$query = "SELECT j.*, u.full_name as customer_name, h.full_name as handyman_name 
          FROM jobs j 
          JOIN users u ON j.customer_id = u.id 
          JOIN handymen_profiles h ON j.handyman_id = h.user_id 
          WHERE j.status IN ('completed', 'disputed') 
          ORDER BY j.updated_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispute Resolution | Handy Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { height: 100vh; background: #212529; color: white; padding: 20px; }
        .dispute-card { border-left: 5px solid #dc3545; }
        .evidence-img { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 sidebar d-none d-md-block">
            <h4 class="fw-bold mb-4">Handy Admin</h4>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="#">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-success fw-bold" href="#">Disputes</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#">Verifications</a></li>
            </ul>
        </nav>

        <main class="col-md-10 p-4">
            <h2 class="mb-4">Dispute Resolution Center</h2>
            
            <div class="table-responsive bg-white p-3 rounded shadow-sm">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>Category</th>
                            <th>Customer</th>
                            <th>Handyman</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo $row['category']; ?></td>
                            <td><?php echo $row['customer_name']; ?></td>
                            <td><?php echo $row['handyman_name']; ?></td>
                            <td class="fw-bold text-success">₦<?php echo number_format($row['budget']); ?></td>
                            <td><span class="badge bg-danger">Disputed</span></td>
                            <td>
                                <button class="btn btn-sm btn-dark" onclick="viewDispute(<?php echo $row['id']; ?>)">Review Evidence</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>