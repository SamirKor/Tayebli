<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

session_start();

// Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit();
// }

$userId = $_SESSION['user_id'];

$userSettings = getUserSettings($connection, $userId);


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['saveProfile'])) {
    // Handle form submission to update user settings
    $Username = sanitize_input($_POST['Username']);
    $Email = sanitize_input($_POST['Email']);
    $cookingSkill = sanitize_input($_POST['cooking_skill']);
    $preferredDifficulty = sanitize_input($_POST['PreferedDifficulty']);
    $maxTime = sanitize_input($_POST['max_time']);
    $favServing = sanitize_input($_POST['fav_serving']);
    $visibility = sanitize_input($_POST['visibility']);
    $isVegetarian = isset($_POST['is_vegetarian']) ? 1 : 0;
    $isVegan = isset($_POST['is_vegan']) ? 1 : 0;
    $isGlutenFree = isset($_POST['is_gluten_free']) ? 1 : 0;
    $isDairyFree = isset($_POST['is_dairy_free']) ? 1 : 0;
    $hasNutAllergy = isset($_POST['has_nut_allergy']) ? 1 : 0;
    $isLowCarb = isset($_POST['is_low_carb']) ? 1 : 0;
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    $recipeOfDay = isset($_POST['recipe_of_day']) ? 1 : 0;
    $newRecipeAlerts = isset($_POST['new_recipe_alerts']) ? 1 : 0;
    $mealPlanningReminders = isset($_POST['meal_planning_reminders']) ? 1 : 0;
    $weeklyDigest = isset($_POST['weekly_digest']) ? 1 : 0;
    
    // Update query
    $sql = "UPDATE users SET Username = ?, Email = ?, cooking_skill = ?, PreferedDifficulty = ?, max_time = ?, fav_serving = ?, visibility = ?, is_vegetarian = ?, is_vegan = ?, is_gluten_free = ?, is_dairy_free = ?, has_nut_allergy = ?, is_low_carb = ?, email_notifications = ?, recipe_of_day = ?, new_recipe_alerts = ?, meal_planning_reminders = ?, weekly_digest = ? WHERE UserID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('ssssssssssssssssssi', $Username, $Email, $cookingSkill, $preferredDifficulty, $maxTime, $favServing, $visibility, $isVegetarian, $isVegan, $isGlutenFree, $isDairyFree, $hasNutAllergy, $isLowCarb, $emailNotifications, $recipeOfDay, $newRecipeAlerts, $mealPlanningReminders, $weeklyDigest, $userId);
    
    if ($stmt->execute()) {

        echo "Settings updated successfully.";
        $_SESSION['username'] = $Username;
        // exit(); 
    } else {
        echo "Error updating settings: " . $connection->error;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['savePassword'])) {
    // Handle password change
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Fetch current password hash from database
    $sql = "SELECT Passwords FROM users WHERE UserID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($currentPasswordHash);
    $stmt->fetch();
    $stmt->close();

    // Verify current password
    if (password_verify($currentPassword, $currentPasswordHash)) {
        // Check if new passwords match
        if (validate_password($newPassword) && $newPassword === $confirmPassword) {
            // Hash new password and update in database
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET Passwords = ? WHERE UserID = ?";
            $updateStmt = $connection->prepare($updateSql);
            $updateStmt->bind_param('si', $newPasswordHash, $userId);
            if ($updateStmt->execute()) {
                echo "Password changed successfully.";
                $_SESSION['password_changed'] = true;
            } else {
                echo "Error updating password: " . $connection->error;
            }
            $updateStmt->close();
        } else {
            echo "New passwords do not match.";
        }
    } else {
        echo "Current password is incorrect.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteAccount'])) {
    
    $password = $_POST['current_password'];

    $sql = "SELECT Passwords FROM users WHERE UserID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($currentPasswordHash);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($password, $currentPasswordHash)) {
        $sql = "DELETE FROM users WHERE UserID = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $userId);
        if ($stmt->execute()) {
            echo "Account deleted successfully.";
            session_destroy();
            header("Location: login.php");
            exit();
        } else {
            echo "Error deleting account: " . $connection->error;
        }
        $stmt->close();
    }

}

// Fetch user settings from the database
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings - TAYEBLI</title>
    <link rel="stylesheet" href="CSS/settings.css">
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

