<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php   // form handling
if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Submitted (Admin)</title>
</head>
<body>
    <div class="wrapper">
    <div class="container">
        <div class="row g-3 mt-2">
            <div class="col">
                <h2>Change Submitted Successfully (Admin)</h2>
                <p>Your database change has been submitted successfully.</p>
                <p><a href="admin.php" class="btn btn-primary" style="margin-top:10px;">Go back to admin page</a></p>
            </div>
        </div>
    </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>