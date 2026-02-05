<?php
require_once 'includes/functions.php';
require_once 'includes/config.php';
require_once 'includes/ai_nutrition.php';

session_start();

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php');
    exit();
}

$success_message = '';
$error_message = '';
$nutrition_data = null;
$unknown_ingredients = [];

if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $title = sanitize_input($_POST['titlerecipe']);
    $description = sanitize_input($_POST['description']);
    $category = sanitize_input($_POST['Category']);
    $difficulty = sanitize_input($_POST['Difficulty']);
    $prepTime = intval($_POST['preptime']);
    $cookTime = intval($_POST['CookTime']);
    $servings = intval($_POST['servings']);
    $userId = $_SESSION['user_id'];
    
    // Handle image upload
    $imagePath = null;
    $imageFilename = null;
    $upload_debug = []; // DEBUG
    
    $upload_debug[] = "File input received: " . (isset($_FILES['recipe_image']) ? 'YES' : 'NO');
    $upload_debug[] = "Error code: " . (isset($_FILES['recipe_image']) ? $_FILES['recipe_image']['error'] : 'N/A');
    
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] != UPLOAD_ERR_NO_FILE) {
        $recipe_image = $_FILES['recipe_image'];
        $errorCode = $recipe_image['error'];
        
        $upload_debug[] = "Processing upload...";
        $upload_debug[] = "File name: " . $recipe_image['name'];
        $upload_debug[] = "File type: " . $recipe_image['type'];
        $upload_debug[] = "File size: " . $recipe_image['size'];
        $upload_debug[] = "Tmp name: " . $recipe_image['tmp_name'];
        
        if ($errorCode === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = mime_content_type($recipe_image['tmp_name']);
            
            $upload_debug[] = "MIME type detected: " . $file_type;
            $upload_debug[] = "MIME type allowed: " . (in_array($file_type, $allowed_types) ? 'YES' : 'NO');
            $upload_debug[] = "is_uploaded_file: " . (is_uploaded_file($recipe_image['tmp_name']) ? 'YES' : 'NO');
            
            if (in_array($file_type, $allowed_types) && is_uploaded_file($recipe_image['tmp_name'])) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($recipe_image['name']));
                $filename = time() . '_' . $safeName;
                $destAbsolute = $uploadDir . $filename;
                $destRelative = 'uploads/' . $filename;
                
                $upload_debug[] = "Destination: " . $destAbsolute;
                $upload_debug[] = "Directory writable: " . (is_writable($uploadDir) ? 'YES' : 'NO');
                
                if (move_uploaded_file($recipe_image['tmp_name'], $destAbsolute)) {
                    chmod($destAbsolute, 0644);
                    $imagePath = $destRelative;
                    $imageFilename = $filename;
                    $upload_debug[] = "✅ File uploaded successfully!";
                } else {
                    $upload_debug[] = "❌ move_uploaded_file FAILED";
                    $error_message = "Failed to save image file.";
                }
            } else {
                $upload_debug[] = "❌ File type validation failed";
                $error_message = "Invalid file type. Please upload an image (JPEG, PNG, GIF, or WebP).";
            }
        } else {
            $upload_debug[] = "❌ Upload error code: " . $errorCode . " - " . getUploadErrorMessage($errorCode);
            $error_message = "Image upload failed: " . getUploadErrorMessage($errorCode);
        }
    } else {
        $upload_debug[] = "ℹ️ No file selected or UPLOAD_ERR_NO_FILE";
    }
    
    // ===== AI NUTRITION ANALYSIS =====
    if (isset($_POST['Iname']) && isset($_POST['Iamount'])) {
        $ingredientNames = $_POST['Iname'];
        $ingredientAmounts = $_POST['Iamount'];
        $ingredientCount = count($ingredientNames);
        
        // Prepare ingredients for AI analysis
        $ingredients_for_ai = [];
        for ($i = 0; $i < $ingredientCount; $i++) {
            $ingredients_for_ai[] = [
                'name' => sanitize_input($ingredientNames[$i]),
                'amount' => sanitize_input($ingredientAmounts[$i])
            ];
        }
        
        // Call AI service
        $analyzer = new NutritionAnalyzer();
        $ai_result = $analyzer->analyzeIngredients($ingredients_for_ai, $servings);
        echo "<script>console.log('AI Result: " . json_encode($ai_result) . "');</script>";
        if ($ai_result['success']) {
            $nutrition_data = $ai_result['data'];
            
            // Check for unknown ingredients
            if (isset($nutrition_data['ingredient_status'])) {
                if ($nutrition_data['ingredient_status'] === 'all_unknown') {
                    $error_message = "ERROR: All ingredients are unknown or unclear. Please use common ingredient names (e.g., 'eggs', 'flour', 'milk') instead of brand names or ambiguous terms.";
                    require_once 'includes/header.php';
                    displayFormWithError($error_message);
                    exit();
                } 
                elseif ($nutrition_data['ingredient_status'] === 'partial_unknown') {
                    if (isset($nutrition_data['unknown_ingredients']) && is_array($nutrition_data['unknown_ingredients'])) {
                        $unknown_ingredients = $nutrition_data['unknown_ingredients'];
                        $error_message = "WARNING: Some ingredients could not be analyzed: " . 
                                        implode(', ', $unknown_ingredients) . 
                                        ". Please verify these ingredients. The recipe will be saved with partial nutrition data.";
                    }
                }
            }
            
            // Prepare nutrition values for database - provide defaults instead of NULL
            // This prevents "Column cannot be null" errors
            $vegetarian = isset($nutrition_data['vegetarian']) ? ($nutrition_data['vegetarian'] ? 1 : 0) : 0;
            $vegan = isset($nutrition_data['vegan']) ? ($nutrition_data['vegan'] ? 1 : 0) : 0;
            $gluten_free = isset($nutrition_data['gluten_free']) ? ($nutrition_data['gluten_free'] ? 1 : 0) : 0;
            $dairy_free = isset($nutrition_data['dairy_free']) ? ($nutrition_data['dairy_free'] ? 1 : 0) : 0;
            $has_nuts = isset($nutrition_data['has_nuts']) ? ($nutrition_data['has_nuts'] ? 1 : 0) : 0;
            $low_carb = isset($nutrition_data['low_carb']) ? ($nutrition_data['low_carb'] ? 1 : 0) : 0;
            $carbs = isset($nutrition_data['Carbs']) && $nutrition_data['Carbs'] !== null ? intval($nutrition_data['Carbs']) : 0;
            $proteins = isset($nutrition_data['Proteins']) && $nutrition_data['Proteins'] !== null ? intval($nutrition_data['Proteins']) : 0;
            $calories = isset($nutrition_data['Calories']) && $nutrition_data['Calories'] !== null ? intval($nutrition_data['Calories']) : 0;
            $fat = isset($nutrition_data['Fat']) && $nutrition_data['Fat'] !== null ? intval($nutrition_data['Fat']) : 0;
            
        } else {
            $error_message = "AI analysis failed: " . $ai_result['error'] . ". Recipe will be saved without nutrition data.";
            $vegetarian = $vegan = $gluten_free = $dairy_free = $has_nuts = $low_carb = 0;
            $carbs = $proteins = $calories = $fat = 0;
        }
    } else {
        $error_message = "No ingredients provided for analysis.";
        $vegetarian = $vegan = $gluten_free = $dairy_free = $has_nuts = $low_carb = 0;
        $carbs = $proteins = $calories = $fat = 0;
    }
    // ===== END AI ANALYSIS =====
    echo "<script>console.log('Nutrition Data: " . json_encode($nutrition_data) . "');</script>";
    // Insert recipe into database WITH nutrition data
    $sql = "INSERT INTO recipes (Rtitle, Description, PrepTime, CookTime, Serving, Category, Difficulty, ChefID, ImagePath, ImageFilename,
            vegetarian, vegan, gluten_free, dairy_free, has_nuts, low_carb, Carbs, Proteins, Calories, Fat) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        $error_message = "Database error: " . $connection->error;
        displayFormWithError($error_message);
        exit();
    }
    
    $stmt->bind_param("ssiiississiiiiiiiiii", 
        $title, $description, $prepTime, $cookTime, $servings, $category, $difficulty, $userId, $imagePath, $imageFilename,
        $vegetarian, $vegan, $gluten_free, $dairy_free, $has_nuts, $low_carb, $carbs, $proteins, $calories, $fat);
    
    if ($stmt->execute()) {
        $recipeID = $stmt->insert_id;
        mysqli_query($connection , "Update users set TotalRecipes = TotalRecipes + 1 where UserID = $userId");
        
        $success_message = "Recipe submitted successfully!";
        
        // Save ingredients to database
        if (isset($ingredientNames) && isset($ingredientAmounts)) {
            $ingredientSql = "INSERT INTO ingredients (RecipeID, Iname, Amount) VALUES (?, ?, ?)";
            $ingredientStmt = $connection->prepare($ingredientSql);
            
            for ($i = 0; $i < $ingredientCount; $i++) {
                $iname = sanitize_input($ingredientNames[$i]);
                $iamount = sanitize_input($ingredientAmounts[$i]);
                $ingredientStmt->bind_param("iss", $recipeID, $iname, $iamount);
                $ingredientStmt->execute();
            }
            $ingredientStmt->close();
        }
        
        // Save instructions to database
        if(isset($_POST['step_description'])) {
            $stepTimes = $_POST['step_time'];
            $stepDescriptions = $_POST['step_description'];
            $stepCount = count($stepDescriptions);
            
            $stepSql = "INSERT INTO instructions (RecipeID, Step, TimeNeeded, Description) VALUES (?, ?, ?, ?)";
            $stepStmt = $connection->prepare($stepSql);
            
            for ($i = 0; $i < $stepCount; $i++) {
                $stepNumber = $i + 1;
                $stepTime = intval($stepTimes[$i]);
                $stepDescription = sanitize_input($stepDescriptions[$i]);
                $stepStmt->bind_param("iiis", $recipeID, $stepNumber, $stepTime, $stepDescription);
                $stepStmt->execute();
            }
            $stepStmt->close();
        }
        
        // Add nutrition analysis result to success message
        if ($nutrition_data) {
            $success_message .= "<br><br><strong>Nutrition Analysis:</strong><br>";
            $success_message .= "Calories: " . ($calories ?? 'N/A') . " per serving<br>";
            $success_message .= "Carbs: " . ($carbs ?? 'N/A') . "g | Protein: " . ($proteins ?? 'N/A') . "g | Fat: " . ($fat ?? 'N/A') . "g<br>";
            
            $dietary_flags = [];
            if ($vegetarian) $dietary_flags[] = "Vegetarian";
            if ($vegan) $dietary_flags[] = "Vegan";
            if ($gluten_free) $dietary_flags[] = "Gluten-Free";
            if ($dairy_free) $dietary_flags[] = "Dairy-Free";
            if ($has_nuts) $dietary_flags[] = "Contains Nuts";
            if ($low_carb) $dietary_flags[] = "Low-Carb";
            
            if (!empty($dietary_flags)) {
                $success_message .= "Dietary: " . implode(", ", $dietary_flags);
            }
        }
        
        if (!empty($unknown_ingredients)) {
            $success_message .= "<br><span style='color: orange;'><i class='fas fa-exclamation-triangle'></i> Note: Some ingredients could not be fully analyzed.</span>";
        }
        
    } else {
        $error_message = "Error submitting recipe: " . $stmt->error;
        
        // Add better error logging
        error_log("SQL Error: " . $stmt->error);
        error_log("SQL State: " . $stmt->sqlstate);
        $error_message = "Error submitting recipe. Please try again.";

    }
    
    $stmt->close();
}