<hr>

    <div class="container">
        <!-- Page Title -->
        <div class="page-title">
            <h1>⚙️ User Settings</h1>
            <p class="subtitle">Personalize your TAYEBLI experience</p>
        </div>
        
        <form action="settings.php" method="post" id="settingsForm">
        
            <!-- Profile Information Section -->
            <div class="section">
                <h2>Profile Information</h2>
                <p class="section-description">Update your personal information and cooking profile</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="fullName">User Name</label>
                        <input type="text" id="fullName" value="<?php echo htmlspecialchars($userSettings['Username']); ?>" name="Username">
                        <span id="nameError" style="color: #f65118; font-size: 14px;"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($userSettings['Email']); ?>" name="Email">
                        <span id="emailError" style="color: #f65118; font-size: 14px;"></span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Cooking Skill Level</label>
                    <div class="dropdown" id="skillLevelDropdown">                            
                        <input type="hidden" id="skillLevelInput" name="cooking_skill" required value="<?php echo htmlspecialchars($userSettings['cooking_skill']); ?>">

                        <button type="button" class="dropdown-toggle" id="skillLevelToggle">
                            <span id="skillLevelText"><?php echo htmlspecialchars($userSettings['cooking_skill']); ?></span>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="dropdown-option" data-value="beginner">
                                Beginner - Just starting out
                                <span class="checkmark">✓</span>
                            </div>
                            <div class="dropdown-option selected" data-value="intermediate">
                                Intermediate - Comfortable in the kitchen
                                <span class="checkmark">✓</span>
                            </div>
                            <div class="dropdown-option" data-value="advanced">
                                Advanced - Experienced cook
                                <span class="checkmark">✓</span>
                            </div>
                        </div>
                    </div>
                </div>
                
            
            </div>

            <!-- Dietary Restrictions & Preferences Section -->
            <div class="section">
                <h2>Dietary Restrictions & Preferences</h2>
                <p class="section-description">Help us show you recipes that match your dietary needs</p>
                
                <div class="dietary-grid">
                    <div class="dietary-item">
                        <span class="dietary-emoji">🥦</span>
                        <span class="dietary-label">Vegetarian</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_vegetarian" <?php if ($userSettings['is_vegetarian']) echo 'checked'; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="dietary-item">
                        <span class="dietary-emoji">🌱</span>
                        <span class="dietary-label">Vegan</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_vegan" <?php if ($userSettings['is_vegan']) echo 'checked'; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="dietary-item">
                        <span class="dietary-emoji">🌾</span>
                        <span class="dietary-label">Gluten Free</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_gluten_free" <?php if ($userSettings['is_gluten_free']) echo 'checked'; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="dietary-item">
                        <span class="dietary-emoji">🥛</span>
                        <span class="dietary-label">Dairy Free</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_dairy_free" <?php if ($userSettings['is_dairy_free']) echo 'checked'; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="dietary-item">
                        <span class="dietary-emoji">🥜</span>
                        <span class="dietary-label">Nut Allergy</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="has_nut_allergy" <?php if ($userSettings['has_nut_allergy']) echo 'checked'; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="dietary-item">
                        <span class="dietary-emoji">🥗</span>
                        <span class="dietary-label">Low Carb</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_low_carb" <?php if ($userSettings['is_low_carb']) echo 'checked'; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Cooking Preferences Section -->
            <div class="section">
                <h2>Cooking Preferences</h2>
                <p class="section-description">Set your default cooking parameters</p>
                
                <div class="preferences-grid">
                    <div class="preference-item">
                        <label>Maximum Cooking Time (minutes)</label>
                        <div class="dropdown" id="cookingTimeDropdown">
                            <input type="hidden" id="cookingTimeInput" name="max_time" value="<?php echo htmlspecialchars($userSettings['max_time']); ?>">
                            <button type="button" class="dropdown-toggle" id="cookingTimeToggle">
                                
                                <span id="cookingTimeText"><?php echo htmlspecialchars($userSettings['max_time']); ?></span>
                                <span class="dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu">
                                <div class="dropdown-option" data-value="15">
                                    15 minutes
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option selected" data-value="30">
                                    30 minutes
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="45">
                                    45 minutes
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="60">
                                    1 hour
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="90">
                                    1.5 hours
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="any">
                                    Any duration
                                    <span class="checkmark">✓</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="preference-item">
                        <label>Default Servings</label>
                        <div class="dropdown" id="servingsDropdown">
                            <input type="hidden" id="servingsInput" name="fav_serving" value="<?php echo htmlspecialchars($userSettings['fav_serving']); ?>">
                            <button type="button" class="dropdown-toggle" id="servingsToggle">
                                
                                <span id="servingsText"><?php echo htmlspecialchars($userSettings['fav_serving']); ?></span>
                                <span class="dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu">
                                <div class="dropdown-option" data-value="1">
                                    1 person
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option selected" data-value="2">
                                    2 people
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="4">
                                    4 people
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="6">
                                    6 people
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="8">
                                    8 people
                                    <span class="checkmark">✓</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="preference-item" style="display: none;">
                        <label>Measurement System</label>
                        <div class="dropdown" id="measurementDropdown">
                            <button type="button" class="dropdown-toggle" id="measurementToggle">
                                <span id="measurementText">Metric (g, ml, °C)</span>
                                <span class="dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu">
                                <div class="dropdown-option selected" data-value="metric">
                                    Metric (g, ml, °C)
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="imperial">
                                    Imperial (oz, cups, °F)
                                    <span class="checkmark">✓</span>
                                </div>
                            </div>
                        </div>
                    </div> 
                    
                    <div class="preference-item">
                        <label>Preferred Difficulty</label>
                        <div class="dropdown" id="difficultyDropdown">
                            <input type="hidden" id="difficultyInput" name="PreferedDifficulty" value="<?php echo htmlspecialchars($userSettings['PreferedDifficulty']); ?>">
                            <button type="button" class="dropdown-toggle" id="difficultyToggle">
                                
                                <span id="difficultyText"><?php echo htmlspecialchars($userSettings['PreferedDifficulty']); ?></span>
                                <span class="dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu">
                                <div class="dropdown-option" data-value="easy">
                                    Easy
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="easy-medium">
                                    Medium
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option selected" data-value="all">
                                    Hard
                                    <span class="checkmark">✓</span>
                                </div>
                                <div class="dropdown-option" data-value="all">
                                    All difficulties
                                    <span class="checkmark">✓</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Privacy & Security Section -->
            <div class="section">
                <h2>Privacy & Security</h2>
                
                <div class="privacy-item">
                    <button type="button" class="privacy-btn" id="changePasswordBtn">Change Password</button>
                </div>
                
                <div class="privacy-item">
                    <label>Profile Visibility</label>
                    <p class="privacy-description">Choose who can see your profile and recipes</p>
                    <div class="dropdown" id="visibilityDropdown">
                        <input type="hidden" id="visibilityInput" name="visibility" value="<?php echo htmlspecialchars($userSettings['visibility']); ?>">
                        <button type="button" class="dropdown-toggle" id="visibilityToggle">
                            
                            <span id="visibilityText"><?php echo htmlspecialchars($userSettings['visibility']); ?></span>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="dropdown-option selected" data-value="public">
                                Public - Anyone can see
                                <span class="checkmark">✓</span>
                            </div>
                            <div class="dropdown-option" data-value="friends">
                                Friends only
                                <span class="checkmark">✓</span>
                            </div>
                            <div class="dropdown-option" data-value="private">
                                Private - Only me
                                <span class="checkmark">✓</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="privacy-item">
                    <button type="button" class="privacy-btn danger" id="deleteAccountBtn">Delete Account</button>
                </div>
                
                <div class="action-buttons">
                    <input type="reset" class="btn btn-cancel">
                    <input type="submit" class="btn btn-save" id="saveProfile" value="Save Changes" name="saveProfile">
                </div>
            </div>
        </form>
    </div>

    <!-- Modals -->
    <div id="addCuisineModal" class="modal">
        <div class="modal-content">
            <h3>Add Cuisine</h3>
            <input type="text" id="newCuisineInput" placeholder="Enter cuisine name">
            <div class="modal-actions">
                <button class="btn btn-cancel" id="cancelCuisineBtn">Cancel</button>
                <button class="btn btn-save" id="saveCuisineBtn">Add</button>
            </div>
        </div>
    </div>

    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <form id="passwordForm" method="post">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="current_password">
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="new_password">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password">
                </div>
                <input type="hidden" name="savePassword" id="savePasswordHidden" value="">
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" id="cancelPasswordBtn">Cancel</button>
                    <button type="button" class="btn btn-save" id="savePasswordBtn">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteAccountModal" class="modal">
        <div class="modal-content">
            <form method="post">
            <h3>Delete Account</h3>
            <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="current_password">
                </div>
            <p>Are you sure you want to delete your account? This action cannot be undone.</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cancelDeleteBtn">Cancel</button>
                <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" name="deleteAccount">Delete Account</button>
            </div>
            </form>
        </div>
    </div>
     <div id ="footer"></div>
    <script src="JS/settings.js"></script>
</body>
</html>

<?php




mysqli_close($connection);

?>