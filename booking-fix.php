<?php
// Add this at the top of booking.php to debug any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Make sure the database connection is working
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add this before the form submission check to ensure the form is being processed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug the POST data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Continue with the existing code...
}
