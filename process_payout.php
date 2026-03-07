<?php
// process_payout.php - Senior Dev Final State Transition
require_once 'db_config.php';

// Configuration
$paystack_secret = "sk_live_xxxxxxxxxxxxxxxxxxxx";
$commission_rate = 0.15; // 15% platform fee as per PRD strategy

// Admin-triggered Job ID
$job_id = mysqli_real_escape_string($conn, $_POST['job_id']);

// 1. Fetch Job and Handyman's Transfer Recipient Code
$query = "SELECT j.budget, h.paystack_recipient_code, j.status 
          FROM jobs j 
          JOIN handymen_profiles h ON j.handyman_id = h.user_id 
          WHERE j.id = '$job_id'";

$result = mysqli_query($conn, $query);
$job = mysqli_fetch_assoc($result);

if (!$job || empty($job['paystack_recipient_code'])) {
    die("Error: Handyman bank details not verified or job not found.");
}

// 2. Financial Calculations
$total_budget = $job['budget'];
$platform_fee = $total_budget * $commission_rate;
$payout_amount = ($total_budget - $platform_fee) * 100; // Convert to Kobo

// 3. Initiate Paystack Transfer
$url = "https://api.paystack.co/transfer";
$fields = [
    "source" => "balance",
    "amount" => $payout_amount,
    "recipient" => $job['paystack_recipient_code'],
    "reason" => "Payout for Job #" . $job_id
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

// 4. Update Database on Success
if ($response['status'] && $response['data']['status'] !== 'failed') {
    $sql = "UPDATE jobs SET 
            status = 'released', 
            escrow_status = 'payout_initiated', 
            commission_earned = '$platform_fee',
            updated_at = NOW() 
            WHERE id = '$job_id'";
            
    mysqli_query($conn, $sql);
    echo "Success: Payout of ₦" . ($payout_amount/100) . " initiated.";
} else {
    echo "Transfer Failed: " . $response['message'];
}
?>