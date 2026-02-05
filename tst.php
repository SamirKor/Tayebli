<?php
echo "<h2>Test Upload</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    echo "POST:\n";
    print_r($_POST);
    echo "\nFILES:\n";
    print_r($_FILES);
    echo "</pre>";
    
    if (isset($_FILES['recipe_image'])) {
        echo "File found!<br>";
        echo "Error code: " . $_FILES['recipe_image']['error'] . "<br>";
    } else {
        echo "File NOT found in \$_FILES<br>";
    }
}
?>

<form method="post" enctype="multipart/form-data">
    <input type="text" name="test" value="test"><br>
    <input type="file" name="recipe_image"><br>
    <button type="submit">Submit</button>
</form>