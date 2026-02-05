<?php


session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';



$errors1 = [];
$errors2 = [];

$success = '';

// Handle form submissions before any HTML output so we can redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createbtn'])) {
    // Get and sanitize inputs
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $errors1[] = "Username is required";
    } elseif (strlen($username) < 8) {
        $errors1[] = "Username must be at least 8 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors1[] = "Username can only contain letters, numbers, and underscores";
    }
    
    if (empty($email)) {
        $errors1[] = "Email is required";
    } elseif (!validate_email($email)) {
        $errors1[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors1[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors1[] = "Password must be at least 8 characters";
    } elseif (!validate_password($password)) {
        $errors1[] = "Password must contain at least one uppercase letter, one lowercase letter, and one number";
    }
    
    if ($password !== $confirm_password) {
        $errors1[] = "Passwords do not match";
    }
    
    // Check if username or email already exists
    if (empty($errors1)) {
        $check_sql = "SELECT userid FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($connection, $check_sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors1[] = "Username or email already exists";
        }
        mysqli_stmt_close($stmt);
    }
    
    // If no errors1, register user and redirect
    if (empty($errors1)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, passwords, email ) 
                VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $email);

        if (mysqli_stmt_execute($stmt)) {
            $user_id = mysqli_insert_id($connection);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            mysqli_stmt_close($stmt);
            header('Location: index.php');
            echo "Success";
            exit;
        } else {
            $errors1[] = "Registration failed. Please try again.";
        }
    }
}
elseif($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize_input($_POST['email2'] ?? '');
    $password = $_POST['password2'] ?? '';

    if (empty($email)) {
        $errors2[] = "Email is required";
    } elseif (!validate_email($email)) {
        $errors2[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors2[] = "Password is required";
    }

    if (empty($errors2)) {
        $sql = "SELECT userid, username, passwords FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $user_id, $username, $hashed_password);
            mysqli_stmt_fetch($stmt);

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                mysqli_stmt_close($stmt);
                header('Location: index.php');
                exit;
            } else {
                $errors2[] = "Incorrect password.";
            }
        } else {
            $errors2[] = "No account found with that email.";
        }

        mysqli_stmt_close($stmt);
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login_page</title>
    <link rel="stylesheet" href="CSS/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <div class="container">
        <div class="leftside">
            <div class="logo">
                <a href="index.php" style="text-decoration: none;"> <!--sends to the home page-->
                    <svg class="chef-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                    <line x1="6" y1="17" x2="18" y2="17"/>
                    </svg>
                    <p style="color: black; font-size: 36px; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"> TAYEBLI </p>
                </a>

            </div>

            <p style="font-size: 30px;">Join the cooking community</p>

            <p style="font-size: 20px; color: #202020; margin-bottom: 0;">Discover thousands of recipes, share your
                creations, and connect with</p><p style="font-size: 20px; color: #202020; margin-bottom: 30px;"> fellow food lovers.</p>

            <img src="https://i.pinimg.com/originals/39/63/7c/39637c136b3d9c0805eba7c4af8e335b.jpg" alt="spoons" style="border-radius: 20px; height: 70%; width: 80%; object-fit: cover;">
        </div>

        <div class="rightside">
            
            <div class="logo">
                
                    <img src="..\assets\logo.png" alt="logo" style="height: 40%; width: 40%;">
                    <p style="color: black; font-size: 36px; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"> TAYEBLI </p>

            </div>
            <p style="font-size: 30px; margin-bottom: 10px;">Welcome!</p>
            <p style="font-size: 20px; margin-top: 10px;">Sign in to your account or create a new one</p>

            <!-- Open sign in modal-->
            <div class="signinupbtns">
                <button type="button" id="signinBtn" class="active" style="margin-right: 10px;"
                    onclick="openLoginModal() ">
                    Sign In
                </button>

                <!-- Open sign up modal -->
                <button type="button" id="signupBtn">
                    Sign Up
                </button>
            </div>


            <form id="sign_up_form" class="sign_in_up_forms" method="POST">
                <label for="name">User Name</label>


                <div class="inputslot">
                    <i class="fa-solid fa-user"></i>
                    <input id="name" type="text" placeholder="Enter your full name" name="username" >
                    
                </div>
                <span id="nameError" style="color: #FF914D; font-size: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"></span>

                <label for="email">Email</label>


                <div class="inputslot">
                    <i class="fa-solid fa-envelope"></i>
                    <input id="email" type="text" placeholder="Enter ur email" name="email" >
                    
                </div>
                <span id="emailError" style="color: #FF914D; font-size: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"></span>

                <label for="Password">Password</label>


                <div class="inputslot passwordslot" style="display: flex; flex-direction: row;">
                    <i class="fa-solid fa-lock"></i>
                    <input id="password1" type="password" placeholder="Create a password" name="password" >
                    
                    <button type="button" onclick="togglepassword('password1'); togglepassword('password2');" style="background-color: transparent; border: none;"><i class="fa-solid fa-eye" style="margin-left: auto;"></i></button>
                </div>
                    <span id="passwordError1" style="color: #FF914D; font-size: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"></span>

                <label for="comfirmation">Confirm Password</label>


                <div class="inputslot">
                    <i class="fa-solid fa-lock"></i>
                    <input id="password2" type="password" placeholder="Confirm Password" name="confirm_password" >
                    
                </div>
                <span id="passwordError2" style="color: #FF914D; font-size: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"></span>
                <div class="agree">
                    <input type="checkbox" id="agree" required>
                    <p>I agree to <a href="" style="color: #FF914D; font:bold; text-decoration: none;">Terms and
                            Services <span style="color: black;">and</span> Privacy policy</a> </p>

                </div>

                <button type="submit" id="Createbtn" class="submit-buttons" name="createbtn">Create account</button>
            </form>


            <form id="sign_in_form" class="sign_in_up_forms" method="POST">
                <label for="email">Email</label>
                <div class="inputslot">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" placeholder="Enter ur email" id="email2" name="email2">

                </div>
                <span id="emailError1"style="font-size: 14px; color: #FF914D; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"></span>

                <label for="password">Password</label>
                <div class="inputslot" style="display: flex; flex-direction: row; justify-content: space-between;">
                    <div>
                        <i class="fa-solid fa-lock"></i>
                        <input id="password" type="password" placeholder="Create a password" style="width: 200px;" name="password2">
                    </div>

                    
                    <button type="button" onclick="togglepassword('password')" style="background-color: transparent; border: none;"><i class="fa-solid fa-eye" style="margin-left: auto;"></i></button>
                </div>
                <span id="passwordError" style="font-size: 14px; color: #FF914D; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"></span>
                <div class="agree">
                    <input type="checkbox" >
                    <p>Remember Me</p>
                    <a href=""
                        style="text-decoration: none; color: #FF914D; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin-left: 100px;">Forgot
                        ur Password?</a>
                </div>

                <?php
                foreach($errors2 as $error) {
                    echo "<p style='color:#FF914D; font-size: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;'>$error</p>";
                }
                ?>

                <button type="submit" id="Sign_in_btn" class="submit-buttons" name="login">Sign In</button>

            </form>

            <button id="backhomebtn"><i class="fas fa-arrow-left"></i> Back home</button>
        </div>
    </div>

    <script src="JS/login.js"></script>


</body>

</html>

<?php
foreach($errors1 as $error) {
    echo "<p style='color:#FF914D; font-size: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;'>$error</p>";
}
mysqli_close($connection);

?>