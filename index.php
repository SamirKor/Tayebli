<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';
session_start();


// Handle AJAX request for popular recipes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_popular'])) {
    header('Content-Type: application/json');
    
    
    $userID = $_SESSION['user_id'] ?? null;
    
    // Query popular recipes (top rated with reviews)
    $query = "
    SELECT
    r.RecipeID AS id,
    r.Rtitle AS title,
    r.Description AS description,
    r.ImagePath AS image,
    r.CookTime AS cookTime,
    r.Serving AS servings,
    r.Difficulty AS difficulty,
    COUNT(DISTINCT rv.UserID) AS reviewCount,
    COALESCE(AVG(rv.Rating), 0) AS avgRating
    FROM recipes r
    LEFT JOIN reviews rv ON r.RecipeID = rv.RecipeID
    GROUP BY
    r.RecipeID,
    r.Rtitle,
    r.Description,
    r.ImagePath,
    r.CookTime,
    r.Serving,
    r.Difficulty
    ORDER BY avgRating DESC, reviewCount DESC
    LIMIT 3
";
    
    $result = mysqli_query($connection, $query);
    
    $recipes = [];

    if (!$result) {
    echo json_encode([
        'success' => false,
        'error' => "YES U FOUND THE ERROR U SMART ASS"
    ]);
    exit();
}

    while ($row = mysqli_fetch_assoc($result)) {
        $recipeID = (int)$row['id'];
        
        // Check if user has favorited this recipe
        $isFavorite = false;
        if ($userID) {
            $favResult = mysqli_query($connection, "SELECT * FROM favorites WHERE UserID = $userID AND RecipeID = $recipeID");
            $isFavorite = mysqli_num_rows($favResult) > 0;
        }
        
        $row['rating'] = $row['avgRating'] ? round($row['avgRating'], 1) : 0;
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
    <link rel="stylesheet" href="CSS/home.css">
    <title>Home</title>
</head>

<body>



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
            <button class="active" id="homebtn"><i class="fa-solid fa-house"></i> Home</button>
            <button id="findbtn"><i class="fa-solid fa-magnifying-glass"></i> Recipes</button>
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






    <div class="welcome-panel">
        <div class="welcome-text">
            <p style="font-size: 48px;">WELCOME TO<br> TAYEBLI</p>
            <P style="font-size: 20px;">Your friendly cooking assistant. Discover amazing recipes, get smart meal
                suggestions, and share your culinary creations with fellow food lovers.</P>

            <div class="welcome-btns">
                <button onclick="window.location.href='recipes.php'"><i class="fa-solid fa-magnifying-glass"></i> Find Recipes</button>
                <button onclick="window.location.href='suggestions.php'"><i class="fa-solid fa-utensils"></i> Get suggestions</button>
            </div>
        </div>
        <div class="welcome-img">
            <img src="https://i.pinimg.com/originals/39/63/7c/39637c136b3d9c0805eba7c4af8e335b.jpg" alt="spoons" id="welcome-img">
        </div>

    </div>


    <div class="search-all">
        <div class="searchbar">
            <i class="fa-solid fa-magnifying-glass" style="margin-right: 10px; color: grey;"></i>
            <input type="text" placeholder="Search recipes, ingredients or cuisine">
        </div>
        <button onclick="window.location.href = 'recipes.php'">Search</button>
    </div>


    <div class="why">
        <p style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 30px; margin-bottom: 10px;">
            Why choose TAYEBLI?</p>
        <p style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 15px;">We make cooking
            accessible, fun, and social for students and beginners everywhere.</p>
        <div class="reasons">
            <div class="reason">
                <i class="fa-solid fa-magnifying-glass"></i>
                <p style="font-size: 20px;">Find Recipes</p>
                <p style="font-size: 18px; color:  oklch(35.233% 0.00004 271.152);">Search thousands of recipes by
                    ingredients, cuisine, or dietary preferences</p>
            </div>

            <div class="reason">
                <i class="fa-solid fa-utensils"></i>
                <p style="font-size: 20px;">Smart Suggestion</p>
                <p style="font-size: 18px; color:  oklch(35.233% 0.00004 271.152);">Get personalized meal
                    recommendations based on time of day and preferences</p>
            </div>

            <div class="reason">
                <i class="fa-regular fa-heart"></i>
                <p style="font-size: 20px;">Save Favorites</p>
                <p style="font-size: 18px; color:  oklch(35.233% 0.00004 271.152);">Keep track of your favorite recipes
                    and create your personal cookbook</p>
            </div>
        </div>
    </div>


    <!-- for popular recipes -->
    <div class="Popular">
        <div class="Pop">
            <p><span style="font-size: 24px-;">Popular Recipes</span> <br> Trending dishes that everyone loves</p>
            <button onclick="window.location.href='recipes.php'">View All</button>
        </div>

        <div class="recipes-grid" id="popularRecipesContainer"></div>
    </div>



    <div class="ready">
        <p style="font-size: 36px; margin: 50px auto 15px;">Ready to Start Cooking?</p>
        <p style="font-size: 20px; margin: 0px auto 10px;">Join thousands of home cooks sharing their favorite recipes
            and discovering new flavors.</p>
        <!-- <p style="font-size: 20px; margin: 0px auto 20px auto;"></p> -->
        <div class="readybtns">
            <button id="Sign+up" onclick="window.location.href='login.php'">Sign up Free</button>
            <button id="upload" onclick="window.location.href = 'addrecipe.php'">Share a Recipe</button>
        </div>
    </div>

    <div id="footer"></div>

    <script src="JS/home.js"></script>
</body>

</html>

<?php

mysqli_close($connection);


?>