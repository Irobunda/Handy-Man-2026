<?php
// process_verification.php - Handling Pro Documents & Bank Data
require_once 'db_config.php';
session_start();

$user_id = $_SESSION['user_id'];
$paystack_secret = "sk_live_xxxxxxxxxxxxxxxxxxxx";

// 1. Handle ID Upload
$idPath = "";
if (isset($_FILES['id_image']) && $_FILES['id_image']['error'] == 0) {
    $targetDir = "uploads/verification_ids/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    
    $fileName = time() . "_ID_" . $user_id . "_" . basename($_FILES["id_image"]["name"]);
    $targetFile = $targetDir . $fileName;
    
    if (move_uploaded_file($_FILES["id_image"]["tmp_name"], $targetFile)) {
        $idPath = $targetFile;
    }
}

// 2. Create Paystack Transfer Recipient
$account_number = $_POST['account_number'];
$bank_code = $_POST['bank_code'];
$full_name = $_SESSION['full_name'];

$url = "https://api.paystack.co/transferrecipient";
$fields = [
    "type" => "nuban",
    "name" => $full_name,
    "account_number" => $account_number,
    "bank_code" => $bank_code,
    "currency" => "NGN"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $paystack_secret,
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['status']) {
    $recipient_code = $response['data']['recipient_code'];

    // 3. Update Pro Profile in Database
    $sql = "UPDATE handymen_profiles SET 
            paystack_recipient_code = '$recipient_code', 
            id_document_path = '$idPath',
            is_verified = 0 -- Pending Admin Review
            WHERE user_id = '$user_id'";
            
    if (mysqli_query($conn, $sql)) {
        header("Location: handyman_dashboard.php?status=pending_review");
    }
} else {
    die("Bank Verification Failed: " . $response['message']);
}
?>