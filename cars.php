<?php
include 'db.php';

// Get filter parameters
$location_id = isset($_GET['location']) ? $_GET['location'] : '';
$pickup_date = isset($_GET['pickup_date']) ? $_GET['pickup_date'] : '';
$return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';
$car_type = isset($_GET['car_type']) ? $_GET['car_type'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$transmission = isset($_GET['transmission']) ? $_GET['transmission'] : '';
$fuel_type = isset($_GET['fuel_type']) ? $_GET['fuel_type'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc';

// Build query
$query = "SELECT c.*, l.city FROM cars c 
          JOIN locations l ON c.location_id = l.location_id 
          WHERE c.is_available = 1";

// Add filters
if (!empty($location_id)) {
    $query .= " AND c.location_id = " . $conn->real_escape_string($location_id);
}

if (!empty($car_type)) {
    $query .= " AND c.car_type = '" . $conn->real_escape_string($car_type) . "'";
}

if (!empty($min_price)) {
    $query .= " AND c.price_per_day >= " . $conn->real_escape_string($min_price);
}

if (!empty($max_price)) {
    $query .= " AND c.price_per_day <= " . $conn->real_escape_string($max_price);
}

if (!empty($transmission)) {
    $query .= " AND c.transmission = '" . $conn->real_escape_string($transmission) . "'";
}

if (!empty($fuel_type)) {
    $query .= " AND c.fuel_type = '" . $conn->real_escape_string($fuel_type) . "'";
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY c.price_per_day ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY c.price_per_day DESC";
        break;
    case 'rating_desc':
        $query .= " ORDER BY c.rating DESC";
        break;
    default:
        $query .= " ORDER BY c.price_per_day ASC";
}

// Execute query
$result = $conn->query($query);

// Get all locations for the filter
$locationQuery = "SELECT * FROM locations WHERE is_active = 1";
$locationResult = $conn->query($locationQuery);

// Get min and max prices for the filter
$priceQuery = "SELECT MIN(price_per_day) as min_price, MAX(price_per_day) as max_price FROM cars";
$priceResult = $conn->query($priceQuery);
$priceRange = $priceResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - RentACar</title>
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
            display: flex;
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 5%;
            gap: 2rem;
        }
        
        /* Filters Sidebar */
        .filters {
            flex: 0 0 300px;
            background-color: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            align-self: flex-start;
            position: sticky;
            top: 90px;
        }
        
        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filter-title button {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .filter-group {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1.5rem;
        }
        
        .filter-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .filter-group h3 {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #555;
        }
        
        .filter-options {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }
        
        .filter-checkbox {
            display: flex;
            align-items: center;
        }
        
        .filter-checkbox input {
            margin-right: 0.5rem;
        }
        
        .price-range {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }
        
        .price-inputs {
            display: flex;
            gap: 0.5rem;
        }
        
        .price-inputs input {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filter-btn {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        
        .filter-btn:hover {
            background-color: #c0392b;
        }
        
        /* Cars List */
        .cars-list {
            flex: 1;
        }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .results-count {
            font-size: 1.1rem;
            color: #555;
        }
        
        .sort-options {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sort-options label {
            color: #555;
        }
        
        .sort-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
        }
        
        .car-item {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            display: flex;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .car-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .car-image-container {
            flex: 0 0 300px;
            position: relative;
        }
        
        .car-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .car-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background-color: #e74c3c;
            color: #fff;
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .car-info {
            flex: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        
        .car-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .car-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .car-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e74c3c;
            text-align: right;
        }
        
        .car-price span {
            font-size: 0.9rem;
            font-weight: 400;
            color: #777;
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
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
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
        
        .car-description {
            color: #777;
            margin-bottom: 1rem;
            flex: 1;
        }
        
        .car-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
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
        
        .car-actions {
            display: flex;
            gap: 1rem;
        }
        
        /* No Results */
        .no-results {
            background-color: #fff;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .no-results p {
            color: #777;
            margin-bottom: 1.5rem;
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
            .main-content {
                flex-direction: column;
            }
            
            .filters {
                flex: none;
                width: 100%;
                position: static;
            }
            
            .car-item {
                flex-direction: column;
            }
            
            .car-image-container {
                flex: none;
                height: 200px;
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
            
            .car-features {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .car-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .car-actions {
                width: 100%;
            }
            
            .car-actions .btn {
                flex: 1;
                text-align: center;
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
        <h1>Available Cars</h1>
        <ul class="breadcrumb">
            <li><a href="index.php">Home</a></li>
            <li>Cars</li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Filters Sidebar -->
        <div class="filters">
            <div class="filter-title">
                <h2>Filters</h2>
                <button onclick="resetFilters()">Reset All</button>
            </div>
            <form id="filter-form" action="cars.php" method="GET">
                <?php if (!empty($pickup_date) && !empty($return_date)): ?>
                    <input type="hidden" name="pickup_date" value="<?php echo $pickup_date; ?>">
                    <input type="hidden" name="return_date" value="<?php echo $return_date; ?>">
                <?php endif; ?>
                
                <div class="filter-group">
                    <h3>Location</h3>
                    <div class="filter-options">
                        <select class="form-control" name="location">
                            <option value="">All Locations</option>
                            <?php while($location = $locationResult->fetch_assoc()): ?>
                                <option value="<?php echo $location['location_id']; ?>" <?php echo ($location_id == $location['location_id']) ? 'selected' : ''; ?>>
                                    <?php echo $location['city']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="filter-group">
                    <h3>Car Type</h3>
                    <div class="filter-options">
                        <div class="filter-checkbox">
                            <input type="radio" id="all-types" name="car_type" value="" <?php echo empty($car_type) ? 'checked' : ''; ?>>
                            <label for="all-types">All Types</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="economy" name="car_type" value="Economy" <?php echo ($car_type == 'Economy') ? 'checked' : ''; ?>>
                            <label for="economy">Economy</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="compact" name="car_type" value="Compact" <?php echo ($car_type == 'Compact') ? 'checked' : ''; ?>>
                            <label for="compact">Compact</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="midsize" name="car_type" value="Midsize" <?php echo ($car_type == 'Midsize') ? 'checked' : ''; ?>>
                            <label for="midsize">Midsize</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="luxury" name="car_type" value="Luxury" <?php echo ($car_type == 'Luxury') ? 'checked' : ''; ?>>
                            <label for="luxury">Luxury</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="suv" name="car_type" value="SUV" <?php echo ($car_type == 'SUV') ? 'checked' : ''; ?>>
                            <label for="suv">SUV</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="van" name="car_type" value="Van" <?php echo ($car_type == 'Van') ? 'checked' : ''; ?>>
                            <label for="van">Van</label>
                        </div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <h3>Price Range</h3>
                    <div class="price-range">
                        <div class="price-inputs">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo $min_price; ?>" min="<?php echo floor($priceRange['min_price']); ?>">
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo $max_price; ?>" max="<?php echo ceil($priceRange['max_price']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <h3>Transmission</h3>
                    <div class="filter-options">
                        <div class="filter-checkbox">
                            <input type="radio" id="all-transmission" name="transmission" value="" <?php echo empty($transmission) ? 'checked' : ''; ?>>
                            <label for="all-transmission">All</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="automatic" name="transmission" value="Automatic" <?php echo ($transmission == 'Automatic') ? 'checked' : ''; ?>>
                            <label for="automatic">Automatic</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="manual" name="transmission" value="Manual" <?php echo ($transmission == 'Manual') ? 'checked' : ''; ?>>
                            <label for="manual">Manual</label>
                        </div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <h3>Fuel Type</h3>
                    <div class="filter-options">
                        <div class="filter-checkbox">
                            <input type="radio" id="all-fuel" name="fuel_type" value="" <?php echo empty($fuel_type) ? 'checked' : ''; ?>>
                            <label for="all-fuel">All</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="petrol" name="fuel_type" value="Petrol" <?php echo ($fuel_type == 'Petrol') ? 'checked' : ''; ?>>
                            <label for="petrol">Petrol</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="diesel" name="fuel_type" value="Diesel" <?php echo ($fuel_type == 'Diesel') ? 'checked' : ''; ?>>
                            <label for="diesel">Diesel</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="hybrid" name="fuel_type" value="Hybrid" <?php echo ($fuel_type == 'Hybrid') ? 'checked' : ''; ?>>
                            <label for="hybrid">Hybrid</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="radio" id="electric" name="fuel_type" value="Electric" <?php echo ($fuel_type == 'Electric') ? 'checked' : ''; ?>>
                            <label for="electric">Electric</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="filter-btn">Apply Filters</button>
            </form>
        </div>
        
        <!-- Cars List -->
        <div class="cars-list">
            <div class="list-header">
                <div class="results-count">
                    <?php echo $result->num_rows; ?> cars found
                    <?php if (!empty($pickup_date) && !empty($return_date)): ?>
                        for <?php echo date('M d, Y', strtotime($pickup_date)); ?> - <?php echo date('M d, Y', strtotime($return_date)); ?>
                    <?php endif; ?>
                </div>
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" class="sort-select" onchange="updateSort(this.value)">
                        <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="rating_desc" <?php echo ($sort == 'rating_desc') ? 'selected' : ''; ?>>Best Rated</option>
                    </select>
                </div>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <?php while($car = $result->fetch_assoc()): ?>
                    <div class="car-item">
                        <div class="car-image-container">
                            <img src="images/<?php echo $car['image']; ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>" class="car-image">
                            <div class="car-badge"><?php echo $car['car_type']; ?></div>
                        </div>
                        <div class="car-info">
                            <div class="car-header">
                                <h3 class="car-title"><?php echo $car['brand'] . ' ' . $car['model']; ?> (<?php echo $car['year']; ?>)</h3>
                                <div class="car-price">$<?php echo $car['price_per_day']; ?> <span>/ day</span></div>
                            </div>
                            <p class="car-location"><i class="fas fa-map-marker-alt"></i> <?php echo $car['city']; ?></p>
                            <div class="car-features">
                                <div class="feature"><i class="fas fa-car"></i> <?php echo $car['car_type']; ?></div>
                                <div class="feature"><i class="fas fa-gas-pump"></i> <?php echo $car['fuel_type']; ?></div>
                                <div class="feature"><i class="fas fa-cog"></i> <?php echo $car['transmission']; ?></div>
                                <div class="feature"><i class="fas fa-user"></i> <?php echo $car['seats']; ?> Seats</div>
                                <div class="feature"><i class="fas fa-palette"></i> <?php echo $car['color']; ?></div>
                            </div>
                            <p class="car-description"><?php echo substr($car['description'], 0, 150) . '...'; ?></p>
                            <div class="car-footer">
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
                                <div class="car-actions">
                                    <button class="btn btn-outline" onclick="location.href='car-details.php?id=<?php echo $car['car_id']; ?>'">View Details</button>
                                    <button class="btn btn-primary" onclick="location.href='booking.php?id=<?php echo $car['car_id']; ?><?php echo (!empty($pickup_date) && !empty($return_date)) ? '&pickup_date=' . $pickup_date . '&return_date=' . $return_date : ''; ?>'">Book Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <h3>No cars found</h3>
                    <p>Try adjusting your filters or search criteria to find available cars.</p>
                    <button class="btn btn-primary" onclick="resetFilters()">Reset Filters</button>
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
        // Function to update sort and submit form
        function updateSort(sortValue) {
            // Get current URL
            let currentUrl = new URL(window.location.href);
            
            // Update or add sort parameter
            currentUrl.searchParams.set('sort', sortValue);
            
            // Redirect to new URL
            window.location.href = currentUrl.toString();
        }
        
        // Function to reset all filters
        function resetFilters() {
            // Preserve pickup and return dates if they exist
            let pickupDate = "<?php echo $pickup_date; ?>";
            let returnDate = "<?php echo $return_date; ?>";
            
            let url = 'cars.php';
            
            if (pickupDate && returnDate) {
                url += '?pickup_date=' + pickupDate + '&return_date=' + returnDate;
            }
            
            window.location.href = url;
        }
    </script>
</body>
</html>
