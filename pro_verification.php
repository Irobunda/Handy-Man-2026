<?php
// pro_verification.php - Secure Pro Onboarding
require_once 'db_config.php';
session_start();

// Security Guard: Ensure only handymen can access
if ($_SESSION['role'] !== 'handyman') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Pro Account | Handy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --handy-green: #0a7a2a; }
        .verification-card { border-top: 5px solid var(--handy-green); border-radius: 12px; }
        .form-label { font-weight: 600; font-size: 0.9rem; color: #555; }
        .alert-security { background-color: #eef9f1; border: none; color: var(--handy-green); font-size: 0.85rem; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card verification-card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h2 class="fw-bold mb-1">Verify Your Identity</h2>
                    <p class="text-muted mb-4">Complete your profile to start accepting jobs and receiving payouts.</p>

                    <div class="alert alert-security mb-4">
                        <i class="bi bi-shield-lock-fill"></i> Your data is encrypted and used only for identity verification and Paystack payout setup.
                    </div>

                    <form action="process_verification.php" method="POST" enctype="multipart/form-data">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">1. Identity Verification</h5>
                        <div class="mb-4">
                            <label class="form-label">ID Type</label>
                            <select name="id_type" class="form-select" required>
                                <option value="nin">National ID (NIN)</option>
                                <option value="voters">Voters Card</option>
                                <option value="passport">International Passport</option>
                                <option value="license">Drivers License</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Upload ID Image (Front)</label>
                            <input type="file" name="id_image" class="form-control" accept="image/*" required>
                        </div>

                        <h5 class="fw-bold mb-3 border-bottom pb-2">2. Payout Bank Account</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Bank</label>
                                <select name="bank_code" class="form-select" required>
                                    <option value="058">GTBank</option>
                                    <option value="011">First Bank</option>
                                    <option value="033">United Bank for Africa (UBA)</option>
                                    <option value="044">Access Bank</option>
                                    <option value="057">Zenith Bank</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Number (10 Digits)</label>
                                <input type="text" name="account_number" class="form-control" maxlength="10" placeholder="0123456789" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">Submit for Verification</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>