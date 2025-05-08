<?php
include 'db.php';

// Get car ID and dates from URL
$car_id = isset($_GET['id']) ? $_GET['id'] : 0;
$pickup_date = isset($_GET['pickup_date']) ? $_GET['pickup_date'] : '';
$return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';

// Validate inputs
if (empty($car_id) || empty($pickup_date) || empty($return_date)) {
    header("Location: cars.php");
    exit;
}

// Get car details
$query = "SELECT c.*, l.city, l.address FROM cars c 
          JOIN locations l ON c.location_id = l.location_id 
          WHERE c.car_id = " . $conn->real_escape_string($car_id);
$result = $conn->query($query);

if ($result->num_rows == 0) {
    // Car not found, redirect to cars page
    header("Location: cars.php");
    exit;
}

$car = $result->fetch_assoc();

// Calculate rental days and total price
$pickup = new DateTime($pickup_date);
$return = new DateTime($return_date);
$rental_days = $return->diff($pickup)->days;
$total_price = $car['price_per_day'] * $rental_days;

// Get all locations for the dropdown
$locationQuery = "SELECT * FROM locations WHERE is_active = 1";
$locationResult = $conn->query($locationQuery);
$locations = [];
while ($location = $locationResult->fetch_assoc()) {
    $locations[] = $location;
}

// Process booking form submission
$booking_success = false;
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $pickup_location = isset($_POST['pickup_location']) ? $_POST['pickup_location'] : '';
    $return_location = isset($_POST['return_location']) ? $_POST['return_location'] : '';
    $driving_license = isset($_POST['driving_license']) ? trim($_POST['driving_license']) : '';
    
    // Simple validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($pickup_location) || empty($return_location) || empty($driving_license)) {
        $booking_error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $booking_error = 'Please enter a valid email address.';
    } else {
        // Check if user exists or create new user
        $userQuery = "SELECT user_id FROM users WHERE email = '" . $conn->real_escape_string($email) . "'";
        $userResult = $conn->query($userQuery);
        
        if ($userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $user_id = $user['user_id'];
            
            // Update user information
            $updateUserQuery = "UPDATE users SET 
                                first_name = '" . $conn->real_escape_string($first_name) . "',
                                last_name = '" . $conn->real_escape_string($last_name) . "',
                                phone = '" . $conn->real_escape_string($phone) . "',
                                driving_license = '" . $conn->real_escape_string($driving_license) . "'
                                WHERE user_id = " . $user_id;
            $conn->query($updateUserQuery);
        } else {
            // Create new user
            $password = password_hash(uniqid(), PASSWORD_DEFAULT); // Generate random password
            $insertUserQuery = "INSERT INTO users (first_name, last_name, email, phone, password, driving_license) 
                               VALUES ('" . $conn->real_escape_string($first_name) . "',
                                      '" . $conn->real_escape_string($last_name) . "',
                                      '" . $conn->real_escape_string($email) . "',
                                      '" . $conn->real_escape_string($phone) . "',
                                      '" . $conn->real_escape_string($password) . "',
                                      '" . $conn->real_escape_string($driving_license) . "')";
            $conn->query($insertUserQuery);
            $user_id = $conn->insert_id;
        }
        
        // Create booking
        $insertBookingQuery = "INSERT INTO bookings (user_id, car_id, pickup_location_id, return_location_id, pickup_date, return_date, total_price, booking_status, payment_status) 
                              VALUES (" . $user_id . ",
                                     " . $conn->real_escape_string($car_id) . ",
                                     " . $conn->real_escape_string($pickup_location) . ",
                                     " . $conn->real_escape_string($return_location) . ",
                                     '" . $conn->real_escape_string($pickup_date) . "',
                                     '" . $conn->real_escape_string($return_date) . "',
                                     " . $conn->real_escape_string($total_price) . ",
                                     'Confirmed',
                                     'Paid')";
        
        if ($conn->query($insertBookingQuery)) {
            $booking_id = $conn->insert_id;
            $booking_success = true;
            
            // Redirect to confirmation page
            header("Location: confirmation.php?id=" . $booking_id);
            exit;
        } else {
            $booking_error = 'An error occurred while processing your booking. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Car - RentACar</title>
    <style>
        /* Minimal CSS for functionality */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        
        header {
            background-color: #e74c3c;
            color: white;
            padding: 10px;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 15px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }
        
        .booking-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .booking-form {
            flex: 1;
            min-width: 300px;
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
        }
        
        .booking-summary {
            width: 300px;
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .form-row {
            display: flex;
            gap: 10px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .error {
            color: red;
            margin-bottom: 15px;
        }
        
        button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            width: 100%;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="navbar">
            <div class="logo">RentACar</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="cars.php">Cars</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <h1>Complete Your Booking</h1>
        
        <div class="booking-container">
            <!-- Booking Form -->
            <div class="booking-form">
                <h2>Enter Your Details</h2>
                
                <?php if (!empty($booking_error)): ?>
                    <div class="error"><?php echo $booking_error; ?></div>
                <?php endif; ?>
                
                <form action="booking.php?id=<?php echo $car_id; ?>&pickup_date=<?php echo $pickup_date; ?>&return_date=<?php echo $return_date; ?>" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="driving_license">Driving License Number</label>
                        <input type="text" id="driving_license" name="driving_license" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pickup_location">Pickup Location</label>
                            <select id="pickup_location" name="pickup_location" required>
                                <option value="">Select Pickup Location</option>
                                <?php foreach($locations as $location): ?>
                                    <option value="<?php echo $location['location_id']; ?>" <?php echo ($location['location_id'] == $car['location_id']) ? 'selected' : ''; ?>>
                                        <?php echo $location['city'] . ' - ' . $location['address']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="return_location">Return Location</label>
                            <select id="return_location" name="return_location" required>
                                <option value="">Select Return Location</option>
                                <?php foreach($locations as $location): ?>
                                    <option value="<?php echo $location['location_id']; ?>" <?php echo ($location['location_id'] == $car['location_id']) ? 'selected' : ''; ?>>
                                        <?php echo $location['city'] . ' - ' . $location['address']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit">Complete Booking</button>
                </form>
            </div>
            
            <!-- Booking Summary -->
            <div class="booking-summary">
                <h2>Booking Summary</h2>
                
                <div>
                    <h3><?php echo $car['brand'] . ' ' . $car['model']; ?></h3>
                    <p><?php echo $car['car_type']; ?> | <?php echo $car['transmission']; ?></p>
                </div>
                
                <div class="summary-details">
                    <div class="summary-item">
                        <span>Pickup Date:</span>
                        <span><?php echo date('M d, Y', strtotime($pickup_date)); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Return Date:</span>
                        <span><?php echo date('M d, Y', strtotime($return_date)); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Rental Duration:</span>
                        <span><?php echo $rental_days; ?> days</span>
                    </div>
                    <div class="summary-item">
                        <span>Price per Day:</span>
                        <span>$<?php echo $car['price_per_day']; ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total:</span>
                        <span>$<?php echo $total_price; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 RentACar. All Rights Reserved.</p>
    </footer>
</body>
</html>
