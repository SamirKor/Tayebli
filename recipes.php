<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle AJAX request for recipes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_recipes'])) {
    header('Content-Type: application/json');
    
    $userID = $_SESSION['user_id'] ?? null;
    
    // Query all recipes with details
    $query = "SELECT RecipeID as id, Rtitle as title, Description as description, 
                     ImagePath as image, CookTime as cookTime, Serving as servings, 
                     Difficulty as difficulty, Category as category
              FROM recipes";
    
    $result = mysqli_query($connection, $query);
    
    $recipes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $recipeID = (int)$row['id'];
        $reviews = getReviews($connection, $recipeID);
        $avgRating = calculateAverageRating($reviews, $recipeID);
        
        // Check if user has favorited this recipe
        $isFavorite = false;
        if ($userID) {
            $favResult = mysqli_query($connection, "SELECT * FROM favorites WHERE UserID = $userID AND RecipeID = $recipeID");
            $isFavorite = mysqli_num_rows($favResult) > 0;
        }
        
        $row['rating'] = (float)$avgRating;
        $row['isFavorite'] = $isFavorite;
        $row['cookTime'] = $row['cookTime'] . ' min';
        $row['servings'] = (int)$row['servings'];
        $recipes[] = $row;
    }
    
    echo json_encode(['success' => true, 'recipes' => $recipes]);
    exit();
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    header('Content-Type: application/json');
    
    $userID = $_SESSION['user_id'] ?? null;
    $recipeID = (int) ($_POST['recipe_id'] ?? 0);

    if (!$userID) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    if ($recipeID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid recipe ID']);
        exit();
    }

    // Check if already favorited
    $result = mysqli_query($connection, "SELECT * FROM favorites WHERE UserID = $userID AND RecipeID = $recipeID");
    
    if (mysqli_num_rows($result) > 0) {
        // Remove from favorites
        $stmt = $connection->prepare("DELETE FROM favorites WHERE UserID = ? AND RecipeID = ?");
        $stmt->bind_param("ii", $userID, $recipeID);
        $stmt->execute();
        $stmt->close();
    } else {
        // Add to favorites
        $stmt = $connection->prepare("INSERT INTO favorites (UserID, RecipeID) VALUES (?, ?)");
        $stmt->bind_param("ii", $userID, $recipeID);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode(['success' => true]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>TAYEBLI - Find Recipes</title>
    <link rel="stylesheet" href="CSS/recipes.css">
</head>
<body>
    <!-- Navigation -->
    <div class="navbar">
        <div class="logo">
            <a href="index.php" style="text-decoration: none;"> <!--sends to the home page-->
                <svg class="chef-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                    <line x1="6" y1="17" x2="18" y2="17"/>
                </svg>
                <p style="color: black; font-size: 25px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                    TAYEBLI </p>
            </a>

        </div>

        <div class="nav-buttons">
            <button  id="homebtn"><i class="fa-solid fa-house"></i> Home</button>
            <button class="active" id="findbtn"><i class="fa-solid fa-magnifying-glass"></i> Recipes</button>
            <button id="Suggestbtn"><i class="fa-solid fa-utensils"></i>Suggestion</button>
            <button id="planbtn"><i class="fas fa-calendar-alt fa-2x" style="font-size: 16px;"></i>Planner</button>
            <button id="addrecpbtn"><i class="fa-solid fa-plus"></i> Add Recipe</button>
            <button id="favbtn"><i class="fa-regular fa-heart"></i>Favorites</button>
        </div>

        <div class="settings-signup">
        <button id="settingsbtn" style="margin: 0px;"><i class="fas fa-cog"></i></button>
        <div class="signinupbtns">
            <button id="Login"> <i class="fa-solid fa-user"></i> Login</button>
            <button id="Sign-up">Sign Up</button>
        </div>
       </div>



    </div>



    <!-- Header -->
    <header class="page-header">
        <div class="header-content">
            <h1>Find Your Perfect Recipe</h1>
            <p>Discover thousands of delicious recipes from around the world</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-grid">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar">
                <div class="filters-card">
                    <div class="filters-header">
                        <svg class="filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                        <h2>Filters</h2>
                    </div>

                    <!-- Search Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <div class="search-wrapper">
                            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            <input 
                                type="text" 
                                id="searchInput" 
                                class="filter-input" 
                                placeholder="Recipe name or ingredient..."
                            >
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Category</label>
                        <select id="categoryFilter" class="filter-select">
                            <option value="all">All Categories</option>
                            <option value="Breakfast">Breakfast</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Dinner">Dinner</option>
                            <option value="Snacks">Snacks</option>
                            <option value="Desserts">Desserts</option>
                        </select>
                    </div>

                    <!-- Difficulty Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Difficulty</label>
                        <select id="difficultyFilter" class="filter-select">
                            <option value="all">All Levels</option>
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>

                    <!-- Cook Time Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Cooking Time</label>
                        <select id="cookTimeFilter" class="filter-select">
                            <option value="all">Any Time</option>
                            <option value="quick">Quick (≤20 min)</option>
                            <option value="medium">Medium (20-40 min)</option>
                            <option value="long">Long (40+ min)</option>
                        </select>
                    </div>

                    <!-- Clear Filters Button -->
                    <button id="clearFilters" class="btn-clear-filters">
                        Clear All Filters
                    </button>
                </div>
            </aside>

            <!-- Recipe Results -->
            <main class="recipes-section">
                <!-- Results Header -->
                <div class="results-header">
                    <p class="results-count" id="resultsCount">Found 0 recipes</p>
                    <div class="sort-wrapper">
                        <select id="sortBy" class="sort-select">
                            <option value="popular">Most Popular</option>
                            <option value="rating">Highest Rated</option>
                            <option value="time-asc">Cooking Time (Low to High)</option>
                            <option value="time-desc">Cooking Time (High to Low)</option>
                            <option value="name">Alphabetical</option>
                        </select>
                    </div>
                </div>

                <!-- Recipe Grid -->
                <div class="recipes-grid" id="recipesGrid"></div>

                <!-- Empty State -->
                <div class="empty-state" id="emptyState" style="display: none;">
                    <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <h3>No recipes found</h3>
                    <p>Try adjusting your search filters or search terms</p>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <div id="footer"></div>
    <script src="JS/recipes.js"></script>
</body>
</html>