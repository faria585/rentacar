<?php
include 'db.php';

// Get all locations for the dropdown
$locationQuery = "SELECT * FROM locations WHERE is_active = 1";
$locationResult = $conn->query($locationQuery);

// Get featured cars
$featuredCarsQuery = "SELECT c.*, l.city FROM cars c 
                      JOIN locations l ON c.location_id = l.location_id 
                      WHERE c.is_available = 1 
                      ORDER BY c.rating DESC LIMIT 6";
$featuredCarsResult = $conn->query($featuredCarsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar - Find Your Perfect Rental Car</title>
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
        
        /* Hero Section */
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #fff;
            padding: 0 1rem;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 700px;
        }
        
        /* Search Form */
        .search-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 2rem;
            width: 90%;
            max-width: 1000px;
            margin: -70px auto 3rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e74c3c;
        }
        
        .search-btn {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
            grid-column: span 1;
            align-self: end;
        }
        
        .search-btn:hover {
            background-color: #c0392b;
        }
        
        /* Featured Cars Section */
        .section {
            padding: 4rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        
        .section-title span {
            color: #e74c3c;
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
        
        .car-details {
            padding: 1.5rem;
        }
        
        .car-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .car-location {
            color: #777;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .car-location i {
            margin-right: 0.5rem;
            color: #e74c3c;
        }
        
        .car-features {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            color: #555;
        }
        
        .feature {
            display: flex;
            align-items: center;
        }
        
        .feature i {
            margin-right: 0.5rem;
            color: #e74c3c;
        }
        
        .car-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e74c3c;
        }
        
        .price span {
            font-size: 0.9rem;
            font-weight: 400;
            color: #777;
        }
        
        .rating {
            display: flex;
            align-items: center;
            color: #f39c12;
        }
        
        .rating span {
            margin-left: 0.5rem;
            color: #777;
        }
        
        /* Why Choose Us Section */
        .why-us {
            background-color: #f1f2f6;
            padding: 4rem 5%;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .feature-card {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .feature-text {
            color: #777;
            font-size: 0.95rem;
        }
        
        /* Call to Action */
        .cta {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            padding: 5rem 1rem;
            text-align: center;
            color: #fff;
        }
        
        .cta-content {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        
        .cta p {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        /* Footer */
        footer {
            background-color: #222;
            color: #fff;
            padding: 4rem 5% 2rem;
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
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .search-container {
                padding: 1.5rem;
                margin-top: -50px;
            }
            
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .search-btn {
                grid-column: 1;
            }
            
            .section {
                padding: 2rem 1rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .cars-grid {
                grid-template-columns: 1fr;
            }
            
            .cta h2 {
                font-size: 1.8rem;
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

    <!-- Hero Section -->
    <section class="hero">
        <h1>Find Your Perfect Rental Car</h1>
        <p>Choose from a wide range of cars at affordable prices. Book online and hit the road in minutes.</p>
    </section>

    <!-- Search Form -->
    <div class="search-container">
        <form class="search-form" action="cars.php" method="GET">
            <div class="form-group">
                <label for="location">Pickup Location</label>
                <select class="form-control" id="location" name="location" required>
                    <option value="">Select Location</option>
                    <?php while($location = $locationResult->fetch_assoc()): ?>
                        <option value="<?php echo $location['location_id']; ?>"><?php echo $location['city'] . ' - ' . $location['address']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="pickup_date">Pickup Date</label>
                <input type="date" class="form-control" id="pickup_date" name="pickup_date" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="return_date">Return Date</label>
                <input type="date" class="form-control" id="return_date" name="return_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
            </div>
            <div class="form-group">
                <label for="car_type">Car Type</label>
                <select class="form-control" id="car_type" name="car_type">
                    <option value="">All Types</option>
                    <option value="Economy">Economy</option>
                    <option value="Compact">Compact</option>
                    <option value="Midsize">Midsize</option>
                    <option value="Luxury">Luxury</option>
                    <option value="SUV">SUV</option>
                    <option value="Van">Van</option>
                </select>
            </div>
            <button type="submit" class="search-btn">Search Cars</button>
        </form>
    </div>

    <!-- Featured Cars Section -->
    <section class="section">
        <h2 class="section-title">Featured <span>Cars</span></h2>
        <div class="cars-grid">
            <?php while($car = $featuredCarsResult->fetch_assoc()): ?>
                <div class="car-card">
                    <img src="images/<?php echo $car['image']; ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>" class="car-image">
                    <div class="car-details">
                        <h3 class="car-title"><?php echo $car['brand'] . ' ' . $car['model']; ?></h3>
                        <p class="car-location"><i class="fas fa-map-marker-alt"></i> <?php echo $car['city']; ?></p>
                        <div class="car-features">
                            <div class="feature"><i class="fas fa-car"></i> <?php echo $car['car_type']; ?></div>
                            <div class="feature"><i class="fas fa-gas-pump"></i> <?php echo $car['fuel_type']; ?></div>
                            <div class="feature"><i class="fas fa-cog"></i> <?php echo $car['transmission']; ?></div>
                        </div>
                        <div class="car-price">
                            <div class="price">$<?php echo $car['price_per_day']; ?> <span>/ day</span></div>
                            <div class="rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $car['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif($i - 0.5 <= $car['rating']): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span>(<?php echo number_format($car['rating'], 1); ?>)</span>
                            </div>
                        </div>
                        <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="location.href='car-details.php?id=<?php echo $car['car_id']; ?>'">View Details</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-us">
        <h2 class="section-title">Why Choose <span>RentACar</span></h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-car"></i></div>
                <h3 class="feature-title">Wide Selection of Cars</h3>
                <p class="feature-text">Choose from our extensive fleet of vehicles ranging from economy cars to luxury SUVs.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-dollar-sign"></i></div>
                <h3 class="feature-title">Best Price Guarantee</h3>
                <p class="feature-text">We offer the most competitive rates in the market with no hidden fees.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h3 class="feature-title">24/7 Customer Support</h3>
                <p class="feature-text">Our dedicated team is available round the clock to assist you with any queries.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3 class="feature-title">Fully Insured Vehicles</h3>
                <p class="feature-text">All our cars come with comprehensive insurance coverage for your peace of mind.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta">
        <div class="cta-content">
            <h2>Ready to Hit the Road?</h2>
            <p>Book your car now and enjoy the freedom of exploring at your own pace. Special discounts available for long-term rentals.</p>
            <button class="btn btn-primary" onclick="location.href='cars.php'">Browse All Cars</button>
        </div>
    </section>

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

        // Function to redirect to car details page
        function viewCarDetails(carId) {
            window.location.href = 'car-details.php?id=' + carId;
        }
    </script>
</body>
</html>
