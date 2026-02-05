<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Check if this is a login status check request
    if (isset($_POST['check_login'])) {
        $userID = $_SESSION['user_id'] ?? null;
        echo json_encode(['logged_in' => !empty($userID)]);
        exit();
    }


    //handle save to planner btn
    if (isset($_POST['add_to_recipe_planner'])) {
        $sql = "INSERT INTO savedforplanner (RecipeID, UserID) VALUES (?, ?)";
        $stmt = $connection->prepare($sql);
        $recipeID = (int) ($_POST['recipe_id'] );
        $userID = $_SESSION['user_id'] ;
        if ( !isset($_POST['recipe_id']) || empty($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid input'
            ]);
            exit();
        }
        $stmt->bind_param("ii", $recipeID, $userID);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true]);
        exit();
    }

    //handle remove from planner btn
    if (isset($_POST['remove_from_recipe_planner'])) {
        $sql = "DELETE FROM savedforplanner WHERE RecipeID = ? AND UserID = ?";
        $stmt = $connection->prepare($sql);
        $recipeID = (int) ($_POST['recipe_id'] );
        $userID = $_SESSION['user_id'] ;
        if ( !isset($_POST['recipe_id']) || empty($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid input'
            ]);
            exit();
        }
        $stmt->bind_param("ii", $recipeID, $userID);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true]);
        exit();
    }


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

    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $postRecipeID = (int) ($_POST['recipe_id'] ?? 0);
    $userID = $_SESSION['user_id'] ?? null;

    if (!$userID) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    if ($postRecipeID <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }

    $stmt = $connection->prepare(
        "INSERT INTO reviews (RecipeID, UserID, Rating, Comm) VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $connection->error]);
        exit();
    }

    $stmt->bind_param("iiis", $postRecipeID, $userID, $rating, $comment);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
    exit();
}

if (!isset($_GET['id'])) {
    die('Recipe not found');
}

