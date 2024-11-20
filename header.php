<?php 
require_once("connect-db.php");
require_once("request-db.php");
?>

<?php
$isAdmin = false;
if ($_SESSION) {
    // echo "Session started";
    $isAdmin = checkAdmin($_SESSION['user_id']);
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout-btn'])) {
    echo "Logging out";

    $_SESSION = array();
    session_destroy();

    echo "<script>window.location.href = 'login.php';</script>";
    exit;
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
        <title>Smart Grocery Finder</title>
        <!-- header.php is the only file that links to CSS sheets -->
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">  
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">  
        <link rel="stylesheet" type="text/css" href="css/grocery.css">
        <style>
        .logo {
            text-decoration: none;
            color: inherit;
        }
        .logo:hover {
            text-decoration: none; /* Remove underline on hover */
            color: inherit; /* Use the color inherited from its parent on hover */
        }
        </style>
    </head>

    <body>
        <header>
            <div class="header-container">
                <?php if ($isAdmin): ?>
                    <a class="logo" href="grocery.php"><h1>Smart Grocery Finder (Admin)</h1></a>
                <?php else: ?>
                    <a class="logo" href="grocery.php"><h1>Smart Grocery Finder</h1></a>
                <?php endif; ?>
                <div class="header-buttons">
                    <?php if ($isAdmin): ?>
                        <a href="admin.php" id="adminBtn" name="adminBtn" 
                        class="btn btn-primary" title="admin portal">Admin Page</a>
                    <?php endif; ?>
                    <?php if ($_SESSION): ?>
                        <a href="grocery.php" id="groceryBtn" name="groceryBtn" 
                        class="btn btn-secondary" title="main page">Groceries</a>
                        <a href="request_change.php" id="requestBtn" name="requestBtn" 
                        class="btn btn-primary" title="request change form">Request Changes</a>
                        <a href="write_review.php" id="reviewBtn" name="reviewBtn" 
                        class="btn btn-primary" title="write review form">Write A Review</a>
                        <div class="profile-dropdown">
                            <button class="profile-btn" onclick="toggleProfileMenu(event)"></button>
                            <div class="profile-menu" id="profileMenu">
                                <a href="my_profile.php">Edit Profile</a>
                                <a href="my_favorites.php">My Favorites</a>
                                <a href="my_watchlist.php">My Watchlist</a>
                                <a href="my_reviews.php">My Reviews</a>
                                <a href="my_history.php">My History</a>
                                <a href="my_memberships.php">My Memberships</a>
                                <form method="post" class="logout-form" action="<?php $_SERVER['PHP_SELF'] ?>" onsubmit="return validateInput()">
                                    <input type="submit" class="logout-btn" name="logout-btn" value="Logout">
                                </form> 
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <script>
            function toggleProfileMenu() {
                event.stopPropagation();
                var menu = document.getElementById("profileMenu");
                menu.style.display = menu.style.display === "block" ? "none" : "block";
                console.log("toggleProfileMenu function called");
            }

            // called whenever a click
            window.onclick = function(event) {
                var menu = document.getElementById("profileMenu");
                if (event.target !== menu && event.target.parentNode !== menu) {
                    menu.style.display = "none";
                }
            }
        </script>
    </body>
</html>