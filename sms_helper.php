<?php
// sms_helper.php - Optimized for Termii (Nigeria)

function sendJobAlert($phone, $category, $landmark) {
    $apikey = "YOUR_TERMII_API_KEY"; // Replace with your Termii Key
    
    // 1. Normalize Phone Number to 234 format
    // Remove leading zero and append 234 if necessary
    $phone = preg_replace('/^0/', '234', trim($phone));
    if (strlen($phone) == 10) { $phone = "234" . $phone; }

    // 2. Draft the Message
    $message = "Handy Pro Alert: $category needed near $landmark. Log in to your dashboard to accept now!";
    
    $data = [
        "to" => $phone,
        "from" => "HandyNG", // Ensure this is approved in your Termii Dashboard
        "sms" => $message,
        "type" => "plain",
        "channel" => "generic",
        "api_key" => $apikey,
    ];

    $payload = json_encode($data);

    // 3. Initialize cURL
    $ch = curl_init("https://api.ng.termii.com/api/sms/send");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ));
    
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    // 4. Handle Result & Logging
    if ($err) {
        error_log("Termii cURL Error: " . $err);
        return false;
    }

    $response = json_decode($result, true);
    
    // Log failures for Admin oversight
    if (!isset($response['message']) || $response['message'] !== "Successfully Sent") {
        error_log("Termii SMS Failed for $phone: " . ($response['message'] ?? 'Unknown Error'));
    }

    return $response;
}
?>