$recipeID = (int) $_GET['id'];
$recipeinfos = getRecipeByID($connection, $recipeID);
$ingredients = getIngedientsByID($connection, $recipeID);
$instructions = getStepsByID($connection, $recipeID);
$reviews = getReviews($connection, $recipeID);
$isAddedToPlan = checkInPlan($connection, $recipeID, $_SESSION['user_id'] ?? 0);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creamy Pasta Carbonara | TAYEBLI</title>
    <link rel="stylesheet" href="CSS/recipe_description.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <button id="homebtn"><i class="fa-solid fa-house"></i> Home</button>
            <button id="findbtn"><i class="fa-solid fa-magnifying-glass"></i> Recipes</button>
            <button id="Suggestbtn"><i class="fa-solid fa-utensils"></i>Suggestion</button>
            <button id="planbtn"><i class="fas fa-calendar-alt fa-2x" style="font-size: 16px;"></i>Planner</button>
            <button  id="addrecpbtn"><i class="fa-solid fa-plus"></i> Add Recipe</button>
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



     
    <!-- Top Bar -->
    <div class="top-bar">
        <a href="recipes.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Recipes
        </a>
        <div class="action-icons">
            <a href="#" title="Share"><i class="fas fa-share-alt"></i></a>
            <a href="#" title="Print"><i class="fas fa-print"></i></a>
            <a href='#'title="Favorite" >
                <svg id="favorite-btn" viewBox="0 0 24 24" width="22" height="22" fill="<?php
                $isfavorite = false;
                $result = mysqli_query($connection, "SELECT * FROM favorites WHERE UserID = " . (int)$_SESSION['user_id'] . " AND RecipeID = " . (int)$recipeID);
                if (mysqli_num_rows($result) > 0) {
                    $isfavorite = true;
                    echo 'red';
                } else {
                    echo 'none';
                }
                ?>"
                stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <!-- Recipe Image and Header -->
            <div class="recipe-image" style="background-image: url('<?php echo $recipeinfos['ImagePath']?>');">
                <div class="recipe-difficulty-rating">
                    <div class="difficulty">
                        <span><?php echo $recipeinfos['Difficulty']?></span>
                    </div>
                    <div class="rating">
                        <span>
                            <?php
                            $avgRating = calculateAverageRating($reviews, $recipeID); 
                            echo $avgRating;
                            ?>
                        </span>
                        <span>(<?php echo count($reviews); ?> reviews)</span>
                    </div>
                </div>
                <h1 class="recipe-title"><?php echo $recipeinfos['Rtitle']?></h1>
            </div>
            
            <!-- Recipe Info -->
            <div class="recipe-info">
                <p class="recipe-description">
                    <?php echo $recipeinfos['Description']?>
                </p>
                
                <div class="recipe-meta">
                    <div class="meta-item">
                        <div class="meta-icon">⏱️</div>
                        <div class="meta-value"><?php echo $recipeinfos['CookTime'] . "min"?></div>
                        <div class="meta-label">Cook Time</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-icon">🍽️</div>
                        <div class="meta-value"><?php echo $recipeinfos['Serving']?></div>
                        <div class="meta-label">Servings</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-icon">🔪</div>
                        <div class="meta-value"><?php echo $recipeinfos['PrepTime'] . "min"?></div>
                        <div class="meta-label">Prep Time</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-icon">🔥</div>
                        <div class="meta-value"><?php echo $recipeinfos['Calories']?></div>
                        <div class="meta-label">Calories</div>
                    </div>
                </div>
                
                <div class="chef-info">
                    <div class="chef-avatar">M</div>
                    <div class="chef-details">
                        <h3><?php
                            
                            $chefId = (int) $recipeinfos['ChefID'];

                            $result = mysqli_query(
                                $connection,
                                "SELECT Username FROM users WHERE UserID = $chefId"
                            );

                            $row = mysqli_fetch_assoc($result);
                            echo $row['Username'];

                        ?></h3>
                        <p><?php 
                            $result = mysqli_query($connection, "Select TotalRecipes from users where UserID = $chefId");
                            $row = mysqli_fetch_assoc($result);
                            echo $row['TotalRecipes'];    
                        ?> recipes shared</p>
                    </div>
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="tabs">
                <button class="tab active" data-tab="ingredients">Ingredients</button>
                <button class="tab" data-tab="instructions">Instructions</button>
                <button class="tab" data-tab="reviews">Reviews</button>
            </div>
            
            <!-- Tab Content -->
            <div class="tab-content active" id="ingredients">
                <h2>Ingredients</h2>
                <ul class="ingredients-list">
                    <?php
                    if ($ingredients) {
                        foreach ($ingredients as $ingredient) {
                            echo '<li class="ingredient-item">';
                            echo '<span>' . htmlspecialchars($ingredient['Iname']) . '</span>';
                            echo '<span>' . htmlspecialchars($ingredient['Amount']) . '</span>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
            
            <div class="tab-content" id="instructions">
                <h2>Instructions</h2>
                <ol class="instructions-list">
                    <?php
                    if ($instructions) {
                        foreach ($instructions as $instruction) {
                            echo '<li class="step">';
                            echo '<div class="step-number">' . htmlspecialchars($instruction['Step']) . '</div>';
                            echo '<div class="step-content">';
                            echo '<p class="step-text">' . htmlspecialchars($instruction['Description']) . '</p>';
                            echo '<div class="step-time">';
                            echo '<i class="fas fa-clock"></i>';
                            echo '<span>' . htmlspecialchars($instruction['TimeNeeded']) . ' min</span>';
                            echo '</div>';
                            echo '</div>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ol>
            </div>
            
            <div class="tab-content" id="reviews">
                <div class="reviews-header">
                    <h2>Reviews</h2>
                    
                    <span class="review-count"><?php echo count($reviews); ?> reviews</span>
                </div>

                <button class="write-review-btn" name="write_review" style="<?php 
                $IDs = array_column($reviews, 'UserID');
                if(in_array($_SESSION['user_id'], $IDs)) echo 'display: none;'; ?>">Write a Review</button>

                
                <div class="review-list">
                    <?php
                    if ($reviews) {
                        foreach ($reviews as $review) {
                            $counter = (int) 0;
                            $userID = (int) $review['UserID'];
                            $result = mysqli_query(
                                $connection,
                                "SELECT UserName FROM users WHERE UserID = $userID"
                            );                            
                            $row = mysqli_fetch_assoc($result);
                            
                            echo '<div class="review-item">';
                            echo '<div class="review-header">';
                            echo '<span class="reviewer-name">' . $row['UserName'] . '</span>';
                            echo '<span class="review-date">' . htmlspecialchars($review['ReviewDate']) . '</span>';
                            echo '</div>';
                            echo '<div class="review-rating">';
                            echo str_repeat('★', (int)$review['Rating']) . str_repeat('☆', 5 - (int)$review['Rating']);
                            echo '</div>';
                            echo '<p class="review-text">' . htmlspecialchars($review['Comm']) . '</p>';
                            echo '</div>';
                            $counter = $counter + 1;
                            if ($counter === 3) {
                                break;
                            }
                        }
                    }
                    ?>
                </div>
                
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <button class="action-btn start-cooking">
                        <i class="fas fa-utensils"></i>
                        <span>Start Cooking</span>
                    </button>
                    <button class="action-btn <?php echo ($isAddedToPlan)? 'added' : ''; ?>" id="AddMealPlan">
                        <i class="fas fa-calendar-plus"></i>
                        <span><?php echo ($isAddedToPlan)? 'Added to Meal Plan' : 'Add to Meal Plan'; ?></span>
                    </button>
                    <!-- <button class="action-btn" id="shoplstbtn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Shopping List</span>
                    </button> -->
                    <button class="action-btn">
                        <i class="fas fa-balance-scale"></i>
                        <span>Scale Recipe</span>
                    </button>
                </div>
            </div>
            
            <div class="nutrition-facts">
                <h3>Nutrition Facts</h3>
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <span class="nutrition-value"><?php echo $recipeinfos['Calories'] ?></span>
                        <span class="nutrition-label">Calories</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-value"><?php echo $recipeinfos['Proteins'] ?>g</span>
                        <span class="nutrition-label">Protein</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-value"><?php echo $recipeinfos['Carbs'] ?>g</span>
                        <span class="nutrition-label">Carbs</span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-value"><?php echo $recipeinfos['Fat'] ?>g</span>
                        <span class="nutrition-label">Fat</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="requiredingr">
        <div class="lstofing">
            <h1>This will have the needed ingrs</h1>
    </div> </div>

     <div id ="footer"></div>
    <script src="JS/recipe_description.js"></script>
</body>
</html>