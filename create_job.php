<?php
// create_job.php - Secure PDO & Paystack Integration
require_once 'db_config.php'; 
session_start();

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Configuration
$paystack_secret = "sk_live_xxxxxxxxxxxxxxxxxxxx"; // Use Environment Variables in production!
$paystack_url    = "https://api.paystack.co/transaction/initialize";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. Collect and Sanitize Data
    $customer_id = $_SESSION['user_id'];
    $email       = $_SESSION['email'];
    $category    = $_POST['category'] ?? 'Plumbing';
    $description = $_POST['jobType'] ?? ''; 
    $address     = $_POST['address'] ?? '';
    $landmark    = $_POST['landmark'] ?? ''; 
    $state       = $_POST['state'] ?? 'Lagos';
    $budget      = (float)($_POST['budget'] ?? 0);

    // 3. Secure Photo Upload Logic
    $photoPath = null;
    if (isset($_FILES['jobPhoto']) && $_FILES['jobPhoto']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileMime = mime_content_type($_FILES['jobPhoto']['tmp_name']);
        
        if (in_array($fileMime, $allowedTypes)) {
            $targetDir = "uploads/jobs/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

            $extension = pathinfo($_FILES["jobPhoto"]["name"], PATHINFO_EXTENSION);
            $fileName = bin2hex(random_bytes(10)) . "." . $extension; // Secure random filename
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["jobPhoto"]["tmp_name"], $targetFile)) {
                $photoPath = $targetFile;
            }
        }
    }

    try {
        // 4. PDO Prepared Statement (No more mysqli_query with variables!)
        $sql = "INSERT INTO jobs (customer_id, category, description, address, landmark, state, budget, job_photo, status) 
                VALUES (:customer_id, :category, :description, :address, :landmark, :state, :budget, :photoPath, 'draft')";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':customer_id' => $customer_id,
            ':category'    => $category,
            ':description' => $description,
            ':address'     => $address,
            ':landmark'    => $landmark,
            ':state'       => $state,
            ':budget'      => $budget,
            ':photoPath'   => $photoPath
        ]);

        $job_id = $pdo->lastInsertId();

        // 5. Initialize Paystack Transaction
        $fields = [
            'email'        => $email,
            'amount'       => ($budget * 100), // Kobo conversion
            'callback_url' => "https://yourwebsite.com/verify_payment.php",
            'metadata'     => [
                'job_id'   => $job_id,
                'category' => $category
            ]
        ];

        // Paystack API Call
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $paystack_url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($fields), // JSON is cleaner for Paystack
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer $paystack_secret",
                "Content-Type: application/json",
                "Cache-Control: no-cache"
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);

        $result = curl_exec($ch);
        $response = json_decode($result, true);
        curl_close($ch);

        if ($response['status']) {
            header("Location: " . $response['data']['authorization_url']);
            exit();
        } else {
            throw new Exception("Paystack Error: " . $response['message']);
        }

    } catch (Exception $e) {
        error_log($e->getMessage());
        die("An error occurred while creating the job. Please try again.");
    }
}
?>