<?php
// Input sanitization
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

// Display error messages
function display_error($error) {
    if (!empty($error)) {
        return '<div class="alert alert-danger">' . $error . '</div>';
    }
    return '';
}

// Display success messages
function display_success($message) {
    if (!empty($message)) {
        return '<div class="alert alert-success">' . $message . '</div>';
    }
    return '';
}

// Validate email format
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password strength
function validate_password($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

// Generate random token (for email verification)
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

function getUserSettings($conn, $userId) {
    $sql = "SELECT * FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getRecipeIDByCategory($conn, $Category, $userID)
{
    $sql1 = "select is_vegetarian , is_vegan, is_gluten_free, is_dairy_free, has_nut_allergy, is_low_carb from users where UserID = ?"; 
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $userID);
    $stmt1->execute();
    $stmt1->bind_result($is_vegitarian, $is_vegan, $is_gluten_free, $is_dairy_free, $has_nut_allergy, $is_low_carb);
    $stmt1->fetch();
    $stmt1->close();
    if ($Category == 'surprise') {
        $sql = "SELECT * FROM recipes WHERE
        ( (? = 1 AND vegetarian = 1) OR ? = 0 ) AND
        ( (? = 1 AND vegan = 1) OR ? = 0 ) AND
        ( (? = 1 AND gluten_free = 1) OR ? = 0 ) AND
        ( (? = 1 AND dairy_free = 1) OR ? = 0 ) AND
        ( (? = 1 AND has_nuts = 0) OR ? = 0 ) AND
        ( (? = 1 AND low_carb = 1) OR ? = 0 )";
        $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiiiiiiiii", $is_vegitarian, $is_vegitarian, $is_vegan, $is_vegan, $is_gluten_free, $is_gluten_free, $is_dairy_free, $is_dairy_free, $has_nut_allergy, $has_nut_allergy, $is_low_carb, $is_low_carb);
    }else {
        $sql = "SELECT * FROM recipes WHERE Category = ? and 
    ( (? = 1 AND vegetarian = 1) OR ? = 0 ) AND
    ( (? = 1 AND vegan = 1) OR ? = 0 ) AND
    ( (? = 1 AND gluten_free = 1) OR ? = 0 ) AND
    ( (? = 1 AND dairy_free = 1) OR ? = 0 ) AND
    ( (? = 1 AND has_nuts = 0) OR ? = 0 ) AND
    ( (? = 1 AND low_carb = 1) OR ? = 0 )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiiiiiiiiiii", $Category, $is_vegitarian, $is_vegitarian, $is_vegan, $is_vegan, $is_gluten_free, $is_gluten_free, $is_dairy_free, $is_dairy_free, $has_nut_allergy, $has_nut_allergy, $is_low_carb, $is_low_carb);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getRecipeByID($conn, $RecipeID)
{
    $sql = "SELECT * FROM recipes WHERE RecipeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $RecipeID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function randomPicking($array) 
{
    switch (count($array)) {
        case 0:
            return [];
        case 1:
            return [$array[0]];
        case 2:
            return [$array[0], $array[1]];
    }
    $randomKeys = array_rand($array, 3); 
    $randomItems = [$array[$randomKeys[0]], $array[$randomKeys[1]], $array[$randomKeys[2]]];

    return $randomItems;
}

function getIngedientsByID($connection, $RecipeID)
{
    $sql = "Select * from ingredients where RecipeID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $RecipeID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStepsByID($connection, $RecipeID)
{
    $sql = "Select * from instructions where RecipeID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $RecipeID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getReviews($connection, $RecipeID)
{
    $sql = "Select * from reviews where RecipeID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $RecipeID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function calculateAverageRating($reviews, $RecipeID) {
    $totalRating = 0;
    $count = 0;

    foreach ($reviews as $review) {
        if ($review['RecipeID'] == $RecipeID) {
            $totalRating += $review['Rating'];
            $count++;
        }
    }

    return $count > 0 ? round($totalRating / $count, 1) : 0;
}

function checkInPlan($conn, $RecipeID, $UserID)
{
    $sql = "select * from savedforplanner where RecipeID = ? AND UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $RecipeID, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    return (mysqli_num_rows($result) > 0);
}

function getRecipeIdsfromUser($conn, $UserID)
{
    $sql = "SELECT RecipeID FROM savedforplanner WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getIDFromPlan($conn, $UserID, $type, $day)
{
    $sql = "Select * from plannedrecipes where UserID = ? and MealType = ? and PlanDay = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $UserID, $type, $day);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['RecipeID'] : false;
}

function getIngredients($conn, $RecipeID)
{
    $sql = "select * from ingredients where RecipeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $RecipeID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

?>