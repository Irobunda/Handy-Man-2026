<?php
// verify_payment.php - Secured & Fixed Version
require_once 'db_config.php';
require_once 'sms_helper.php';

$paystack_secret = "sk_live_xxxxxxxxxxxxxxxxxxxx";
$reference = $_GET['reference'] ?? null; 

if (!$reference) {
    die("No reference supplied");
}

// 1. Verify Transaction with Paystack
$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $paystack_secret,
    "Cache-Control: no-cache",
]);

$result = curl_exec($ch);
$response = json_decode($result, true);
curl_close($ch);

// 2. Update Job State Based on Result
if ($response['status'] && $response['data']['status'] === 'success') {
    
    // CRITICAL FIX: Extract variables from metadata for use in SMS
    $metadata = $response['data']['metadata'];
    $job_id   = $metadata['job_id'];
    $state    = $metadata['state'] ?? 'Lagos';
    
    // Extracting custom fields sent in create_job.php
    $category = $metadata['custom_fields'][0]['value'] ?? 'General Handyman';
    $landmark = $metadata['custom_fields'][1]['value'] ?? 'Nearby';

    // 3. SECURE UPDATE: Transition from draft to open
    $stmt = $conn->prepare("UPDATE jobs SET status = 'open', escrow_status = 'held', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $job_id);

    if ($stmt->execute()) {
        
        // 4. NOTIFICATION LOOP: Alert verified handymen in the same state
        $proQuery = "SELECT phone FROM handymen_profiles WHERE state = ? AND is_verified = 1";
        $proStmt = $conn->prepare($proQuery);
        $proStmt->bind_param("s", $state);
        $proStmt->execute();
        $proResult = $proStmt->get_result();

        while ($pro = $proResult->fetch_assoc()) {
            // SMS Helper now has the correct $category and $landmark
            sendJobAlert($pro['phone'], $category, $landmark);
        }

        // Success: Redirect customer
        header("Location: ../dashboard.php?status=success&job_id=" . $job_id);
        exit();
    } else {
        error_log("DB Update Error: " . $conn->error);
        die("Internal Server Error.");
    }
} else {
    header("Location: ../post_job.php?status=failed");
    exit();
}
?>