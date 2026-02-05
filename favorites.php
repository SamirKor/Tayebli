<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle AJAX request for favorites data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_favorites'])) {
    header('Content-Type: application/json');
    
    $userID = $_SESSION['user_id'] ?? null;
    
    if (!$userID) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }
    
    // Query favorites with recipe details
    $query = "
        SELECT r.RecipeID as id, r.Rtitle as title, r.Description as description, 
               r.ImagePath as image, r.CookTime as cookTime, r.Serving as servings, 
               r.Difficulty as difficulty, f.SavedAt as SavedAt
        FROM favorites f
        JOIN recipes r ON f.RecipeID = r.RecipeID
        WHERE f.UserID = ?
        ORDER BY f.SavedAt DESC
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $recipeID = (int)$row['id'];
        $reviews = getReviews($connection, $recipeID);
        $avgRating = calculateAverageRating($reviews, $recipeID);
        
        $row['rating'] = (float)$avgRating;
        $row['isFavorite'] = true;
        $row['cookTime'] = $row['cookTime'] . ' min';
        $row['servings'] = (int)$row['servings'];
        $recipes[] = $row;
    }
    
    $stmt->close();
    
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
    <title>TAYEBLI - My Favorites</title>
    <link rel="stylesheet" href="CSS/favorites.css">
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
            <button  id="findbtn"><i class="fa-solid fa-magnifying-glass"></i> Recipes</button>
            <button id="Suggestbtn"><i class="fa-solid fa-utensils"></i>Suggestion</button>
            <button id="planbtn"><i class="fas fa-calendar-alt fa-2x" style="font-size: 16px;"></i>Planner</button>
            <button id="addrecpbtn"><i class="fa-solid fa-plus"></i> Add Recipe</button>
            <button class="active" id="favbtn"><i class="fa-regular fa-heart"></i>Favorites</button>
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
            <div class="header-title">
                <svg class="heart-icon" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <h1>My Favorite Recipes</h1>
            </div>
            <p>Your personal collection of saved recipes</p>
            <button id="clearAllBtn" class="btn-clear-all" style="display: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Clear All
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Stats Cards (shown when there are favorites) -->
        <div class="stats-section" id="statsSection" style="display: none;">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value primary" id="totalRecipes">0</div>
                    <div class="stat-label">Saved Recipes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value secondary" id="avgCookTime">0</div>
                    <div class="stat-label">Avg Cook Time (min)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value rating" id="avgRating">0.0</div>
                    <div class="stat-label">Avg Rating</div>
                </div>
            </div>
        </div>

        <!-- Search and Filter (shown when there are favorites) -->
        <div class="search-filter-section" id="searchFilterSection" style="display: none;">
            <div class="search-filter-card">
                <div class="search-wrapper">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="search-input" 
                        placeholder="Search your favorite recipes..."
                    >
                </div>
                <div class="sort-wrapper">
                    <select id="sortBy" class="sort-select">
                        <option value="recent">Recently Added</option>
                        <option value="oldest">Oldest First</option>
                        <option value="rating">Highest Rated</option>
                        <option value="name">Alphabetical</option>
                        <option value="cookTime">Cook Time</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Recipe Grid (shown when there are favorites) -->
        <div class="recipes-grid" id="recipesGrid" style="display: none;"></div>

        <!-- No Results State (shown when search has no matches) -->
        <div class="no-results-state" id="noResultsState" style="display: none;">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <h3>No recipes found</h3>
            <p>Try adjusting your search terms</p>
        </div>

        <!-- Empty State (shown when no favorites exist) -->
        <div class="empty-state" id="emptyState">
            <svg class="heart-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            <h3>No Favorite Recipes Yet</h3>
            <p>Start exploring recipes and click the heart icon to save your favorites here.</p>
            <div class="empty-actions">
                <button class="btn-browse" onclick="window.location.href='recipes.php'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    Browse Recipes
                </button>
                <button class="btn-suggestions" onclick="window.location.href='suggestions.php'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Get Suggestions
                </button>
            </div>
        </div>

        <!-- Tips Section (shown when there are favorites) -->
        <div class="tips-section" id="tipsSection" style="display: none;">
            <div class="tips-card">
                <h3 class="tips-title">
                    <span>💡</span>
                    <span>Tips for Your Favorites</span>
                </h3>
                <div class="tips-grid">
                    <div class="tip-item">
                        <h4>📋 Meal Planning</h4>
                        <p>Use your favorite recipes to plan your weekly meals and create shopping lists.</p>
                    </div>
                    <div class="tip-item">
                        <h4>📤 Share & Export</h4>
                        <p>Share your favorite collection with friends or export recipes to print.</p>
                    </div>
                    <div class="tip-item">
                        <h4>🔄 Try Variations</h4>
                        <p>Look for similar recipes or variations of your favorites to expand your cooking.</p>
                    </div>
                    <div class="tip-item">
                        <h4>⭐ Rate & Review</h4>
                        <p>Help other cooks by rating and reviewing the recipes you've tried.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div id="footer"></div>
    <script src="JS/favorites.js"></script>
</body>
</html>