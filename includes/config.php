<?php

$hostname = "nozomi.proxy.rlwy.net";
$username = "root";      // Default for XAMPP
$password = "WSmFRcsyVWXWvWJDjwqGnkZBSHNnFuXQ";          // Empty for XAMPP
$database = "railway";
// Create MySQLi connection
$connection = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: Set charset to prevent encoding issues
mysqli_set_charset($connection, "utf8mb4");

// AI Configuration
define('GEMINI_API_KEY', 'AIzaSyB0ZHHa_7Thsk3nc_2W-i0aPWqepr9B8dc'); // Get from https://makersuite.google.com/app/apikey
define('AI_PROVIDER', 'gemini'); // Options: 'gemini' or 'deepseek'

// Optional: Timezone setting
// date_default_timezone_set('Your/Timezone'); // e.g., 'America/New_York'


?>

