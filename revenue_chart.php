<?php
// revenue_chart.php - Visualizing Marketplace Earnings
require_once 'db_config.php';

// 1. Fetch Monthly Revenue for the last 12 months
$query = "SELECT 
            DATE_FORMAT(updated_at, '%b %Y') AS month_name, 
            SUM(commission_earned) AS monthly_revenue
          FROM jobs 
          WHERE status = 'released' 
          AND updated_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
          GROUP BY YEAR(updated_at), MONTH(updated_at)
          ORDER BY updated_at ASC";

$result = mysqli_query($conn, $query);

$labels = [];
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['month_name'];
    $data[] = (float)$row['monthly_revenue'];
}

// Convert arrays to JSON for JavaScript use
$labels_json = json_encode($labels);
$data_json = json_encode($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Revenue Analytics | Handy Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-success">Platform Revenue (Commission)</h5>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" style="max-height: 400px;"></canvas>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $labels_json; ?>,
            datasets: [{
                label: 'Monthly Net Revenue (₦)',
                data: <?php echo $data_json; ?>,
                backgroundColor: '#0a7a2a', // Handy Green
                borderColor: '#075a1f',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

</body>
</html>