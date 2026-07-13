<?php include('includes/header.php')?>
<?php
require_once 'config/db.php';

// Get all restaurants with their menu items
$stmt = $pdo->prepare("
    SELECT
        r.restaurant_id,
        r.name as restaurant_name,
        r.logo,
        r.cuisine_type,
        r.address,
        r.phone,
        m.item_id,
        m.name as item_name,
        m.description,
        m.price,
        m.image as item_image,
        m.category,
        m.is_available
    FROM restaurants r
    LEFT JOIN menu_items m ON r.restaurant_id = m.restaurant_id
    WHERE r.status = 'active' AND m.is_available = 1
    ORDER BY r.name, m.category, m.name
");
$stmt->execute();
$results = $stmt->fetchAll();

// Organize data by restaurant
$restaurants = [];
foreach ($results as $row) {
    $restaurant_id = $row['restaurant_id'];
    
    if (!isset($restaurants[$restaurant_id])) {
        $restaurants[$restaurant_id] = [
            'name' => $row['restaurant_name'],
            'logo' => $row['logo'],
            'cuisine_type' => $row['cuisine_type'],
            'address' => $row['address'],
            'phone' => $row['phone'],
            'items' => []
        ];
    }
    
    // Add menu item if it exists
    if ($row['item_id']) {
        $restaurants[$restaurant_id]['items'][] = [
            'name' => $row['item_name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'image' => $row['item_image'],
            'category' => $row['category']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Restaurant Menus</title>
    <style>
     
        
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
       
        
        .page-title {
            text-align: center;
            color: #ff6a00;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }
        
        .restaurant-section {
            background: white;
            border-radius: 15px;
            margin-bottom: 40px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .restaurant-header {
            background: linear-gradient(135deg, #ff6a00 0%, #ff8c42 100%);
            color: white;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .restaurant-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        
        .restaurant-info {
            flex: 1;
        }
        
        .restaurant-name {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .restaurant-cuisine {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .restaurant-details {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .menu-container {
            padding: 25px;
        }
        
        .category-title {
            color: #ff6a00;
            font-size: 1.4rem;
            margin: 20px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff6a00;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .menu-item {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e9ecef;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(255,106,0,0.15);
        }
        
        .item-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .item-details {
            padding: 15px;
        }
        
        .item-name {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }
        
        .item-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .item-price {
            color: #ff6a00;
            font-size: 1.3rem;
            font-weight: bold;
        }
        
        .no-items {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .restaurant-header {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            
            .restaurant-logo {
                width: 80px;
                height: 80px;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title"> All Restaurant Menus</h1>
        
        <?php if (empty($restaurants)): ?>
            <div class="no-restaurants" style="text-align: center; padding: 40px;">
                <h2>No restaurants available</h2>
                <p>There are no restaurants with active menus at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($restaurants as $restaurant_id => $restaurant): ?>
                <div class="restaurant-section">
                    <!-- Restaurant Header -->
                    <div class="restaurant-header">
                        <?php if ($restaurant['logo']): ?>
                            <img src="assets/images/<?php echo htmlspecialchars($restaurant['logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($restaurant['name']); ?> Logo" 
                                 class="restaurant-logo">
                        <?php else: ?>
                            <div class="restaurant-logo" style="background: #fff; display: flex; align-items: center; justify-content: center; color: #ff6a00;">
                                <i style="font-size: 2rem;">🍴</i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="restaurant-info">
                            <h2 class="restaurant-name"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
                            <div class="restaurant-cuisine"><?php echo htmlspecialchars($restaurant['cuisine_type']); ?></div>
                            <div class="restaurant-details">
                                <?php echo htmlspecialchars($restaurant['address']); ?> | 
                                <?php echo htmlspecialchars($restaurant['phone']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Restaurant Menu -->
                    <div class="menu-container">
                        <?php if (!empty($restaurant['items'])): ?>
                            <?php 
                            // Group items by category
                            $items_by_category = [];
                            foreach ($restaurant['items'] as $item) {
                                $category = $item['category'] ?: 'Menu Items';
                                if (!isset($items_by_category[$category])) {
                                    $items_by_category[$category] = [];
                                }
                                $items_by_category[$category][] = $item;
                            }
                            ?>
                            
                            <?php foreach ($items_by_category as $category => $items): ?>
                                <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                                <div class="menu-grid">
                                    <?php foreach ($items as $item): ?>
                                        <div class="menu-item">
                                            <?php if ($item['image']): ?>
                                                <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="item-image">
                                            <?php else: ?>
                                                <div class="item-image" style="background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #999;">
                                                    <i style="font-size: 3rem;">🍕</i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="item-details">
                                                <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                                
                                                <?php if ($item['description']): ?>
                                                    <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="item-price"><?php echo number_format($item['price'], 2); ?> MMK</div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            
                        <?php else: ?>
                            <div class="no-items">
                                <p>No menu items available for this restaurant.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>