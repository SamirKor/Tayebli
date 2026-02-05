<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$currentRecipes = [];
$spots = [];


$sql = "SELECT * FROM plannedrecipes WHERE UserID = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$initialRecipeCount = $result->num_rows;
$initialIgredientCount =(int) 0;
$stmt->close();

$recipeIds = getRecipeIdsfromUser($connection, $_SESSION['user_id']);
$recipes = [];
foreach ($recipeIds as $key => $recipeRow) {
    $recipes[] = getRecipeByID($connection, $recipeRow['RecipeID']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['get_recipe_by_id']) && isset($_POST['day']) && isset($_POST['meal_type'])) {
        $recipeID = (int) $_POST['get_recipe_by_id']; // force it to integer
        $day = $_POST['day'];
        $meal_type = $_POST['meal_type'];

        
        $recipe = getRecipeByID($connection, $recipeID);
        $ingredie = getIngredients($connection, $recipeID);
        $sql = "INSERT INTO plannedrecipes (RecipeID, UserID, PlanDay, MealType) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($sql); 
        $stmt->bind_param("iiss", $recipeID, $_SESSION['user_id'], $day, $meal_type);
        

        if (!$recipe) {
            echo json_encode([
                'success' => false,
                'message' => "Recipe not found for ID $recipeID"
            ]);
            exit();
        }

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'recipe' => $recipe,
                'ingredients' => $ingredie
            ]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => "HELLLLLLLLLLLLLL"]);
            exit();
        }
        $stmt->close();
        
        
        
    }
    
    if (isset($_POST['Clear_Week'])) {
        $sql = "delete from plannedrecipes where UserID = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        if($stmt->execute())
        {
            echo http_response_code(200);
        }else echo http_response_code(500);
        $stmt->close();
    }

    if (isset($_POST['delete_recipe'])) {
        $day = $_POST['day'];
        $meal_type = $_POST['meal_type'];
        $sql = "delete from plannedrecipes where UserID = ? and PlanDay = ? and MealType = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("iss", $_SESSION['user_id'], $day, $meal_type);
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true
            ]);
            exit();
        }else{ 
            echo json_encode(['success'=>false, 'message' => "something is wrong"]);
            exit();
        }

        $stmt->close();
    }
    
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/planner.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>planner</title>
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
            <button class="active" id="planbtn"><i class="fas fa-calendar-alt fa-2x"
                    style="font-size: 16px;"></i>Planner</button>
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
    <hr>

    <div class="title">
        <p> <i class="fas fa-calendar-alt fa-2x" style="color: #FF914D;"></i><span> Meal Planner</span> <br> Plan your
            weekly meals and generate shopping lists</p>
        <div class="clrlistbtns">
            <button id="clearbtn"><i class="fas fa-sync-alt" style="color: rgb(68, 68, 68);"></i> Clear Week</button>
            <button id="shoplstbtn"> <i class="fas fa-shopping-cart" style="color: white;"></i> Shopping List</button>
        </div>
    </div>

    <hr>

    <div class="week">
        <!-- the infos in the top -->
        <div class="generalinfos">
            <div class="info">
                <p style="color: #FF914D;"><span id="totalplanned">0</span>/28</p>
                <p>Meals Planned</p>
            </div>
            <div class="info">
                <p style="color: #4caf50;"><span id="ingredientscount">0</span></p>
                <p>ingredients Needed</p>
            </div>
            <div class="info">
                <p style="color: #FF914D;"><span id="percentage">0</span>%</p>
                <p>Week Complete</p>
            </div>
            <div class="info">
                <p style="color: rgb(75, 75, 255);"><span id="totaldays">0</span></p>
                <p>Days Planned</p>
            </div>
        </div>

        <p
            style="align-self: flex-start; margin: 25px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 24px;">
            Weekly Schedul</p>


        <!-- the days of the week -->
<div class="aday">
    <p class="dayname">Monday</p>
    <div class="meals">
        <div class="ameal breakfast">
            <p>🌅 Breakfast</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "breakfast", "monday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="monday-breakfast"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'monday-breakfast';
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="monday-breakfast" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal lunch">
            <p>☀️ Lunch</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "lunch", "monday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="monday-lunch"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'monday-lunch';
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="monday-lunch" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal dinner">
            <p>🌙 Dinner</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "dinner", "monday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="monday-dinner"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'monday-dinner'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="monday-dinner" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal snack">
            <p>🍎 Snack</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "snack", "monday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="monday-snack"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'monday-snack'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="monday-snack" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?>
            </div>
        </div>
    </div>
