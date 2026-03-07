<?php
// submit_review.php - Updating Professional Profiles
require_once 'db_config.php';

// 1. Collect Data from the Customer
$job_id      = intval($_POST['job_id']);
$rating      = intval($_POST['rating']); // 1 to 5
$review_text = mysqli_real_escape_string($conn, $_POST['review_text']);

// 2. Fetch Handyman ID for this specific job
$job_query = "SELECT handyman_id FROM jobs WHERE id = '$job_id'";
$job_result = mysqli_query($conn, $job_query);
$job_data = mysqli_fetch_assoc($job_result);
$handyman_id = $job_data['handyman_id'];

// 3. Save Review to the 'reviews' table
$sql_review = "INSERT INTO reviews (job_id, handyman_id, rating, comment, created_at) 
               VALUES ('$job_id', '$handyman_id', '$rating', '$review_text', NOW())";

if (mysqli_query($conn, $sql_review)) {
    
    // 4. Update the Handyman's Profile Aggregate Rating
    // We calculate the new average to keep the 'handymen_profiles' table performant
    $rating_query = "SELECT AVG(rating) as avg_rating, COUNT(id) as total_reviews 
                     FROM reviews WHERE handyman_id = '$handyman_id'";
    $rating_res = mysqli_fetch_assoc(mysqli_query($conn, $rating_query));
    
    $new_avg = $rating_res['avg_rating'];
    $new_count = $rating_res['total_reviews'];

    $update_profile = "UPDATE handymen_profiles SET 
                       rating_avg = '$new_avg', 
                       review_count = '$new_count' 
                       WHERE user_id = '$handyman_id'";
    
    mysqli_query($conn, $update_profile);

    // 5. Final State Transition: Mark Job as 'Reviewed'
    mysqli_query($conn, "UPDATE jobs SET status = 'reviewed' WHERE id = '$job_id'");

    header("Location: job_history.php?status=thank_you");
}
?>