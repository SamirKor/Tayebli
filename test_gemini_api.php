<?php
require_once 'includes/config.php';
require_once 'includes/ai_nutrition.php';

// Test the Gemini API directly
echo "<h1>Gemini API Test</h1>";
echo "<pre>";

$test_ingredients = [
    ['name' => 'chicken breast', 'amount' => '500g'],
    ['name' => 'olive oil', 'amount' => '2 tbsp'],
    ['name' => 'garlic', 'amount' => '3 cloves'],
];

echo "Testing with ingredients:\n";
print_r($test_ingredients);
echo "\n---\n\n";

$analyzer = new NutritionAnalyzer();
$result = $analyzer->analyzeIngredients($test_ingredients, 4);

echo "Result:\n";
print_r($result);

echo "\n\n<strong>Check your Apache error logs:</strong>\n";
echo "Location: c:\\xampp\\apache\\logs\\error.log\n";
echo "Or in XAMPP control panel, click 'Logs' -> 'Apache (error.log)'";

echo "</pre>";
?>
