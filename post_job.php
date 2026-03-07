<form id="postJobForm" onsubmit="event.preventDefault(); showAppHandoff();">

<script>
    function showAppHandoff() {
        // Instead of sending to PHP, we show the "Continue in App" screen
        document.querySelectorAll('.form-step').forEach(step => step.classList.remove('active'));
        
        // You can create a hidden 4th step or a modal that says:
        // "Great! Your estimate is ₦[Budget]. To secure your funds in escrow 
        // and find a pro, please download the Handy App."
        alert("To protect your payment with Escrow, please download the Handy App to finalize this booking.");
        window.location.href = "https://play.google.com/store/apps/details?id=com.handy.ng";
    }
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job | Handy Nigeria</title>
    <link rel="stylesheet" href="styles.css"> </head>
<body class="bg-light">

    <header class="main-header">
        <div class="logo"><a href="index.html">Handy</a></div>
    </header>

    <main class="form-container">
        <div class="wrap">
            <div class="form-card">
                
                <?php if ($success): ?>
                    <div class="alert success">Job posted successfully! Redirecting to payment...</div>
                    <script>setTimeout(() => { window.location.href = 'service.html'; }, 2000);</script>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form id="postJobForm" action="post_job.php" method="POST">
                    
                    <div class="form-step active" id="step1">
                        <h2>What do you need help with?</h2>
                        <div class="form-group">
                            <label>Select Category</label>
                            <select name="category" id="categorySelect" required>
                                <option value="">-- Choose Category --</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical & Power</option>
                                <option value="cleaning">Cleaning</option>
                                <option value="automation">Security & Smart Home</option>
                                <option value="carpentry">Carpentry</option>
                                <option value="general">General Maintenance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Describe the Task</label>
                            <textarea name="description" placeholder="Example: My kitchen sink is leaking..." required></textarea>
                        </div>
                        <button type="button" class="btn-primary" onclick="nextStep(2)">Continue</button>
                    </div>

                    <div class="form-step" id="step2">
                        <h2>Where is the job located?</h2>
                        <div class="form-group">
                            <label>Street Address</label>
                            <input type="text" name="address" placeholder="123 Allen Avenue, Ikeja" required>
                        </div>
                        <div class="form-group">
                            <label>Closest Landmark (Mandatory)</label>
                            <input type="text" name="landmark" placeholder="e.g. Opposite GTBank" required>
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="state" required>
                                <option value="Lagos">Lagos</option>
                                <option value="Abuja">Abuja (FCT)</option>
                                <option value="Rivers">Rivers</option>
                            </select>
                        </div>
                        <div class="button-row">
                            <button type="button" class="btn-ghost" onclick="nextStep(1)">Back</button>
                            <button type="button" class="btn-primary" onclick="nextStep(3)">Final Step</button>
                        </div>
                    </div>

                    <div class="form-step" id="step3">
                        <h2>Set your budget</h2>
                        <p class="form-note">Funds are held securely in escrow and only released when the job is done.</p>
                        <div class="form-group">
                            <label>Estimated Budget (₦)</label>
                            <input type="number" name="budget" placeholder="Enter amount" required>
                        </div>
                        <div class="button-row">
                            <button type="button" class="btn-ghost" onclick="nextStep(2)">Back</button>
                            <button type="submit" class="btn-primary">Post & Pay via Paystack</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <script>
        function nextStep(stepNumber) {
            document.querySelectorAll('.form-step').forEach(step => {
                step.classList.remove('active');
            });
            document.getElementById('step' + stepNumber).classList.add('active');
        }
    </script>
</body>
</html>