</div>

<div class="aday">
    <p class="dayname">Tuesday</p>
    <div class="meals">
        <div class="ameal breakfast">
            <p>🌅 Breakfast</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "breakfast", "tuesday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="tuesday-breakfast"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'tuesday-breakfast'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="tuesday-breakfast" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal lunch">
            <p>☀️ Lunch</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "lunch", "tuesday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="tuesday-lunch"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'tuesday-lunch'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="tuesday-lunch" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal dinner">
            <p>🌙 Dinner</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "dinner", "tuesday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="tuesday-dinner"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'tuesday-dinner'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="tuesday-dinner" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal snack">
            <p>🍎 Snack</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "snack", "tuesday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="tuesday-snack"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'tuesday-snack'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="tuesday-snack" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
    </div>
</div>

<div class="aday">
    <p class="dayname">Wednesday</p>
    <div class="meals">
        <div class="ameal breakfast">
            <p>🌅 Breakfast</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "breakfast", "wednesday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="wednesday-breakfast"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'wednesday-breakfast'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="wednesday-breakfast" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal lunch">
            <p>☀️ Lunch</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "lunch", "wednesday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="wednesday-lunch"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'wednesday-lunch'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="wednesday-lunch" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal dinner">
            <p>🌙 Dinner</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "dinner", "wednesday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="wednesday-dinner"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'wednesday-dinner'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="wednesday-dinner" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal snack">
            <p>🍎 Snack</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "snack", "wednesday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="wednesday-snack"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'wednesday-snack'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="wednesday-snack" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
    </div>
</div>

<div class="aday">
    <p class="dayname">Thursday</p>
    <div class="meals">
        <div class="ameal breakfast">
            <p>🌅 Breakfast</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "breakfast", "thursday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="thursday-breakfast"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'thursday-breakfast'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="thursday-breakfast" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal lunch">
            <p>☀️ Lunch</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "lunch", "thursday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="thursday-lunch"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'thursday-lunch'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="thursday-lunch" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal dinner">
            <p>🌙 Dinner</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "dinner", "thursday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="thursday-dinner"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'thursday-dinner'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="thursday-dinner" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal snack">
            <p>🍎 Snack</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "snack", "thursday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="thursday-snack"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'thursday-snack'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="thursday-snack" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
    </div>
</div>

<div class="aday">
    <p class="dayname">Friday</p>
    <div class="meals">
        <div class="ameal breakfast">
            <p>🌅 Breakfast</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "breakfast", "friday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="friday-breakfast"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'friday-breakfast'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="friday-breakfast" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal lunch">
            <p>☀️ Lunch</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "lunch", "friday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="friday-lunch"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'friday-lunch'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="friday-lunch" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal dinner">
            <p>🌙 Dinner</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "dinner", "friday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="friday-dinner"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'friday-dinner'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="friday-dinner" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?>
            </div>
        </div>
        
        <div class="ameal snack">
            <p>🍎 Snack</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "snack", "friday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="friday-snack"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'friday-snack'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="friday-snack" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
    </div>
</div>

<div class="aday">
    <p class="dayname">Saturday</p>
    <div class="meals">
        <div class="ameal breakfast">
            <p>🌅 Breakfast</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "breakfast", "saturday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="saturday-breakfast"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'saturday-breakfast'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="saturday-breakfast" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal lunch">
            <p>☀️ Lunch</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "lunch", "saturday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="saturday-lunch"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'saturday-lunch'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="saturday-lunch" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?>
            </div>
        </div>
        
        <div class="ameal dinner">
            <p>🌙 Dinner</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "dinner", "saturday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="saturday-dinner"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'saturday-dinner'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="saturday-dinner" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?>
            </div>
        </div>
        
        <div class="ameal snack">
            <p>🍎 Snack</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "snack", "saturday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="saturday-snack"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'saturday-snack'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="saturday-snack" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
    </div>
</div>

