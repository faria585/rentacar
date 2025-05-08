<?php
include 'db.php';

// Get car ID from URL
$car_id = isset($_GET['id']) ? $_GET['id'] : 0;
$pickup_date = isset($_GET['pickup_date']) ? $_GET['pickup_date'] : '';
$return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';

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

// Get car features as array
$features = explode(',', $car['features']);

// Calculate rental days and total price if dates are provided
$rental_days = 0;
$total_price = 0;

if (!empty($pickup_date) && !empty($return_date)) {
    $pickup = new DateTime($pickup_date);
    $return = new DateTime($return_date);
    $rental_days = $return->diff($pickup)->days;
    $total_price = $car['price_per_day'] * $rental_days;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $car['brand'] . ' ' . $car['model']; ?> - RentACar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        /* Header Styles */
        header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #e74c3c;
        }
        
        .logo span {
            color: #333;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 2rem;
            font-weight: 500;
        }
        
        .nav-links li a:hover {
            color: #e74c3c;
        }
        
        .auth-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-outline {
            border: 2px solid #e74c3c;
            color: #e74c3c;
            background: transparent;
        }
        
        .btn-outline:hover {
            background-color: #e74c3c;
            color: #fff;
        }
        
        .btn-primary {
            background-color: #e74c3c;
            color: #fff;
            border: 2px solid #e74c3c;
        }
        
        .btn-primary:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
        
        /* Page Header */
        .page-header {
            background-color: #e74c3c;
            color: #fff;
            padding: 2rem 5%;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .breadcrumb {
            display: flex;
            justify-content: center;
            list-style: none;
        }
        
        .breadcrumb li {
            margin: 0 0.5rem;
        }
        
        .breadcrumb li:not(:last-child)::after {
            content: '/';
            margin-left: 0.5rem;
        }
        
        .breadcrumb a {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .breadcrumb a:hover {
            color: #fff;
        }
        
        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 5%;
        }
        
        /* Car Details */
        .car-details-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        
        /* Car Gallery */
        .car-gallery {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        /* Car Info */
        .car-info {
            background-color: #fff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .car-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .car-subtitle {
            color: #777;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .car-subtitle i {
            margin-right: 0.5rem;
            color: #e74c3c;
        }
        
        .car-rating {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .stars {
            color: #f39c12;
            margin-right: 0.5rem;
        }
        
        .rating-text {
            color: #777;
        }
        
        .car-description {
            margin-bottom: 2rem;
            color: #555;
        }
        
        .car-features-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .features-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            color: #555;
        }
        
        .feature-item i {
            margin-right: 0.5rem;
            color: #e74c3c;
        }
        
        .car-specs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .spec-item {
            text-align: center;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .spec-icon {
            font-size: 1.5rem;
            color: #e74c3c;
            margin-bottom: 0.5rem;
        }
        
        .spec-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.2rem;
        }
        
        .spec-label {
            font-size: 0.9rem;
            color: #777;
        }
        
        /* Booking Card */
        .booking-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 90px;
        }
        
        .price {
            font-size: 2rem;
            font-weight: 700;
            color: #e74c3c;
            margin-bottom: 0.5rem;
        }
        
        .price span {
            font-size: 1rem;
            font-weight: 400;
            color: #777;
        }
        
        .booking-form {
            margin-top: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e74c3c;
        }
        
        .booking-summary {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            color: #555;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            font-weight: 600;
            font-size: 1.2rem;
            color: #333;
        }
        
        .book-btn {
            width: 100%;
            padding: 1rem;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 1.5rem;
        }
        
        .book-btn:hover {
            background-color: #c0392b;
        }
        
        /* Similar Cars */
        .similar-cars {
            margin-top: 3rem;
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .car-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .car-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }
        
        .car-card-details {
            padding: 1.5rem;
        }
        
        .car-card-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .car-card-location {
            color: #777;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .car-card-location i {
            margin-right: 0.5rem;
            color: #e74c3c;
        }
        
        .car-card-features {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            color: #555;
        }
        
        .card-feature {
            display: flex;
            align-items: center;
        }
        
        .card-feature i {
            margin-right: 0.5rem;
            color: #e74c3c;
        }
        
        .car-card-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .card-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e74c3c;
        }
        
        .card-price span {
            font-size: 0.9rem;
            font-weight: 400;
            color: #777;
        }
        
        .card-rating {
            display: flex;
            align-items: center;
            color: #f39c12;
        }
        
        .card-rating span {
            margin-left: 0.5rem;
            color: #777;
        }
        
        /* Footer */
        footer {
            background-color: #222;
            color: #fff;
            padding: 4rem 5% 2rem;
            margin-top: 3rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: #e74c3c;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: #ccc;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #e74c3c;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #333;
            border-radius: 50%;
            color: #fff;
            transition: background-color 0.3s ease;
        }
        
        .social-links a:hover {
            background-color: #e74c3c;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid #444;
            color: #ccc;
            font-size: 0.9rem;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .car-details-container {
                grid-template-columns: 1fr;
            }
            
            .booking-card {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }
            
            .nav-links {
                margin-top: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-links li {
                margin: 0.5rem;
            }
            
            .auth-buttons {
                margin-top: 1rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .car-title {
                font-size: 1.8rem;
            }
            
            .main-image {
                height: 300px;
            }
            
            .car-specs {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .features-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">Rent<span>ACar</span></div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="cars.php">Cars</a></li>
                <li><a href="#">Locations</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <button class="btn btn-outline" onclick="location.href='login.php'">Login</button>
                <button class="btn btn-primary" onclick="location.href='register.php'">Register</button>
            </div>
        </nav>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <h1><?php echo $car['brand'] . ' ' . $car['model']; ?></h1>
        <ul class="breadcrumb">
            <li><a href="index.php">Home</a></li>
            <li><a href="cars.php">Cars</a></li>
            <li><?php echo $car['brand'] . ' ' . $car['model']; ?></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="car-details-container">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Car Gallery -->
                <div class="car-gallery">
                    <img src="images/<?php echo $car['image']; ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>" class="main-image">
                </div>
                
                <!-- Car Info -->
                <div class="car-info">
                    <h1 class="car-title"><?php echo $car['brand'] . ' ' . $car['model'] . ' (' . $car['year'] . ')'; ?></h1>
                    <p class="car-subtitle"><i class="fas fa-map-marker-alt"></i> <?php echo $car['city'] . ' - ' . $car['address']; ?></p>
                    
                    <div class="car-rating">
                        <div class="stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= $car['rating']): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif($i - 0.5 <= $car['rating']): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-text"><?php echo number_format($car['rating'], 1); ?> out of 5</span>
                    </div>
                    
                    <p class="car-description"><?php echo $car['description']; ?></p>
                    
                    <h3 class="car-features-title">Car Specifications</h3>
                    <div class="car-specs">
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-car"></i></div>
                            <div class="spec-value"><?php echo $car['car_type']; ?></div>
                            <div class="spec-label">Type</div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-gas-pump"></i></div>
                            <div class="spec-value"><?php echo $car['fuel_type']; ?></div>
                            <div class="spec-label">Fuel Type</div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-cog"></i></div>
                            <div class="spec-value"><?php echo $car['transmission']; ?></div>
                            <div class="spec-label">Transmission</div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-user"></i></div>
                            <div class="spec-value"><?php echo $car['seats']; ?></div>
                            <div class="spec-label">Seats</div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-palette"></i></div>
                            <div class="spec-value"><?php echo $car['color']; ?></div>
                            <div class="spec-label">Color</div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div class="spec-value"><?php echo $car['year']; ?></div>
                            <div class="spec-label">Year</div>
                        </div>
                    </div>
                    
                    <h3 class="car-features-title">Features</h3>
                    <div class="features-list">
                        <?php foreach($features as $feature): ?>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo trim($feature); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Booking Card -->
            <div class="right-column">
                <div class="booking-card">
                    <div class="price">$<?php echo $car['price_per_day']; ?> <span>/ day</span></div>
                    
                    <form class="booking-form" action="booking.php" method="GET">
                        <input type="hidden" name="id" value="<?php echo $car['car_id']; ?>">
                        
                        <div class="form-group">
                            <label for="pickup_date">Pickup Date</label>
                            <input type="date" class="form-control" id="pickup_date" name="pickup_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $pickup_date; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="return_date">Return Date</label>
                            <input type="date" class="form-control" id="return_date" name="return_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo $return_date; ?>" required>
                        </div>
                        
                        <?php if ($rental_days > 0): ?>
                            <div class="booking-summary">
                                <div class="summary-item">
                                    <span>Rental Period:</span>
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
                        <?php endif; ?>
                        
                        <button type="submit" class="book-btn">Continue to Booking</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Similar Cars Section -->
        <div class="similar-cars">
            <h2 class="section-title">Similar Cars You Might Like</h2>
            
            <?php
            // Get similar cars (same car type, different ID)
            $similarCarsQuery = "SELECT c.*, l.city FROM cars c 
                                JOIN locations l ON c.location_id = l.location_id 
                                WHERE c.car_type = '" . $conn->real_escape_string($car['car_type']) . "' 
                                AND c.car_id != " . $conn->real_escape_string($car_id) . " 
                                AND c.is_available = 1 
                                LIMIT 3";
            $similarCarsResult = $conn->query($similarCarsQuery);
            
            if ($similarCarsResult->num_rows > 0):
            ?>
            <div class="cars-grid">
                <?php while($similarCar = $similarCarsResult->fetch_assoc()): ?>
                    <div class="car-card">
                        <img src="images/<?php echo $similarCar['image']; ?>" alt="<?php echo $similarCar['brand'] . ' ' . $similarCar['model']; ?>" class="car-image">
                        <div class="car-card-details">
                            <h3 class="car-card-title"><?php echo $similarCar['brand'] . ' ' . $similarCar['model']; ?></h3>
                            <p class="car-card-location"><i class="fas fa-map-marker-alt"></i> <?php echo $similarCar['city']; ?></p>
                            <div class="car-card-features">
                                <div class="card-feature"><i class="fas fa-car"></i> <?php echo $similarCar['car_type']; ?></div>
                                <div class="card-feature"><i class="fas fa-gas-pump"></i> <?php echo $similarCar['fuel_type']; ?></div>
                            </div>
                            <div class="car-card-price">
                                <div class="card-price">$<?php echo $similarCar['price_per_day']; ?> <span>/ day</span></div>
                                <div class="card-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= $similarCar['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif($i - 0.5 <= $similarCar['rating']): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="location.href='car-details.php?id=<?php echo $similarCar['car_id']; ?>'">View Details</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>RentACar</h3>
                <p>Your trusted partner for car rentals. We provide quality cars at affordable prices to make your journey comfortable and hassle-free.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="cars.php">Cars</a></li>
                    <li><a href="#">Locations</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Our Services</h3>
                <ul class="footer-links">
                    <li><a href="#">Car Rental</a></li>
                    <li><a href="#">Long Term Rental</a></li>
                    <li><a href="#">Airport Pickup</a></li>
                    <li><a href="#">Corporate Rental</a></li>
                    <li><a href="#">Chauffeur Service</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact Us</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt"></i> 123 Main Street, New York, NY 10001</li>
                    <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                    <li><i class="fas fa-envelope"></i> info@rentacar.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 RentACar. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        // JavaScript for date validation
        document.addEventListener('DOMContentLoaded', function() {
            const pickupDateInput = document.getElementById('pickup_date');
            const returnDateInput = document.getElementById('return_date');
            
            pickupDateInput.addEventListener('change', function() {
                const pickupDate = new Date(this.value);
                const nextDay = new Date(pickupDate);
                nextDay.setDate(pickupDate.getDate() + 1);
                
                const year = nextDay.getFullYear();
                const month = String(nextDay.getMonth() + 1).padStart(2, '0');
                const day = String(nextDay.getDate()).padStart(2, '0');
                
                returnDateInput.min = `${year}-${month}-${day}`;
                
                if (returnDateInput.value && new Date(returnDateInput.value) <= pickupDate) {
                    returnDateInput.value = `${year}-${month}-${day}`;
                }
            });
        });
    </script>
</body>
</html>
