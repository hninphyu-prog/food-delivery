<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once '../../config/db.php';
require_once '../../functions.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'customer')) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if this is the first time user is seeing the dashboard after login
$showIntro = false;
if (!isset($_SESSION['intro_shown'])) {
    $_SESSION['intro_shown'] = true;
    $showIntro = true;
}

function getUser($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT name, address FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getUserFavorites($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT restaurant_id FROM user_favorites WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$user = getUser($pdo, $user_id);
$user_favorites = getUserFavorites($pdo, $user_id);

// Handle page routing
$page = $_GET['page'] ?? 'restaurants';

// Get filters
$q = trim($_GET['q'] ?? '');
$selectedCuisine = trim($_GET['cuisine'] ?? '');

// Check if user has selected location
$hasLocation = isset($_SESSION['user_lat']) && isset($_SESSION['user_lng']);
$userLat = $_SESSION['user_lat'] ?? null;
$userLng = $_SESSION['user_lng'] ?? null;

// Fetch distinct cuisines (only for restaurants within range if location is set)
if ($hasLocation) {
    $cuisineStmt = $pdo->prepare("
        SELECT DISTINCT r.cuisine_type 
        FROM restaurants r
        WHERE r.status IN ('active','closed') 
          AND r.cuisine_type IS NOT NULL 
          AND r.cuisine_type <> '' 
          AND (6371 * acos(cos(radians(?)) * cos(radians(r.lat)) * cos(radians(r.lng) - radians(?)) + sin(radians(?)) * sin(radians(r.lat)))) <= 3
        ORDER BY r.cuisine_type ASC
    ");
    $cuisineStmt->execute([$userLat, $userLng, $userLat]);
    $cuisines = $cuisineStmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $cuisines = $pdo->query("
        SELECT DISTINCT cuisine_type 
        FROM restaurants 
        WHERE status IN ('active','closed') 
          AND cuisine_type IS NOT NULL 
          AND cuisine_type <> '' 
        ORDER BY cuisine_type ASC
    ")->fetchAll(PDO::FETCH_COLUMN);
}

/// Fetch restaurants based on location and filters
$restaurants = [];
if ($hasLocation) {
    // Simple approach - get all restaurants within 3km first, then filter in PHP if needed
    $stmt = $pdo->prepare("
        SELECT r.*, 
               (6371 * acos(cos(radians(?)) * cos(radians(r.lat)) * cos(radians(r.lng) - radians(?)) + sin(radians(?)) * sin(radians(r.lat)))) AS distance
        FROM restaurants r
        WHERE r.status IN ('active','closed')
          AND (6371 * acos(cos(radians(?)) * cos(radians(r.lat)) * cos(radians(r.lng) - radians(?)) + sin(radians(?)) * sin(radians(r.lat)))) <= 3
        ORDER BY distance ASC, r.name ASC
    ");
    $stmt->execute([$userLat, $userLng, $userLat, $userLat, $userLng, $userLat]);
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // search filter in PHP
    if ($q !== '') {
        $searchTerm = strtolower($q);
        $restaurants = array_filter($restaurants, function ($restaurant) use ($searchTerm) {
            return strpos(strtolower($restaurant['name']), $searchTerm) !== false ||
                strpos(strtolower($restaurant['cuisine_type']), $searchTerm) !== false;
        });
    }

    // Apply cuisine filter in PHP
    if ($selectedCuisine !== '') {
        $restaurants = array_filter($restaurants, function ($restaurant) use ($selectedCuisine) {
            return $restaurant['cuisine_type'] === $selectedCuisine;
        });
    }
}
// After fetching restaurants, add review data for each restaurant
foreach ($restaurants as &$res) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as avg_rating
        FROM reviews 
        WHERE restaurant_id = ? AND status = 'visible'
    ");
    $stmt->execute([$res['restaurant_id']]);
    $review_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $res['total_reviews'] = $review_stats['total_reviews'] ?? 0;
    $res['avg_rating'] = $review_stats['avg_rating'] ? round($review_stats['avg_rating'], 1) : 0;
}
unset($res);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        /* Splash Screen Styles */
        #splashScreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10001;
            /* Start visible if showing intro, hidden if not */
            opacity: <?php echo $showIntro ? '1' : '0'; ?>;
            visibility: <?php echo $showIntro ? 'visible' : 'hidden'; ?>;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        /* Hide main content initially when showing intro */
        <?php if ($showIntro): ?>body>*:not(#splashScreen) {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        body.intro-done>*:not(#splashScreen) {
            opacity: 1;
        }

        <?php endif; ?>.splash-content {
            text-align: center;
            color: white;
        }

        /* Logo Animation */
        .logo-animation {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .logo-icon {
            font-size: 80px;
            animation: bounce 1.5s infinite, rotate 2s ease-in-out;
        }

        .logo-text {
            font-size: 48px;
            font-weight: bold;
            font-family: 'Arial', sans-serif;
        }

        .logo-text .amp {
            color: #fff;
            font-weight: bold;
        }

        .splash-tagline {
            font-size: 18px;
            opacity: 0.9;
            margin-top: 10px;
            animation: fadeInUp 0.8s ease 0.3s both;
        }

        /* Animations */
        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes rotate {
            0% {
                transform: rotateY(0deg);
            }

            50% {
                transform: rotateY(180deg);
            }

            100% {
                transform: rotateY(360deg);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 102, 0, 0.4);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(255, 102, 0, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 102, 0, 0);
            }
        }

        .custom-marker {
            background: none !important;
            border: none !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .leaflet-marker-draggable {
            cursor: move !important;
        }

        /* Location Prompt Styles - FIXED */
        .location-prompt-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
            width: 100%;
            padding: 20px;
        }

        .location-prompt {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .location-prompt i {
            font-size: 64px;
            color: #ff6600;
            animation: pulse 2s infinite;
            margin-bottom: 20px;
        }

        .location-prompt h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .location-prompt p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .find-restaurants-btn {
            background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
            display: inline-block;
            min-width: 200px;
        }

        .find-restaurants-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 102, 0, 0.4);
        }

        .location-info {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .location-info span {
            color: #28a745;
            font-weight: bold;
        }

        .change-location-btn {
            font-size: 25px;
            margin-right: 5px;
        }

        .location-row {
            display: flex;
            align-items: center;
            margin-bottom: 2px;
            animation: pulse 2s infinite;
            margin-right: 80px;
        }

        /* Map Modal Styles */
        #mapModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        #mapModal.visible {
            opacity: 1;
            visibility: visible;
        }

        .map-modal-content {
            background: white;
            border-radius: 20px;
            width: 95%;
            max-width: 800px;
            height: 90vh;
            max-height: 90vh;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .map-modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            text-align: center;
            flex-shrink: 0;
        }

        .map-modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .map-modal-header p {
            margin: 5px 0 0 0;
            color: #666;
        }

        .map-container {
            flex: 1;
            width: 100%;
            min-height: 400px;
        }

        .map-controls {
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
            flex-shrink: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .confirm-location-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .confirm-location-btn:hover:not(:disabled) {
            background: #218838;
            transform: translateY(-1px);
        }

        .confirm-location-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .location-details {
            text-align: left;
            flex: 1;
            margin-right: 20px;
        }

        .location-details strong {
            color: #333;
        }

        .close-map-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            z-index: 10001;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-map-modal:hover {
            background: #f8f9fa;
            color: #333;
        }

        .distance-badge {
            background: #ff6600;
            color: white;
            padding: 2px 8px;
            margin: 10px;
            font-weight: bold;
            float: right;
            border-radius: 20px;
            font-size: 14px;

        }

        .restaurant-card.closed {
            opacity: 0.7;
            position: relative;
        }

        .restaurant-card.closed::after {
            content: "CLOSED";
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 3;
        }

        .closed-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #dc3545;
            color: #fff;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            z-index: 3;
            display: block !important;
        }

        .restaurant-card.closed {
            opacity: 0.7;
            position: relative;
        }

        .restaurant-card.closed::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            z-index: 1;
            pointer-events: none;
        }

        /* Favorite Button Styles */
        .favorite-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 3;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .favorite-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        .favorite-btn i {
            font-size: 18px;
            color: #ccc;
            transition: all 0.3s ease;
        }

        .favorite-btn.favorited i {
            color: #ff6600;
        }

        .favorite-btn:hover i {
            color: #ff6600;
        }

        .restaurant-card-wrapper {
            position: relative;
        }

        .restaurant-card {
            position: relative;
        }

        .distance-badge {
            background: #ff6600;
            color: white;
            padding: 2px 8px;
            margin: 10px;
            font-weight: bold;
            float: right;
            border-radius: 20px;
            font-size: 14px;
        }

        /* Favorite Filter Button Styles */
        .favorite-filter-btn {
            background-color: rgb(255, 102, 0);
            border: 2px solid rgb(255, 102, 0);
            color: rgb(255, 102, 0);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 10px;
            transition: all 0.3s ease;
            position: relative;
            animation: pulse 2s infinite;
        }

        .favorite-filter-btn i {
            font-size: 28px;
            color: white;
        }

        .favorite-filter-btn i:hover {
            color: black;
        }

        .favorite-filter-btn:hover {
            background: rgb(255, 102, 0);
            color: black;
            transform: scale(1.1);
        }

        .favorite-filter-btn.active {
            background: rgb(255, 102, 0);
            color: white;
        }

        .favorite-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff6600;
            color: white;
            font-size: 12px;
            font-weight: bold;
            min-width: 10px;
            height: 14px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: 2px solid white;
            animation: pulse 2s infinite;
        }

        /* Favorite Modal Styles */
        .favorite-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .favorite-modal.visible {
            opacity: 1;
            visibility: visible;
        }

        .favorite-modal-content {
            background: white;
            border-radius: 20px;
            width: 95%;
            max-width: 600px;
            height: 70vh;
            max-height: 70vh;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .favorite-modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            text-align: center;
            flex-shrink: 0;
        }

        .favorite-modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .favorite-modal-header p {
            margin: 5px 0 0 0;
            color: #666;
        }

        .favorite-restaurants-list {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .favorite-restaurant-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            padding: 10px;
            gap: 15px;
            border: 1px solid #f0f0f0;
        }

        .favorite-restaurant-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            border-color: #ff6600;
        }

        .favorite-card-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
            border: 2px solid #f0f0f0;
        }

        .favorite-card-content {
            flex: 1;
            padding: 0;
        }

        .favorite-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            line-height: 1.3;
        }

        .favorite-card-cuisine {
            margin: 4px 0 0 0;
            color: #666;
            font-size: 13px;
        }

        .no-favorites {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .no-favorites i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .no-favorites h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
        }

        .no-favorites p {
            margin: 0;
            font-size: 14px;
            line-height: 1.4;
        }

        .close-favorite-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            z-index: 10001;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-favorite-modal:hover {
            background: #f8f9fa;
            color: #333;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .loading-spinner i {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .loading-spinner p {
            margin: 0;
            font-size: 14px;
        }

        .favorite-restaurant-card-wrapper {
            position: relative;
        }

        .remove-favorite-btn {
            position: absolute;
            top: 20px;
            right: 15px;
            background: rgb(255, 102, 0);
            color: red;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 12px;

            transition: all 0.3s ease;
            z-index: 2;
        }

        .remove-favorite-btn i {
            font-size: 25px;
        }

        .remove-favorite-btn:hover {
            background: #e55a00;
            transform: scale(1.1);
        }

        .favorite-restaurant-card-wrapper:hover .remove-favorite-btn {
            opacity: 1;
        }

        .remove-favorite-btn:hover {
            background: rgb(255, 102, 0);
            transform: scale(1.1);
        }

        /* Cart Icon Styles */
        .cart-icon-container {
            position: relative;
            margin-right: 20px;
        }

        .cart-icon-link {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            font-size: 24px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .cart-icon-link:hover {
            color: black;
            transform: scale(1.1);
        }

        .cart-count-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff6600;
            color: white;
            font-size: 12px;
            font-weight: bold;
            min-width: 10px;
            height: 14px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: 2px solid white;
            animation: pulse 2s infinite;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .cart-icon-container {
                margin-right: 15px;
            }

            .cart-icon-link {
                width: 35px;
                height: 35px;
                font-size: 20px;
            }

            .cart-count-badge {
                font-size: 10px;
                min-width: 16px;
                height: 16px;
                border-radius: 8px;
            }

            .logo-icon {
                font-size: 60px;
            }

            .logo-text {
                font-size: 36px;
            }

            .splash-tagline {
                font-size: 16px;
            }

            .location-prompt {
                padding: 40px 20px;
                margin: 20px;
            }

            .location-prompt h2 {
                font-size: 20px;
            }

            .location-prompt p {
                font-size: 14px;
            }

            .find-restaurants-btn {
                padding: 10px 20px;
                font-size: 16px;
                min-width: 180px;
            }
        }
    </style>
