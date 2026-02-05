<?php
/**
 * Gemini API Diagnostic Script
 * Run this to debug the API connection issue
 */

// Direct API test without the class
$api_key = 'AIzaSyBzdbtIHj2JxIcN_MFBilVPWcnJ7RZQEK0';
// Using gemini-2.5-flash - the latest and most compatible model
$url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=' . $api_key;

echo "<h1>Gemini API Diagnostic Test</h1>";
echo "<hr>";

// Test 1: Check if cURL is available
echo "<h2>Test 1: cURL Extension</h2>";
if (extension_loaded('curl')) {
    echo "<span style='color: green;'>✓ cURL is installed</span><br>";
} else {
    echo "<span style='color: red;'>✗ cURL is NOT installed</span><br>";
    exit("cURL is required but not installed!");
}

// Test 2: Check PHP version
echo "<h2>Test 2: PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
if (version_compare(phpversion(), '7.0', '>=')) {
    echo "<span style='color: green;'>✓ PHP version is compatible</span><br>";
} else {
    echo "<span style='color: red;'>✗ PHP version is too old</span><br>";
}

// Test 3: Test network connectivity
echo "<h2>Test 3: Network Connectivity</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<span style='color: orange;'>⚠ Network connectivity warning: " . htmlspecialchars($error) . "</span><br>";
} else {
    echo "<span style='color: green;'>✓ Network connectivity OK</span><br>";
}

// Test 4: Simple Gemini API test
echo "<h2>Test 4: Simple Gemini API Request</h2>";
echo "Testing with gemini-2.5-flash model (latest, fastest, and fully compatible)<br>";
$url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=' . $api_key;
echo "URL: <code>" . htmlspecialchars($url) . "</code><br>";

$test_data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Return only this JSON: {"test": true}']
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.1,
        'maxOutputTokens' => 100,
    ]
];

$json_payload = json_encode($test_data);
echo "<br><strong>Sending Request...</strong><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: PHP-Gemini-Test'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capture verbose output
$verbose_handle = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose_handle);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$curl_errno = curl_errno($ch);
$response_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

curl_close($ch);

echo "<strong>Response Details:</strong><br>";
echo "HTTP Code: <strong>" . $http_code . "</strong><br>";
echo "cURL Error Number: " . $curl_errno . "<br>";
echo "cURL Error Message: " . htmlspecialchars($curl_error) . "<br>";
echo "Response Time: " . round($response_time, 2) . "s<br>";

echo "<br><strong>Response Body:</strong><br>";
echo "<pre style='background: #f4f4f4; padding: 10px; overflow-x: auto;'>";
echo htmlspecialchars($response);
echo "</pre>";

if ($http_code == 200) {
    echo "<span style='color: green;'>✓ API request successful!</span><br>";
    $decoded = json_decode($response, true);
    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
        echo "Response text: " . htmlspecialchars($decoded['candidates'][0]['content']['parts'][0]['text']);
    }
} elseif ($http_code == 400) {
    echo "<span style='color: red;'>✗ Bad Request (400) - Check your request format</span><br>";
    $error_data = json_decode($response, true);
    if (isset($error_data['error']['message'])) {
        echo "<strong>Error Message:</strong> " . htmlspecialchars($error_data['error']['message']) . "<br>";
    }
} elseif ($http_code == 401 || $http_code == 403) {
    echo "<span style='color: red;'>✗ Authentication Error (" . $http_code . ") - Your API key may be invalid or have insufficient permissions</span><br>";
} elseif ($http_code == 429) {
    echo "<span style='color: orange;'>⚠ Rate Limited (429) - Too many requests. Wait a moment and try again.</span><br>";
} elseif ($http_code == 500 || $http_code == 503) {
    echo "<span style='color: orange;'>⚠ Server Error (" . $http_code . ") - Gemini API is having issues. Try again later.</span><br>";
} else {
    echo "<span style='color: red;'>✗ Unexpected HTTP Code: " . $http_code . "</span><br>";
}

// Test 5: Check logs
echo "<h2>Test 5: Logs Location</h2>";
echo "PHP Error Log: " . (ini_get('error_log') ?: 'Not configured') . "<br>";
echo "Apache Error Log: c:\\xampp\\apache\\logs\\error.log<br>";
echo "MySQL Log: c:\\xampp\\mysql\\data\\<br>";

echo "<hr>";
echo "<p style='color: #666;'><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>Check Apache error logs for detailed error messages</li>";
echo "<li>Verify your Gemini API key is active at: https://console.cloud.google.com/apis/credentials</li>";
echo "<li>Make sure 'Generative Language API' is enabled in your Google Cloud project</li>";
echo "<li>Check if your API key has usage restrictions that block this origin</li>";
echo "</ul>";

?>