<div class="aday">
    <p class="dayname">Sunday</p>
    <div class="meals">
        <div class="ameal breakfast">
            <p>🌅 Breakfast</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "breakfast", "sunday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="sunday-breakfast"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'sunday-breakfast'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="sunday-breakfast" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal lunch">
            <p>☀️ Lunch</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "lunch", "sunday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="sunday-lunch"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'sunday-lunch'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="sunday-lunch" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal dinner">
            <p>🌙 Dinner</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "dinner", "sunday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="sunday-dinner"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'sunday-dinner'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="sunday-dinner" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
        
        <div class="ameal snack">
            <p>🍎 Snack</p>
            <div class="rcpholder">
            <?php
            $recipeID = getIDFromPlan($connection, $_SESSION['user_id'], "snack", "sunday");
            if (!$recipeID) {
                echo '<button class="selectrcp" data-slot="sunday-snack"><i class="fa-solid fa-plus"></i> Add Recipe</button>';
            } else {
                $recipe = getRecipeByID($connection, $recipeID);
                $currentRecipes[] = $recipe;
                $spots[] = 'sunday-snack'; // ADDED THIS
                echo '
                    <div data-recipe-id="' . $recipe['RecipeID'] . '" class="selected-recipe">
                        <div class="recipe-image-container">
                            <img src="' . $recipe['ImagePath'] . '" alt="' . $recipe['Rtitle'] . '" class="recipe-thumb">
                        </div>
                        <div class="recipe-info">
                            <p class="recipe-title">' . $recipe['Rtitle'] . '</p>
                            <div class="recipe-footer">
                            <p class="recipe-time"><i class="fa-regular fa-clock"></i> ' . $recipe['TotalTime'] . 'min</p>
                            <button class="remove-recipe-btn" data-slot="sunday-snack" aria-label="Remove recipe">
                                <i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                ';
            }
            ?></div>
        </div>
    </div>
</div>
        <!-- fot tips in the end -->
        <div class="tips">
            <p>💡 Meal Planning Tips</p>
            <div class="alltips">
                <div class="atip">
                    <p> <span>🎯 Plan Ahead </span> <br> Planning meals in advance helps you save time, reduce food
                        waste, and eat healthier.</p> <br>
                </div>


                <div class="atip">
                    <p> <span>🔄 Batch Cooking </span> <br>Cook larger portions and use leftovers for multiple meals
                        throughout the week.</p> <br>
                </div>


                <div class="atip">
                    <p> <span>🛒 Smart Shopping </span> <br> Use the shopping list feature to buy only what you need
                        and stay within budget.</p> <br>
                </div>
            </div>

        </div>
    </div>

    <div class="requiredingr">
        <div class="lstofing">

        <p style="font-size: 24px;">Weekly Shopping List</p>
        <p>Ingredients needed for your planned meals</p>
            
        <ul id="Ingredients">
            <?php
                foreach($currentRecipes as $recipe)
                {
                    $ingredients = getIngredients($connection, $recipe['RecipeID']); 
                    foreach($ingredients as $ing){
                        echo '
                        <li class="ingredientItem" data-spotid="' . current($spots) . '">
                        <p>' . htmlspecialchars($ing['Iname']) . ' ' . htmlspecialchars($ing['Amount']) . '</p>
                        </li>
                        ';
                        $initialIgredientCount = $initialIgredientCount + 1;

                    }
                    next($spots);
                }
                reset($spots);
            ?> 
        </ul>
        <div class="ingbtns">
            <button>Export</button>
            <button id="Printing">Print List</button>
        </div>

        </div> 
    </div>


    <div id="lstofrecipes" class="lstofrecipes">
        <div class="lstcontent">
            <span id="closePopup" class="close">&times;</span>
            <p style="font-size: 24px;">Add Recipe</p>
            <p>Choose a recipe from your collection</p>
            <ul id="recipes">
                <?php
                foreach ($recipes as $recipe) {
                    echo '<li class="recpitem" data-recipe-id="' . $recipe['RecipeID'] . '">
                            <div class="Recipe">
                                <div> 
                                    <p>' . htmlspecialchars($recipe['Rtitle']) . '</p>
                                    <p>' . htmlspecialchars($recipe['TotalTime']) . ' mins</p>
                                </div>
                                <span class="dif">' . htmlspecialchars($recipe['Difficulty']) . '</span>
                            </div>
                          </li>';
                }
                ?>
                

            </ul>
        </div>
    </div>

    <div id="selectedrcp"></div>


    <div id="footer"></div>

    <script>
        const INITIAL_RECIPE_COUNT = <?php echo $initialRecipeCount; ?>;
        const INITIAL_INGREDIENT_COUNT = <?php echo $initialIgredientCount ?>;
    </script>
    <script src="JS/planner.js"></script>

</body>

</html>