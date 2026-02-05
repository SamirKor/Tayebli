<?php
session_start();

echo "<h2>File Upload Diagnostic Test</h2>";
echo "<hr>";

// Test 1: Check $_FILES
echo "<h3>1. \$_FILES Variable:</h3>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

// Test 2: Check POST
echo "<h3>2. \$_POST Variable:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Test 3: Server info
echo "<h3>3. Server Configuration:</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not Set') . "<br>";
echo "CONTENT_LENGTH: " . ($_SERVER['CONTENT_LENGTH'] ?? 'Not Set') . "<br>";

// Test 4: Uploads directory
echo "<h3>4. Uploads Directory:</h3>";
$uploadDir = __DIR__ . '/uploads/';
echo "Path: " . $uploadDir . "<br>";
echo "Exists: " . (is_dir($uploadDir) ? 'Yes' : 'No') . "<br>";
echo "Writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "<br>";

// Test 5: Form test
echo "<h3>5. Test Form:</h3>";
?>
<form method="POST" enctype="multipart/form-data" action="">
    <input type="file" name="test_image" accept="image/*" required>
    <button type="submit">Upload Test Image</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['test_image'])) {
    echo "<h3>6. Upload Results:</h3>";
    echo "File name: " . $_FILES['test_image']['name'] . "<br>";
    echo "File size: " . $_FILES['test_image']['size'] . "<br>";
    echo "File type: " . $_FILES['test_image']['type'] . "<br>";
    echo "Error code: " . $_FILES['test_image']['error'] . "<br>";
    echo "Tmp name: " . $_FILES['test_image']['tmp_name'] . "<br>";
    echo "is_uploaded_file: " . (is_uploaded_file($_FILES['test_image']['tmp_name']) ? 'Yes' : 'No') . "<br>";
    
    echo "<h3>7. Attempting File Move:</h3>";
    $uploadDir = __DIR__ . '/uploads/';
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['test_image']['name']));
    $filename = time() . '_' . $safeName;
    $destAbsolute = $uploadDir . $filename;
    $destRelative = 'uploads/' . $filename;
    
    echo "Destination: " . $destAbsolute . "<br>";
    echo "move_uploaded_file attempt: ";
    
    if (move_uploaded_file($_FILES['test_image']['tmp_name'], $destAbsolute)) {
        echo "<span style='color: green;'>SUCCESS</span><br>";
        echo "File saved to: " . $destAbsolute . "<br>";
        echo "File exists: " . (file_exists($destAbsolute) ? 'Yes' : 'No') . "<br>";
    } else {
        echo "<span style='color: red;'>FAILED</span><br>";
        echo "Checking conditions:<br>";
        echo "- is_uploaded_file: " . (is_uploaded_file($_FILES['test_image']['tmp_name']) ? 'Yes' : 'No') . "<br>";
        echo "- tmp_name exists: " . (file_exists($_FILES['test_image']['tmp_name']) ? 'Yes' : 'No') . "<br>";
        echo "- uploadDir writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "<br>";
        echo "- uploadDir readable: " . (is_readable($uploadDir) ? 'Yes' : 'No') . "<br>";
    }
}
?>
