<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php
$isFormSubmitted = false;

// Login form submission handling
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['signup'])) {

        $error = "";
        
        if (empty($_POST['first-name'])) {
            $error .= "<p>First name is required.</p>";
        }
        if (empty($_POST['last-name'])) {
            $error .= "<p>Last name is required.</p>";
        }
        if (empty($_POST['email'])) {
            $error .= "<p>Email is required.</p>";
        }
        if (empty($_POST['password'])) {
            $error .= "<p>Password is required.</p>";
        }
        if (empty($_POST['password-conf'])) {
            $error .= "<p>Confirm password is required.</p>";
        }
        
        $isFormSubmitted = true;

        // if everything filled, try signup
        if (empty($error)) {
            $result = signUp($_POST['first-name'], $_POST['last-name'], $_POST['email'], $_POST['password'], $_POST['password-conf']);
            if ($result["success"]) {
                echo "Success!";
                echo "<script>window.location.href = 'login.php';</script>";
                exit;
            } else {
                if ($result["cause"] == "exist") {
                    $error .= "The email has been registered already.";
                } else if ($result["cause"] == "password") {
                    $error .= "Passwords don't match.";
                } else {
                    $error .= "Unknown error...";
                }
            }
        } else {
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Wilson Zheng">
    <meta name="description" content="Smart Grocery Finder App (CS 4750)">
    <title>Signup</title>
</head>

<body>
    <div class="wrapper">
    <div class="container">
        <h2 style="margin-bottom:20px;">Signup</h2>
        <?php if($isFormSubmitted && isset($error)) echo "<p>$error</p>"; ?>
        <form method="post" action="signup.php" onsubmit="return validateInput()">
            <label for="first-name">First Name:</label><br>
            <input type="text" id="first-name" name="first-name"><br>
            <label for="last-name">Last Name:</label><br>
            <input type="text" id="last-name" name="last-name"><br>
            <label for="email">Email:</label><br>
            <input type="text" id="email" name="email"><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password"><br>
            <label for="password-conf">Confirm Password:</label><br>
            <input type="password" id="password-conf" name="password-conf"><br>
            <div style="margin-top:20px;">
                <input type="submit" name="signup" value="Signup">
                <a style="margin-left:20px;" href="login.php">Login</a>
            </div>
        </form>
    </div>  
    </div>
</body>
</html>

<?php include 'footer.php'; ?>