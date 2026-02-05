<?php

session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
$_SESSION['meal'] = null;

$Category = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Handle favorite toggle
    if (isset($_POST['toggle_favorite'])) {
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

    if (isset($_POST['meal'])) {
        
        $Category = $_POST['meal'];
        
        // Fetch recipes for this category
        $AllrecipesbyCategory = getRecipeIDByCategory($connection, $Category, $_SESSION['user_id']);
        
        if ($AllrecipesbyCategory) {
            // Handle shuffle or initial load - always call randomPicking
            $RecipesToAppear = randomPicking($AllrecipesbyCategory);
            
            $isfavorite = [];
            foreach ($RecipesToAppear as $recipe) {
                $RecipeID = $recipe['RecipeID'];
                $sql = "SELECT * FROM favorites WHERE RecipeID = ? and UserID = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("ii", $RecipeID, $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $isfavorite[$RecipeID] = ($result->num_rows > 0) ? true : false;
            }

            echo json_encode([
                'success' => true,
                'meal' => $Category,
                'recipes' => $RecipesToAppear,
                'isfavorite' => $isfavorite
            ]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'No recipes found for this meal type']);
            exit();
        }
    }
}

// echo $_SESSION['meal']?? "NOTHIIIING";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>TAYEBLI - Smart Meal Suggestions</title>
    <link rel="stylesheet" href="CSS/suggestions.css">
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
            <button id="homebtn"><i class="fa-solid fa-house"></i> Home</button>
            <button id="findbtn"><i class="fa-solid fa-magnifying-glass"></i> Recipes</button>
            <button class="active" id="Suggestbtn"><i class="fa-solid fa-utensils"></i>Suggestion</button>
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
            <h1>Smart Meal Suggestions</h1>
            <p>Let us help you decide what to cook based on the time of day</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Smart Suggestion Banner -->
        <div class="smart-banner" id="smartBanner">
            <div class="banner-content">
                <div class="banner-left">
                    <div class="banner-icon" id="bannerIcon"></div>
                    <div class="banner-text">
                        <h3 id="bannerTitle">Perfect time for breakfast!</h3>
                        <p id="bannerDescription">Based on the current time, we suggest breakfast recipes.</p>
                    </div>
                </div>
                <button class="btn-banner" id="showSuggestionsBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Show Suggestions
                </button>
            </div>
        </div>

        <!-- Meal Time Selection -->
        <div class="meal-times-section">
            <h2>What are you in the mood for?</h2>

            <form action="suggestions.php" method="post" id="mealForm">
                
                <input type="hidden" name="meal" id="selectedMeal" value="">
                <script>
                    // Prevent the form from doing a regular submit (we use AJAX)
                    document.addEventListener('DOMContentLoaded', function(){
                        const mealForm = document.getElementById('mealForm');
                        if (mealForm) mealForm.addEventListener('submit', function(e){ e.preventDefault(); });
                    });
                </script>

                <div class="meal-times-grid">
                    <!-- Breakfast Card -->
                    <div class="meal-time-card" data-meal="breakfast">
                        <div class="meal-icon breakfast-gradient">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 2v10"/>
                                <path d="M18.36 6.64a9 9 0 1 1-12.73 0"/>
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                        </div>
                        <h3>Breakfast</h3>
                        <p class="meal-description">Start your day right</p>
                        <p class="meal-time">6:00 - 11:00 AM</p>
                    </div>

                    <!-- Lunch Card -->
                    <div class="meal-time-card" data-meal="lunch">
                        <div class="meal-icon lunch-gradient">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <h3>Lunch</h3>
                        <p class="meal-description">Midday fuel</p>
                        <p class="meal-time">11:00 AM - 3:00 PM</p>
                    </div>

                    <!-- Dinner Card -->
                    <div class="meal-time-card" data-meal="dinner">
                        <div class="meal-icon dinner-gradient">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        </div>
                        <h3>Dinner</h3>
                        <p class="meal-description">Evening satisfaction</p>
                        <p class="meal-time">5:00 PM - 9:00 PM</p>
                    </div>

                    <!-- Surprise Me Card -->
                    <div class="meal-time-card" data-meal="surprise">
                        <div class="meal-icon surprise-gradient">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="16 3 21 3 21 8"/>
                                <line x1="4" y1="20" x2="21" y2="3"/>
                                <polyline points="21 16 21 21 16 21"/>
                                <line x1="15" y1="15" x2="21" y2="21"/>
                                <line x1="4" y1="4" x2="9" y2="9"/>
                            </svg>
                        </div>
                        <h3>Surprise Me!</h3>
                        <p class="meal-description">Random delicious pick</p>
                        <p class="meal-time">Anytime</p>
                    </div>
                </div>
            </form>
        </div>

        <!-- Suggested Recipes Section -->
        <div class="suggested-recipes-section" id="suggestedSection" style="display: none;">
            <div class="section-header">
                <h2 id="suggestedTitle">Suggested Recipes</h2>
                <button class="btn-shuffle" id="shuffleBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="16 3 21 3 21 8"/>
                        <line x1="4" y1="20" x2="21" y2="3"/>
                        <polyline points="21 16 21 21 16 21"/>
                        <line x1="15" y1="15" x2="21" y2="21"/>
                        <line x1="4" y1="4" x2="9" y2="9"/>
                    </svg>
                    Get New Suggestions
                </button>
            </div>
            <div class="recipes-grid" id="recipesGrid"></div>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <h3>Choose a meal time above</h3>
            <p>Select when you're planning to eat, and we'll suggest the perfect recipes for that time of day.</p>
        </div>

        <!-- Tips Section -->
        <div class="tips-section">
            <div class="tips-card">
                <h3 class="tips-title">
                    <span>💡</span>
                    <span>Smart Cooking Tips</span>
                </h3>
                <div class="tips-grid">
                    <div class="tip-item">
                        <h4>🌅 Breakfast (6-11 AM)</h4>
                        <p>Start with protein and fiber to keep you energized. Quick recipes work best for busy mornings.</p>
                    </div>
                    <div class="tip-item">
                        <h4>☀️ Lunch (11 AM-3 PM)</h4>
                        <p>Light but satisfying meals that won't make you sleepy. Great time for salads and grain bowls.</p>
                    </div>
                    <div class="tip-item">
                        <h4>🌙 Dinner (5-9 PM)</h4>
                        <p>Time to unwind with comfort foods. Perfect for trying new recipes and cooking techniques.</p>
                    </div>
                    <div class="tip-item">
                        <h4>🎲 Surprise Me!</h4>
                        <p>Feeling adventurous? Let us pick something unexpected that you might love.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div id="footer"></div>


    <script src="JS/suggestions.js"></script>
</body>
</html>

<?php

?>