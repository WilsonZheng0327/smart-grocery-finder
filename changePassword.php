<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php

if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$user_information = getUserInformation($_SESSION["user_id"]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    if (!empty($_POST['changePasswordBtn'])) 
    {
        $result = changePassword($_SESSION["user_id"], $_POST['new_password'], $_POST['new_password_conf']);
        if ($result["success"]) {
            echo "Success!";
        } else {
            if ($result["cause"] == "different") {
                echo "Passwords don't match.";
            }
        }
    } 
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Information</title>
    <style> 
        label {
            width: 120px;
        }
        .info-box {
            margin-bottom: 10px;
        }
        h2 {
            margin-bottom: 20px;
        }
        body {
            padding-left: 
        }
        #changePasswordBtn {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
    <div class="container">
        <h2>User Information</h2>
        <form method="post" action="changePassword.php">
            <div class=info-box>
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password"name="new_password"><br>
            </div>
            <div class=info-box>
                <label for="new_password_conf">Confirmation:</label>
                <input type="password" id="new_password_conf" name="new_password_conf"><br>
            </div>
            <input type="submit" value="Change Password" id="changePasswordBtn" name="changePasswordBtn" class="btn btn-primary"/>
        </form>
    </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>