</head>

<body style="background: #f7f7f7; <?php if ($showIntro) echo 'class="intro-active"'; ?>">

    <!-- Intro Splash Screen - START VISIBLE if showing intro -->
    <?php if ($showIntro): ?>
        <div id="splashScreen" style="opacity: 1; visibility: visible;">
            <div class="splash-content">
                <!-- Animated Logo -->
                <div class="logo-animation">
                    <div class="logo-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="logo-text">
                        <span>Food<span class="amp">&amp;</span>Me</span>
                    </div>
                </div>
                <p class="splash-tagline">Delivering happiness to your doorstep</p>
            </div>
        </div>
    <?php else: ?>
        <div id="splashScreen" style="display: none;"></div>
    <?php endif; ?>

    <!-- Hide main content initially when showing intro -->
    <?php if ($showIntro): ?>
        <div id="mainContent" style="opacity: 0;">
        <?php endif; ?>

        <header class="res-sticky-dash">
            <div class="container header-inner">
                <div class="brand">
                    <div class="customer_brand__logo">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <span class="customer_brand__name">Food<span>&amp;</span>Me</span>
                </div>

                <!-- SEARCH FORM - Only show if location is set -->
                <?php if ($hasLocation): ?>
                    <div class="search-row" aria-label="Search restaurants and cuisines">
                        <form class="search-form" action="dashboard.php" method="get" role="search" autocomplete="off">
                            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                            <?php if ($selectedCuisine !== ''): ?>
                                <input type="hidden" name="cuisine" value="<?= htmlspecialchars($selectedCuisine) ?>">
                            <?php endif; ?>
                            <input
                                type="search"
                                name="q"
                                class="search-input"
                                placeholder="Search restaurants......"
                                value="<?= htmlspecialchars($q) ?>">
                            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>

                        <div class="search-suggestions" style="display:none"></div>
                    </div>
                <?php endif; ?>

                <div class="location-row">
                    <i class="fas fa-map-marker-alt change-location-btn"></i>
                    <button onclick="openMapModal()" style="border: none;">
                        <div style="background: rgb(255, 102, 0);font-size: 18px;font-weight: bold;color: white;">Change Location</div>
                    </button>
                </div>

                <div class="favorite-row">
                    <button class="favorite-filter-btn" id="favoriteFilterBtn" title="Show Favorite Restaurants">
                        <i class="fa-solid fa-heart"></i>
                        <?php if (count($user_favorites) > 0): ?>
                            <span class="favorite-count"><?= count($user_favorites) ?></span>
                        <?php endif; ?>
                    </button>
                </div>
                <!-- CART ICON WITH BADGE -->
                <?php
                // Check if user has active saved carts
                $cart_item_count = 0;

                // First, check if there's an active cart in session
                if (isset($_SESSION['cart']) && isset($_SESSION['cart']['items'])) {
                    $cart_item_count = array_sum(array_column($_SESSION['cart']['items'], 'quantity'));
                } else {
                    // If no active cart in session, check saved carts from database
                    try {
                        // This query should match the one in cart_history.php
                        $stmt = $pdo->prepare("
                    SELECT sc.cart_data 
                    FROM saved_carts sc 
                    WHERE sc.user_id = ? 
                      AND (sc.status = 'active' OR sc.status IS NULL)
                      AND (sc.order_id IS NULL OR sc.order_id = 0)
                ");
                        $stmt->execute([$user_id]);
                        $active_carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($active_carts as $cart) {
                            $cart_data = json_decode($cart['cart_data'], true);
                            if (isset($cart_data['items'])) {
                                foreach ($cart_data['items'] as $item) {
                                    $cart_item_count += $item['qty'] ?? $item['quantity'] ?? 0;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // If there's an error, just show cart icon without count
                        $cart_item_count = 0;
                    }
                }
                ?>

                <div class="cart-icon-container">
                    <a href="cart_history.php" class="cart-icon-link" title="My Saved Carts">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_item_count > 0): ?>
                            <span class="cart-count-badge"><?= $cart_item_count ?></span>
                        <?php endif; ?>
                    </a>
                </div>


                <!-- USER MENU -->
                <div class="user-menu">
                    <div class="user-menu-toggle" id="userMenuToggle">
                        <span><?= htmlspecialchars(explode(' ', $user['name'] ?? 'User')[0]) ?></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content" id="dropdownContent">
                        <ul>
                            <li><a href="dashboard.php?page=profile"><i class="fa-regular fa-user"></i> Profile</a></li>
                            <li><a href="dashboard.php?page=orders"><i class="fa-solid fa-receipt"></i> Reordering & Reviews</a></li>
                            <li><a href="dashboard.php?page=help"><i class="fa-regular fa-circle-question"></i> Help Center</a></li>
                            <?php if ($page !== 'restaurants' && $hasLocation): ?>
                                <li><a href="dashboard.php?page=restaurants"><i class="fa-solid fa-arrow-left"></i> Back To Restaurants</a></li>
                            <?php endif; ?>
                            <li class="divider"><a href="../../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <?php
            if ($page === 'restaurants') {
                if (!$hasLocation): ?>
                    <!-- Location Prompt - NOW PROPERLY CENTERED -->
                    <div class="location-prompt-container">
                        <div class="location-prompt">
                            <i class="fas fa-map-marker-alt"></i>
                            <h2>Discover Restaurants Near You</h2>
                            <p>Set your delivery location to unlock amazing restaurants within 3km. Get personalized recommendations and fast delivery!</p>
                            <button class="find-restaurants-btn" onclick="openMapModal()">
                                Find Nearby Restaurants
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Show restaurants when location is set -->
                    <?php if ($q !== ''): ?>
                        <div class="result">
                            <p>Showing results for <strong><?= htmlspecialchars($q) ?></strong>. <a href="dashboard.php">Clear</a></p>
                        </div>
                    <?php endif; ?>

                    <div style="color: #ff8000;margin-top:10px;">
                        <h2>Restaurants Near You</h2>
                    </div>

                    <!-- Cuisine filter -->
                    <?php if ($cuisines): ?>
                        <div class="cuisine-carousel">
                            <div class="cuisine-filter">
                                <a href="dashboard.php?page=restaurants" class="cuisine-btn reset">All</a>
                                <?php foreach ($cuisines as $cuisine): ?>
                                    <?php $active = ($cuisine === $selectedCuisine) ? 'active' : ''; ?>
                                    <a href="dashboard.php?page=restaurants&cuisine=<?= urlencode($cuisine) ?>" class="cuisine-btn <?= $active ?>">
                                        <?= htmlspecialchars($cuisine) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Restaurant List -->
                    <div class="restaurant-list">
                        <?php if (count($restaurants) === 0): ?>
                            <div style="text-align: center; padding: 40px;">
                                <i class="fas fa-utensils" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                <h3>No restaurants found nearby</h3>
                                <p>No restaurants found within 3km of your location.</p>
                                <button class="find-restaurants-btn" onclick="openMapModal()" style="margin-top: 15px;">
                                    Try Different Location
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($restaurants as $res): ?>
                                <?php
                                $logo = htmlspecialchars($res['logo'] ?? 'default_restaurant.png');
                                $lat = htmlspecialchars($res['lat'] ?? 0);
                                $lng = htmlspecialchars($res['lng'] ?? 0);
                                $prep = htmlspecialchars($res['preparation_time'] ?? 15);
                                $distance = isset($res['distance']) ? round($res['distance'], 1) : null;
                                $is_favorite = in_array($res['restaurant_id'], $user_favorites);
                                $is_closed = ($res['status'] ?? 'active') !== 'active';
                                ?>


                                <a href="restaurants.php?id=<?= $res['restaurant_id'] ?>&lat=<?= $lat ?>&lng=<?= $lng ?>&prep=<?= $prep ?>"
                                    class="restaurant-card-link restaurant-card-dynamic"
                                    data-lat="<?= $lat ?>"
                                    data-lng="<?= $lng ?>"
                                    data-prep-time="<?= $prep ?>">

                                    <!-- Favorite Heart Button -->
                                    <div class="restaurant-card <?= $is_closed ? 'closed' : '' ?>">
                                        <div class="card-image" style="background-image: url('../../assets/images/<?= $logo ?>');">

                                            <?php if ($distance): ?>
                                                <span class="distance-badge"><?= $distance ?> km</span>
                                            <?php endif; ?>
                                            <button class="favorite-btn <?= $is_favorite ? 'favorited' : '' ?>"
                                                data-restaurant-id="<?= $res['restaurant_id'] ?>">
                                                <i class="fa-<?= $is_favorite ? 'solid' : 'regular' ?> fa-heart"></i>
                                            </button>
                                        </div>
                                        <div class="card-content">
                                            <h3 class="card-title"><?= htmlspecialchars($res['name']) ?></h3>
                                            <p class="card-cuisine">
                                                <?= htmlspecialchars($res['cuisine_type']) ?>

                                                <?php if ($res['total_reviews'] > 0): ?>
                                                    <span class="rating-badge">★ <?= $res['avg_rating'] ?> (<?= $res['total_reviews'] ?>)</span>
                                                <?php endif; ?>
                                            </p>
                                            <div class="card-details">
                                                <span class="detail-item"><i class="fa-solid fa-clock"></i> <span class="dynamic-time">...</span></span>
                                                <span class="motor"><i class="fa-solid fa-motorcycle"></i> <span class="dynamic-fee">...</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
            <?php endif;
            } else {
                // Handle other pages (profile, orders, help)
                switch ($page) {
                    case 'profile':
                        include 'profile.php';
                        break;
                    case 'orders':
                        include 'orders.php';
                        break;
                    case 'help':
                        include 'help_center.php';
                        break;
                }
            }
            ?>
        </main>

        <!-- Map Modal -->
        <div id="mapModal" class="modal-backdrop">
            <div class="map-modal-content">
                <button class="close-map-modal" onclick="closeMapModal()">&times;</button>
                <div class="map-modal-header">
                    <h3>Choose Your Delivery Location</h3>
                    <p>Click on the map to set your location or use your current location</p>
                </div>
                <div class="map-container" id="mapContainer"></div>
                <div class="map-controls">
                    <div class="location-details">
                        <strong>Selected Location:</strong>
                        <span id="selectedLocationText">Click on the map to select a location</span>
                    </div>
                    <div>
                        <button class="confirm-location-btn" onclick="confirmLocation()" id="confirmLocationBtn" disabled>
                            <i class="fas fa-check"></i> Confirm Location
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Favorite Restaurants Modal -->
        <div id="favoriteModal" class="favorite-modal">
            <div class="favorite-modal-content">
                <button class="close-favorite-modal" onclick="closeFavoriteModal()">&times;</button>
                <div class="favorite-modal-header">
                    <h3>Your Favorites</h3>
                    <p>Quick access to your favorite restaurants</p>
                </div>
                <div class="favorite-restaurants-list" id="favoriteRestaurantsList">
                    <!-- Favorite restaurants will be loaded here via AJAX -->
                </div>
            </div>
        </div>

        <?php if ($showIntro): ?>
        </div> <!-- Close mainContent div -->
    <?php endif; ?>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Audio element for intro sound - PRELOAD IMMEDIATELY
        const introSound = new Audio('../../assets/sound/intro.mp3');
        introSound.volume = 0.3;
        introSound.preload = 'auto';

        // Map variables
        let map;
        let marker;
        let selectedLat = null;
        let selectedLng = null;
        let rangeCircle = null; // Track the range circle

        // Show splash screen - ONLY ONCE after login with NO DELAY
        function showSplashScreen() {
            const splashScreen = document.getElementById('splashScreen');
            const mainContent = document.getElementById('mainContent');

            // Only show if it's visible (first time login)
            if (splashScreen && splashScreen.style.opacity === '1') {
                // Play sound immediately - NO DELAY
                setTimeout(() => {
                    introSound.currentTime = 0; // Reset to start
                    introSound.play().catch(e => console.log('Audio play failed:', e));
                }, 0);

                // Hide after animation completes (2 seconds total)
                setTimeout(() => {
                    splashScreen.style.opacity = '0';
                    splashScreen.style.visibility = 'hidden';

                    // Show main content with fade in
                    if (mainContent) {
                        mainContent.style.opacity = '1';
                        document.body.classList.add('intro-done');
                    }

                    // Show location prompt if needed (only if no location set)
                    if (!<?php echo $hasLocation ? 'true' : 'false'; ?>) {
                        // Small delay before showing location modal
                        setTimeout(() => {
                            openMapModal();
                        }, 300);
                    }
                }, 2000); // Show for 2 seconds
            }
        }

        function openMapModal() {
            // Play a subtle sound when opening location modal
            const clickSound = new Audio('../../assets/sound/click.mp3');
            clickSound.volume = 0.2;
            clickSound.currentTime = 0;
            clickSound.play().catch(e => console.log('Sound play failed'));

            document.getElementById('mapModal').classList.add('visible');
            document.body.style.overflow = 'hidden';
            initializeMap();
        }

        function closeMapModal() {
            document.getElementById('mapModal').classList.remove('visible');
            document.body.style.overflow = '';

            // Clean up map resources
            if (map) {
                map.remove();
                map = null;
            }
            marker = null;
            rangeCircle = null;
        }

        function initializeMap() {
            // Initialize map
            map = L.map('mapContainer').setView([16.8661, 96.1951], 13); // Default to Yangon center

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add click event to map
            map.on('click', function(e) {
                setLocation(e.latlng.lat, e.latlng.lng);
            });

            // Try to get current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;

                        // Center map on user's location
                        map.setView([userLat, userLng], 15);

                        // Set location automatically
                        setLocation(userLat, userLng);
                    },
                    function(error) {
                        console.log('Geolocation error:', error);
                        // Use default location (Yangon)
                        map.setView([16.8661, 96.1951], 13);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    }
                );
            }
        }

        function setLocation(lat, lng) {
            selectedLat = lat;
            selectedLng = lng;

            // Remove existing marker
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }

            // Remove existing range circle
            if (rangeCircle) {
                map.removeLayer(rangeCircle);
                rangeCircle = null;
            }

            // Create custom icon
            const customIcon = L.divIcon({
                html: '<i class="fas fa-map-marker-alt" style="font-size: 32px; color: #ff6600;"></i>',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                className: 'custom-marker'
            });

            // Add new draggable marker
            marker = L.marker([lat, lng], {
                icon: customIcon,
                draggable: true
            }).addTo(map);

            // Add circle showing range - ONLY ONE CIRCLE
            rangeCircle = L.circle([lat, lng], {
                color: '#ff6600',
                fillColor: '#ff6600',
                fillOpacity: 0.1,
                radius: 3000
            }).addTo(map);

            // Update location when marker is dragged
            marker.on('dragend', function(event) {
                const newLatLng = event.target.getLatLng();
                selectedLat = newLatLng.lat;
                selectedLng = newLatLng.lng;

                // Move the circle with the marker
                if (rangeCircle) {
                    rangeCircle.setLatLng(newLatLng);
                }
                updateLocationText();
            });

            // Update location text
            updateLocationText();

            // Enable confirm button
            document.getElementById('confirmLocationBtn').disabled = false;

            // Center map on selected location
            map.setView([lat, lng], 15);
        }

        function updateLocationText() {
            document.getElementById('selectedLocationText').textContent =
                `Lat: ${selectedLat.toFixed(6)}, Lng: ${selectedLng.toFixed(6)}`;
        }

        function confirmLocation() {
            if (selectedLat && selectedLng) {
                // Send location to server via AJAX
                const formData = new FormData();
                formData.append('lat', selectedLat);
                formData.append('lng', selectedLng);
                formData.append('action', 'set_location');

                fetch('location_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Reload to show restaurants
                        } else {
                            alert('Error setting location');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error setting location');
                    });
            }
        }

        // Close modal when clicking outside
        document.getElementById('mapModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMapModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMapModal();
            }
        });

        // START THE INTRO IMMEDIATELY ON PAGE LOAD - NO DELAY
        document.addEventListener('DOMContentLoaded', function() {
            // Start splash screen immediately - NO DELAY
            showSplashScreen();

            // Add event listener for manual location change
            document.querySelector('.change-location-btn').addEventListener('click', function() {
                openMapModal();
            });

            const userMenuToggle = document.getElementById('userMenuToggle');
            const dropdownContent = document.getElementById('dropdownContent');
            const userMenu = document.querySelector('.user-menu');

            if (userMenuToggle) {
                userMenuToggle.addEventListener('click', function(event) {
                    event.stopPropagation();
                    dropdownContent.classList.toggle('show');
                    userMenu.classList.toggle('open');
                });

                window.addEventListener('click', function(event) {
                    if (!userMenu.contains(event.target)) {
                        dropdownContent.classList.remove('show');
                        userMenu.classList.remove('open');
                    }
                });
            }

            // Only run delivery calculations if location is set and restaurants are shown
            <?php if ($hasLocation && !empty($restaurants)): ?>

                function haversine(lat1, lon1, lat2, lon2) {
                    const R = 6371;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) ** 2 +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) ** 2;
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return R * c;
                }

                function formatTimeRange(minutes) {
                    const lower = Math.floor(minutes / 5) * 5;
                    const upper = lower + 10;
                    return `${lower}-${upper} min`;
                }

                function updateAllCardDetails(userLat, userLng) {
                    const restaurantCards = document.querySelectorAll('.restaurant-card-dynamic');
                    const averageSpeedKmh = 20;

                    restaurantCards.forEach(card => {
                        const resLat = parseFloat(card.dataset.lat);
                        const resLng = parseFloat(card.dataset.lng);
                        const prepTime = parseInt(card.dataset.prepTime);
                        const feeSpan = card.querySelector('.dynamic-fee');
                        const timeSpan = card.querySelector('.dynamic-time');

                        if (!isNaN(resLat) && !isNaN(resLng) && feeSpan && timeSpan) {
                            const distKm = haversine(userLat, userLng, resLat, resLng);
                            let fee = 0;

                            if (distKm <= 2) {
                                fee = 700;
                            } else if (distKm <= 4) {
                                fee = 1400;
                            } else if (distKm <= 6) {
                                fee = 2100;
                            } else {
                                fee = 2100 + Math.round((distKm - 6) * 500);
                            }

                            feeSpan.innerText = fee.toLocaleString() + " MMK";

                            const travelTimeMinutes = (distKm / averageSpeedKmh) * 60;
                            const totalTimeMinutes = prepTime + travelTimeMinutes;
                            timeSpan.innerText = formatTimeRange(totalTimeMinutes);

                            // Store the calculated fee in a data attribute for the restaurant page to use
                            card.setAttribute('data-delivery-fee', fee);
                        }
                    });
                }

                function handleLocationError() {
                    document.querySelectorAll('.dynamic-fee').forEach(span => span.innerText = "Fee varies");
                    document.querySelectorAll('.dynamic-time').forEach(span => span.innerText = "30-40 min");
                }

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        pos => updateAllCardDetails(pos.coords.latitude, pos.coords.longitude),
                        handleLocationError, {
                            enableHighAccuracy: true
                        }
                    );
                } else {
                    handleLocationError();
                }
            <?php endif; ?>
        });

        // Function to store delivery fee when clicking on a restaurant card
        document.addEventListener('DOMContentLoaded', function() {
            const restaurantLinks = document.querySelectorAll('.restaurant-card-dynamic');

            restaurantLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const feeSpan = this.querySelector('.dynamic-fee');
                    if (feeSpan && feeSpan.innerText !== 'Fee varies' && feeSpan.innerText !== '...') {
                        const feeText = feeSpan.innerText.trim();
                        const feeMatch = feeText.match(/(\d+[\d,]*)/);
                        if (feeMatch) {
                            const deliveryFee = parseInt(feeMatch[1].replace(/,/g, ''));
                            // Store in sessionStorage to pass to restaurant page
                            sessionStorage.setItem('deliveryFee', deliveryFee);
                            sessionStorage.setItem('deliveryFeeDisplay', feeText);
                        }
                    }
                });
            });
        });
        // Favorite functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Handle favorite button clicks
            document.addEventListener('click', function(e) {
                if (e.target.closest('.favorite-btn')) {
                    e.preventDefault();
                    e.stopPropagation();

                    const favoriteBtn = e.target.closest('.favorite-btn');
                    const restaurantId = favoriteBtn.dataset.restaurantId;
                    const heartIcon = favoriteBtn.querySelector('i');

                    toggleFavorite(restaurantId, favoriteBtn, heartIcon);
                }
            });

            function toggleFavorite(restaurantId, button, icon) {
                const formData = new FormData();
                formData.append('restaurant_id', restaurantId);
                formData.append('action', 'toggle_favorite');

                fetch('favorite_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI
                            if (data.is_favorite) {
                                button.classList.add('favorited');
                                icon.classList.remove('fa-regular');
                                icon.classList.add('fa-solid');
                            } else {
                                button.classList.remove('favorited');
                                icon.classList.remove('fa-solid');
                                icon.classList.add('fa-regular');
                            }

                            // Update favorite count
                            updateFavoriteCount(data.favorite_count);

                            // Add animation effect
                            button.style.transform = 'scale(1.2)';
                            setTimeout(() => {
                                button.style.transform = 'scale(1)';
                            }, 300);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
        // Favorite Modal functionality
        function openFavoriteModal() {
            document.getElementById('favoriteModal').classList.add('visible');
            document.body.style.overflow = 'hidden';
            loadFavoriteRestaurants();
        }

        function closeFavoriteModal() {
            document.getElementById('favoriteModal').classList.remove('visible');
            document.body.style.overflow = '';
        }

        function loadFavoriteRestaurants() {
            const favoritesList = document.getElementById('favoriteRestaurantsList');

            // Show loading
            favoritesList.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading your favorite restaurants...</p>
        </div>
    `;

            fetch('get_favorites.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.restaurants.length > 0) {
                        let html = '';
                        data.restaurants.forEach(restaurant => {
                            const logo = restaurant.logo || 'default_restaurant.png';

                            html += `
    <div class="favorite-restaurant-card-wrapper" data-restaurant-id="${restaurant.restaurant_id}">
        <a href="restaurants.php?id=${restaurant.restaurant_id}&lat=${restaurant.lat}&lng=${restaurant.lng}&prep=${restaurant.preparation_time}" 
           class="favorite-restaurant-card">
            <div class="favorite-card-image" style="background-image: url('../../assets/images/${logo}');"></div>
            <div class="favorite-card-content">
                <h3 class="favorite-card-title">${restaurant.name}</h3>
                <p class="favorite-card-cuisine">${restaurant.cuisine_type}</p>
            </div>
        </a>
        <button class="remove-favorite-btn" title="Remove from favorites">
            <i class="fas fa-trash"></i>
        </button>
    </div>
`;
                        });
                        favoritesList.innerHTML = html;
                    } else {
                        favoritesList.innerHTML = `
                    <div class="no-favorites">
                        <i class="far fa-heart"></i>
                        <h3>No Favorite Restaurants</h3>
                        <p>Click the heart icon on restaurants to add them to your favorites</p>
                    </div>
                `;
                    }
                })
                .catch(error => {
                    console.error('Error loading favorites:', error);
                    favoritesList.innerHTML = `
                <div class="no-favorites">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Favorites</h3>
                    <p>Please try again later</p>
                </div>
            `;
                });
        }

        // Handle remove favorite button clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-favorite-btn')) {
                e.preventDefault();
                e.stopPropagation();

                const removeBtn = e.target.closest('.remove-favorite-btn');
                const cardWrapper = removeBtn.closest('.favorite-restaurant-card-wrapper');
                const restaurantId = cardWrapper.dataset.restaurantId;

                removeFavorite(restaurantId, cardWrapper);
            }
        });
        // Function to handle remove favorite
        function removeFavorite(restaurantId, cardWrapper) {
            const formData = new FormData();
            formData.append('restaurant_id', restaurantId);
            formData.append('action', 'remove_favorite');

            fetch('favorite_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the card with animation
                        cardWrapper.style.opacity = '0';
                        cardWrapper.style.transform = 'translateX(-20px)';

                        setTimeout(() => {
                            cardWrapper.remove();

                            // Reload favorites if empty
                            const remainingCards = document.querySelectorAll('.favorite-restaurant-card-wrapper');
                            if (remainingCards.length === 0) {
                                loadFavoriteRestaurants();
                            }

                            // Update favorite count
                            updateFavoriteCount(data.favorite_count);

                            // Also update any favorite heart buttons on the main page
                            const heartBtn = document.querySelector(`.favorite-btn[data-restaurant-id="${restaurantId}"]`);
                            if (heartBtn) {
                                heartBtn.classList.remove('favorited');
                                const heartIcon = heartBtn.querySelector('i');
                                heartIcon.classList.remove('fa-solid');
                                heartIcon.classList.add('fa-regular');
                            }
                        }, 300);
                    } else {
                        alert('Error removing favorite');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error removing favorite');
                });
        }

        // Update favorite count
        function updateFavoriteCount(count) {
    const favoriteCount = document.querySelector('.favorite-count');
    const favoriteBtn = document.querySelector('.favorite-filter-btn');
    
    if (count > 0) {
        if (!favoriteCount) {
            // Create badge if it doesn't exist
            const badge = document.createElement('span');
            badge.className = 'favorite-count';
            badge.textContent = count;
            favoriteBtn.appendChild(badge);
        } else {
            // Update existing badge
            favoriteCount.textContent = count;
        }
    } else {
        // Remove badge if count is 0
        if (favoriteCount) {
            favoriteCount.remove();
        }
    }
}

        // Close modal when clicking outside
        document.getElementById('favoriteModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeFavoriteModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('favoriteModal').classList.contains('visible')) {
                closeFavoriteModal();
            }
        });

        // Add event listener for favorite filter button
        document.addEventListener('DOMContentLoaded', function() {
            const favoriteFilterBtn = document.getElementById('favoriteFilterBtn');
            if (favoriteFilterBtn) {
                favoriteFilterBtn.addEventListener('click', openFavoriteModal);
            }
        });

        // Only refresh if user is not interacting with the page
        function checkRestaurantStatuses() {
            const restaurantCards = document.querySelectorAll('.restaurant-card-dynamic');
            const restaurantIds = Array.from(restaurantCards).map(card => {
                const url = new URL(card.href);
                return url.searchParams.get('id');
            });

            if (restaurantIds.length === 0) return;

            fetch('../../api/get_restaurant_statuses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        restaurant_ids: restaurantIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateRestaurantStatuses(data.statuses);
                    }
                })
                .catch(error => console.error('Error checking statuses:', error));
        }

        function updateRestaurantStatuses(statuses) {
            const restaurantCards = document.querySelectorAll('.restaurant-card-dynamic');

            restaurantCards.forEach(card => {
                const url = new URL(card.href);
                const restaurantId = url.searchParams.get('id');
                const status = statuses[restaurantId];

                if (status) {
                    const restaurantCard = card.querySelector('.restaurant-card');
                    const closedBadge = card.querySelector('.closed-badge');
                    const isClosed = status !== 'active';

                    // Update card appearance
                    restaurantCard.classList.toggle('closed', isClosed);

                    // Update or create closed badge
                    if (isClosed) {
                        if (!closedBadge) {
                            const cardImage = card.querySelector('.card-image');
                            const badge = document.createElement('span');
                            badge.className = 'closed-badge';
                            badge.textContent = 'CLOSED';
                            cardImage.appendChild(badge);
                        }
                    } else {
                        if (closedBadge) {
                            closedBadge.remove();
                        }
                    }
                }
            });
        }
    </script>
    <script>
        // Function to update cart count badge
        function updateCartCountBadge() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartBadge = document.querySelector('.cart-count-badge');
                    const cartIconLink = document.querySelector('.cart-icon-link');

                    if (data.count > 0) {
                        if (cartBadge) {
                            cartBadge.textContent = data.count;
                        } else {
                            // Create badge if it doesn't exist
                            const badge = document.createElement('span');
                            badge.className = 'cart-count-badge';
                            badge.textContent = data.count;
                            cartIconLink.appendChild(badge);
                        }
                    } else {
                        // Remove badge if count is 0
                        if (cartBadge) {
                            cartBadge.remove();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching cart count:', error);
                });
        }

        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCountBadge();

            // Update cart count every 10 seconds (optional)
            setInterval(updateCartCountBadge, 10000);
        });
    </script>

    <?php include('../../includes/footer.php') ?>
    <?php include 'track_status_bar.php'; ?>
</body>

</html>