function displayFormWithError($error) {
    echo '
    <div class="error-message" style="background-color: #ffebee; color: #c62828; padding: 15px; margin: 20px; border-radius: 5px; border-left: 4px solid #c62828;">
        <i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($error) . '
    </div>';
}

function getUploadErrorMessage($errorCode) {
    $messages = [
        UPLOAD_ERR_OK => 'No error',
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'Partial upload',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder missing',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
        UPLOAD_ERR_EXTENSION => 'Extension blocked by server',
    ];
    return $messages[$errorCode] ?? 'Unknown error';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/addrecipe.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Add_Recipe</title>
</head>

<body>


    <div class="navbar">
        <div class="logo">
            <a href="index.php" style="text-decoration: none;">
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
            <button class="active" id="addrecpbtn"><i class="fa-solid fa-plus"></i> Add Recipe</button>
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

    <p class="title"><span>Share Your Recipe</span><br>Help fellow cooks discover your delicious creation</p>

    <hr>

    <div class="allform">
        <?php if ($success_message): ?>
            <div class="success-message" style="background: #e8f5e9; color: #2e7d32; padding: 15px; margin: 20px; border-radius: 5px; border-left: 4px solid #4CAF50;">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message && !$success_message): ?>
            <div class="error-message" style="background: #ffebee; color: #c62828; padding: 15px; margin: 20px; border-radius: 5px; border-left: 4px solid #c62828;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- DEBUG BOX -->
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($upload_debug)): ?>
            <div style="background: #f0f0f0; border: 2px solid #2196f3; padding: 15px; margin: 20px; border-radius: 5px; font-family: monospace; font-size: 12px;">
                <strong style="display: block; margin-bottom: 10px; color: #2196f3;">📋 FILE UPLOAD DEBUG INFO:</strong>
                <?php foreach ($upload_debug as $debug): ?>
                    <div style="margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                        <?php echo htmlspecialchars($debug); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form id="Publishform" method="post" enctype="multipart/form-data" action="addrecipe.php">
            <div class="qstbox">

                <label style="font-size: 18px;">Recipe Details </label>

                <div class="lblinp">
                    <label for="title">Recipe Title *</label>
                    <input required type="text" placeholder="Give ur recipe a catchy name" class="inputbox" id="titlerecipe" name="titlerecipe" value="<?php echo isset($_POST['titlerecipe']) ? htmlspecialchars($_POST['titlerecipe']) : ''; ?>">
                </div>


                <div class="lblinp">
                    <label for="title">Description *</label>
                    <input required type="text" placeholder="Describe ur Recipe" class="inputbox Description" id="description" name="description" value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>">
                </div>

                <div class="twoinrow">

                    <div class="lblinp">
                        <label for="Category">Category *</label>
                        <input type="hidden" name="Category" id="CategoryID">
                        <select id="Category" aria-placeholder="Select Category">
                            <option value="Breakfast" <?php echo (isset($_POST['Category']) && $_POST['Category'] == 'Breakfast') ? 'selected' : ''; ?>>Breakfast</option>
                            <option value="Lunch" <?php echo (isset($_POST['Category']) && $_POST['Category'] == 'Lunch') ? 'selected' : ''; ?>>Lunch</option>
                            <option value="Dinner" <?php echo (isset($_POST['Category']) && $_POST['Category'] == 'Dinner') ? 'selected' : ''; ?>>Dinner</option>
                            <option value="Snack" <?php echo (isset($_POST['Category']) && $_POST['Category'] == 'Snack') ? 'selected' : ''; ?>>Snack</option>
                            <option value="Desert" <?php echo (isset($_POST['Category']) && $_POST['Category'] == 'Desert') ? 'selected' : ''; ?>>Desert</option>
                            <option value="Drinks" <?php echo (isset($_POST['Category']) && $_POST['Category'] == 'Drinks') ? 'selected' : ''; ?>>Drinks</option>
                        </select>
                    </div>

                    <div class="lblinp">
                        <label for="Difficulty">Difficulty *</label>
                        <input type="hidden" name="Difficulty" id="DifficultyID">
                        <select id="Difficulty" aria-placeholder="Select Difficulty">
                            <option value="Easy" <?php echo (isset($_POST['Difficulty']) && $_POST['Difficulty'] == 'Easy') ? 'selected' : ''; ?>>Easy</option>
                            <option value="Medium" <?php echo (isset($_POST['Difficulty']) && $_POST['Difficulty'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="hard" <?php echo (isset($_POST['Difficulty']) && $_POST['Difficulty'] == 'hard') ? 'selected' : ''; ?>>hard</option>
                        </select>
                    </div>


                </div>


                <div class="threeinrow">


                    <div class="lblinp">
                        <label for="Time">Prep Time *</label>
                        <input required type="number" placeholder=" 15 min" class="inputbox" name="preptime" value="<?php echo isset($_POST['preptime']) ? htmlspecialchars($_POST['preptime']) : ''; ?>">
                    </div>

                    <div class="lblinp">
                        <label for="title">Cook Time *</label>
                        <input required type="number" placeholder="30 min" class="inputbox" name="CookTime" value="<?php echo isset($_POST['CookTime']) ? htmlspecialchars($_POST['CookTime']) : ''; ?>">
                    </div>

                    <div class="lblinp">
                        <label for="title">Servings *</label>
                        <input required type="number" placeholder="5" class="inputbox" name="servings" value="<?php echo isset($_POST['servings']) ? htmlspecialchars($_POST['servings']) : ''; ?>">
                    </div>


                </div>

                <label for="image">Recipe Image</label>
                <div class="imagezone">
                    <input type="file" id="imageUpload" accept="image/*" name="recipe_image" style="display: none;">
                    <i class="fas fa-upload"></i>
                    <p>Upload a photo of ur dish</p>
                    <button type="button" id="imgbtn">Choose an image</button>
                </div>



            </div>

            <div class="qstbox">

                <label for="Ingredients" style="font-size: 18px;">Ingredients</label>
                <div class="ingredientinfos" id="ingredients-list">
                    <div class="ingredient-item">
                        <input required type="text" placeholder="Ingredient Name" class="inputbox" name="Iname[]">
                        <input required type="text" placeholder="Amount(e.g 2cups)" class="inputbox" name="Iamount[]">
                        <button class="remove" type="button">-</button>
                    </div>

                </div>
                <button class="addbutton" id="add-ingredient-btn" type="button">+ Add Ingredient</button>
                <small style="color: #666; display: block; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> Use common ingredient names (e.g., "eggs", "flour", "milk") for better AI analysis
                </small>

            </div>

            <div class="qstbox">
                <label for="Instructions" style="font-size: 18px;">Instructions</label>
                <div class="instrow" id="instrc-list">
                    <div class="inst-item">
                        <p style="border-radius: 20px;height: fit-content; padding: 10px 15px; font-size: 15px; background-color: #FF914D; color: white; margin-top: 10px;">1</p>
                        <div class="instcols">
                            <div class="twoinrow">
                                <input style="width: 45%" type="text" placeholder="Step Title (Optinal)" class="inputbox" name="step_title[]">
                                <input style="width: 45%" type="number" placeholder="Time Needed (in minutes)" class="inputbox" name="step_time[]">
                            </div>
                            
                            <input required type="text" placeholder="Describe this step in Details"
                                class="inputbox Description" name="step_description[]">
                        </div>
                        <button class="remove" type="button"> - </button>
                    </div>


                </div>
                <button class="addbutton" type="button" id="addstepbtn">+ Add Step</button>
            </div>

            <!-- Nutrition Preview Section -->
            <div class="qstbox" id="nutrition-preview" style="<?php echo ($success_message && $nutrition_data) ? 'display: block;' : 'display: none;'; ?>">
                <label style="font-size: 18px;">Nutrition Analysis</label>
                <div id="nutrition-results" style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
                    <?php if ($nutrition_data): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 15px;">
                            <?php if ($vegetarian): ?><span class="nutrition-badge vegetarian">Vegetarian</span><?php endif; ?>
                            <?php if ($vegan): ?><span class="nutrition-badge vegan">Vegan</span><?php endif; ?>
                            <?php if ($gluten_free): ?><span class="nutrition-badge gluten-free">Gluten-Free</span><?php endif; ?>
                            <?php if ($dairy_free): ?><span class="nutrition-badge dairy-free">Dairy-Free</span><?php endif; ?>
                            <?php if ($has_nuts): ?><span class="nutrition-badge has-nuts">Contains Nuts</span><?php endif; ?>
                            <?php if ($low_carb): ?><span class="nutrition-badge low-carb">Low-Carb</span><?php endif; ?>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <div><strong>Calories:</strong> <?php echo $calories ?? 'N/A'; ?> per serving</div>
                            <div><strong>Carbs:</strong> <?php echo $carbs ?? 'N/A'; ?>g</div>
                            <div><strong>Protein:</strong> <?php echo $proteins ?? 'N/A'; ?>g</div>
                            <div><strong>Fat:</strong> <?php echo $fat ?? 'N/A'; ?>g</div>
                        </div>
                        <?php if (!empty($unknown_ingredients)): ?>
                            <div style="margin-top: 10px; color: orange;">
                                <i class="fas fa-exclamation-triangle"></i> Note: Some ingredients could not be fully analyzed.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><i class="fas fa-spinner fa-spin"></i> Nutrition analysis will appear here after submission...</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="drftpbl">
                <button id="draftbtn" type="button">Save as Draft</button>
                <button id="publishbtn" type="submit">Publish Recipe</button>
            </div>
        </form>
    </div>

    <div id="footer"></div>


    <script src="JS/addrecipe.js"></script>
</body>

</html>

<?php
mysqli_close($connection);
?>