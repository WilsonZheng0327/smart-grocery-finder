<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php
if ($_SESSION) {
    echo "<script>window.location.href = 'grocery.php';</script>";
    exit;
}
// Login form submission handling
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['login'])) {
        $result = checkLogin($_POST['email'], $_POST['password']);
        if ($result["success"] == true) {
            $_SESSION['user_id'] = $result["user"]['user_ID'];
            echo "<script>window.location.href = 'grocery.php';</script>";
            exit;
        } else if ($result["cause"] == "exist") {
            echo "Account doesn't exist.";
        } else if ($result["cause"] == "password") {
            echo "Password is incorrect";
        } else {
            echo "Unknown error...";
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
    <title>Login</title>
</head>
<body>
    <div class="wrapper">
    <div class="container">
        <h2 style="margin-bottom:20px;">Login</h2>
        <?php if(isset($error)) echo "<p>$error</p>"; ?>
        <!-- <form method="post" action="php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"> -->
        <form method="post" action="login.php">
            <label for="email">Email:</label><br>
            <input type="text" id="email" name="email"><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password"><br>
            <div style="margin-top:20px;">
                <input type="submit" name="login" value="Login">
                <a style="margin-left:20px;" href="signup.php">Signup</a>
            </div>
        </form>
    </div>
    </div>  
</body>
</html>

<?php include 'footer.php'; ?>