<?php
// admin_verify_pros.php - Secured Manual Review
require_once 'db_config.php';
session_start();

// Security Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch unverified handymen
$query = "SELECT u.full_name, u.email, h.* FROM handymen_profiles h 
          JOIN users u ON h.user_id = u.id 
          WHERE h.is_verified = 0 AND h.id_document_path IS NOT NULL 
          ORDER BY u.created_at ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Professionals | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="fw-bold mb-4">Pending Professional Verifications</h2>
    
    <div class="table-responsive bg-white rounded shadow-sm p-3">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Professional</th>
                    <th>ID Document</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                        </td>
                        <td>
                            <a href="<?php echo htmlspecialchars($row['id_document_path']); ?>" target="_blank">View ID Document</a>
                        </td>
                        <td>
                            <form action="api/approve_pro.php" method="POST">
                                <input type="hidden" name="user_id" value="<?php echo (int)$row['user_id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>