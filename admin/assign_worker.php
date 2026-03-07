<?php
require '../includes/auth.php';
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'];
    $worker_id = $_POST['worker_id'];

    $stmt = $pdo->prepare("INSERT INTO assignments (job_id, worker_id) VALUES (?, ?)");
    $stmt->execute([$job_id, $worker_id]);
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Assign Worker</h2>
<form method="post">
<input name="job_id" placeholder="Job ID"><br>
<input name="worker_id" placeholder="Worker ID"><br>
<button type="submit">Assign</button>
</form>
</body>
</html>
