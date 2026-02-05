<?php
/**
 * Check available Gemini models
 */
$api_key = 'AIzaSyBzdbtIHj2JxIcN_MFBilVPWcnJ7RZQEK0';

echo "<h1>Checking Available Gemini Models</h1>";
echo "<hr>";

// List available models
$url = 'https://generativelanguage.googleapis.com/v1/models?key=' . $api_key;

echo "<h2>API Request</h2>";
echo "URL: <code>" . htmlspecialchars($url) . "</code><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h2>Response</h2>";
echo "HTTP Code: <strong>" . $http_code . "</strong><br><br>";

if ($http_code == 200) {
    $data = json_decode($response, true);
    
    if (isset($data['models'])) {
        echo "<h3>Available Models:</h3>";
        echo "<ul>";
        foreach ($data['models'] as $model) {
            echo "<li><strong>" . $model['name'] . "</strong>";
            if (isset($model['displayName'])) {
                echo " (" . $model['displayName'] . ")";
            }
            if (isset($model['supportedGenerationMethods'])) {
                echo "<br>Methods: " . implode(", ", $model['supportedGenerationMethods']);
            }
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "No models found in response.";
    }
} else {
    echo "Error: " . htmlspecialchars($response);
}

echo "<hr>";
echo "<h2>Note:</h2>";
echo "<p>If you only see deprecated models, you may need to use the <strong>v1beta</strong> API instead.</p>";
echo "<p>Try updating the URL to: <code>https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent</code></p>";
?>
