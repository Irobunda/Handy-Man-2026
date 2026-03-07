<?php
// job_manage.php - Refined In-Progress Management
require_once 'db_config.php';

// 1. Get IDs from URL and Session
$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$handyman_id = 101; // In production, use $_SESSION['user_id']

// 2. Fetch Job and Customer Details using Prepared Statement (Security Fix)
$stmt = $conn->prepare("SELECT j.*, u.full_name as customer_name, u.phone as customer_phone 
                        FROM jobs j 
                        JOIN users u ON j.customer_id = u.id 
                        WHERE j.id = ? AND j.handyman_id = ?");
$stmt->bind_param("ii", $job_id, $handyman_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) die("Job not found or unauthorized access.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Job #<?php echo $job_id; ?> | Handy Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --handy-green: #0a7a2a; }
        .chat-box { height: 350px; overflow-y: auto; background: #fdfdfd; border: 1px solid #eee; padding: 15px; border-radius: 8px; }
        .message { margin-bottom: 12px; padding: 10px 15px; border-radius: 18px; max-width: 85%; font-size: 0.95rem; }
        .msg-left { background: #f1f1f1; align-self: flex-start; color: #333; }
        .msg-right { background: var(--handy-green); color: #fff; align-self: flex-end; }
        .status-header { background: #fff; border-bottom: 2px solid var(--handy-green); padding: 15px 0; position: sticky; top: 0; z-index: 100; }
        .customer-photo { max-height: 400px; width: 100%; object-fit: contain; background: #000; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">

<div class="status-header mb-4 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark mb-1">STATE: IN PROGRESS</span>
            <h4 class="mb-0 fw-bold"><?php echo htmlspecialchars($job['category']); ?></h4>
        </div>
        <a href="tel:<?php echo $job['customer_phone']; ?>" class="btn btn-success">📞 Call Customer</a>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-md-8">
            
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-0 pt-3">Initial Job Evidence</div>
                <div class="card-body">
                    <?php if (!empty($job['job_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($job['job_photo']); ?>" class="customer-photo mb-2" alt="Job Photo">
                        <p class="text-muted small mb-0 text-center">Reference photo submitted by customer during booking.</p>
                    <?php else: ?>
                        <div class="py-4 text-center border rounded bg-white">
                            <p class="text-muted mb-0 italic">No reference photo was provided for this job.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-0 pt-3">Chat with <?php echo htmlspecialchars($job['customer_name']); ?></div>
                <div class="card-body">
                    <div class="chat-box d-flex flex-column mb-3" id="chatWindow">
                        <div class="message msg-left shadow-sm">Hello! I've accepted your request. I'm heading over now.</div>
                        <div class="message msg-right shadow-sm">Great, I'll be waiting near the <strong><?php echo htmlspecialchars($job['landmark']); ?></strong>.</div>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Type instructions or updates...">
                        <button class="btn btn-success px-4">Send</button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold pt-3">Complete Job & Request Payment</div>
                <div class="card-body">
                    <p class="small text-muted mb-3">As per platform policy, you must upload at least one photo of the finished work to trigger the escrow release.</p>
                    <form action="api/complete_job.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Work Completion Photos</label>
                            <input type="file" name="work_photos[]" class="form-control" multiple required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">SUBMIT WORK FOR APPROVAL</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted extra-small fw-bold mb-3">Service Location</h6>
                    <p class="mb-4">
                        <i class="text-dark"><?php echo htmlspecialchars($job['address']); ?></i><br>
                        <span class="text-success small fw-bold mt-2 d-block">
                            <i class="bi bi-geo-alt"></i> Landmark: <?php echo htmlspecialchars($job['landmark']); ?>
                        </span>
                    </p>
                    
                    <hr class="my-4">
                    
                    <h6 class="text-uppercase text-muted extra-small fw-bold mb-2">Escrow Protected Funds</h6>
                    <h3 class="text-success fw-bold">₦<?php echo number_format($job['budget']); ?></h3>
                    <p class="text-muted" style="font-size: 0.8rem;">
                        Funds are verified and held by the platform. Payout is triggered upon customer confirmation or admin approval.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>