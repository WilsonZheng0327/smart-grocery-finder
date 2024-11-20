<?php session_start(); ?>

<?php
include 'header.php';

// Check if user is logged in
if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Check if the request method is POST and the toggle_notification button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["toggle_notification"])) {
    // Check if the required parameters are set
    if (isset($_POST["item_id"]) && isset($_POST["store_id"])) {
        // Sanitize input data
        $item_id = filter_var($_POST["item_id"], FILTER_SANITIZE_NUMBER_INT);
        $store_id = filter_var($_POST["store_id"], FILTER_SANITIZE_NUMBER_INT);

        // Fetch the current notification status from the database
        $query = "SELECT notification_enabled FROM Favorites WHERE user = :user_id AND item = :item_id AND store = :store_id";
        $statement = $db->prepare($query);
        $statement->bindValue(':user_id', $_SESSION["user_id"], PDO::PARAM_INT); // Assuming user_id is stored in the session
        $statement->bindValue(':item_id', $item_id, PDO::PARAM_INT);
        $statement->bindValue(':store_id', $store_id, PDO::PARAM_INT);
        $statement->execute();
        $current_status = $statement->fetchColumn();
        $statement->closeCursor();

        // Toggle the notification status
        $new_status = $current_status ? 0 : 1; // Toggle the current status

        // Update the notification status in the database
        $query = "UPDATE Favorites SET notification_enabled = :new_status WHERE user = :user_id AND item = :item_id AND store = :store_id";
        $statement = $db->prepare($query);
        $statement->bindValue(':new_status', $new_status, PDO::PARAM_INT);
        $statement->bindValue(':user_id', $_SESSION["user_id"], PDO::PARAM_INT); // Assuming user_id is stored in the session
        $statement->bindValue(':item_id', $item_id, PDO::PARAM_INT);
        $statement->bindValue(':store_id', $store_id, PDO::PARAM_INT);
        $success = $statement->execute();
        $statement->closeCursor();

        // Check if the update was successful
        if ($success) {
            // Redirect back to favorites.php to update the UI
            echo "<script>window.location.href = 'my_favorites.php';</script>";

            exit;
        } else {
            // Handle error, if needed
        }
    }
}

$user_information = getUserInformation($_SESSION["user_id"]);

// Function to fetch favorite items for a user
function getFavoriteItems($user_id) {
    global $db;
    $query = "SELECT f.item, f.store, f.added_date, f.notification_enabled, i.name AS item_name, i.brand, i.image, s.name AS store_name
              FROM Favorites f
              JOIN Item i ON f.item = i.item_ID
              JOIN Store s ON f.store = s.store_ID
              WHERE f.user = :user_id
              ORDER BY f.added_date DESC";
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();
    $favorites = $statement->fetchAll();
    $statement->closeCursor();
    return $favorites;
}

// Function to remove a favorite item
function removeFavoriteItem($user_id, $item_id, $store_id) {
    global $db;
    $query = "DELETE FROM Favorites WHERE user = :user_id AND item = :item_id AND store = :store_id";
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->bindValue(':item_id', $item_id, PDO::PARAM_INT);
    $statement->bindValue(':store_id', $store_id, PDO::PARAM_INT);
    $success = $statement->execute();
    $statement->closeCursor();
    return $success;
}

// Get favorite items for the current user
$favorites = getFavoriteItems($_SESSION["user_id"]);

// Handle removal of favorite items
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_favorite'])) {
    $item_id = $_POST['item_id'];
    $store_id = $_POST['store_id'];
    if (removeFavoriteItem($_SESSION["user_id"], $item_id, $store_id)) {
        // Item removed successfully, refresh favorites list
        $favorites = getFavoriteItems($_SESSION["user_id"]);
        echo "Item removed successfully.";

    } else {
        echo "<p>Failed to remove item from favorites.</p>";
    }
}

// Function to toggle notification status
function toggleNotificationStatus($user_id, $item_id, $store_id, $current_status) {
    global $db;
    $new_status = $current_status ? 0 : 1; // Toggle the current status
    $query = "UPDATE Favorites SET notification_enabled = :new_status WHERE user = :user_id AND item = :item_id AND store = :store_id";
    $statement = $db->prepare($query);
    $statement->bindValue(':new_status', $new_status, PDO::PARAM_INT);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->bindValue(':item_id', $item_id, PDO::PARAM_INT);
    $statement->bindValue(':store_id', $store_id, PDO::PARAM_INT);
    $success = $statement->execute();
    $statement->closeCursor();
    return $success;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Favorites</title>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            display: inline-block;
            margin: 10px;
            text-align: center;
            vertical-align: top;
        }
        img {
            max-width: 300px;
            height: auto;
        }
        p {
            margin: 5px 0;
        }
        .notification-btn {
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            color: white;
            border-radius: 5px;
        }
        .enable-notification {
            background-color: #4CAF50; /* Green */
        }
        .disable-notification {
            background-color: #008CBA; /* Blue */
        }
        .remove-favorite {
            background-color: #f44336; /* Red */
        }
        .button-container button {
            margin-bottom: 5px;
        }
        /*.button-container button[name="remove_favorite"] {
            padding: 2px 5px; 
        }*/
    </style>
</head>
<body>
    <div class="wrapper">
    <div class="container">
        <h2>My Favorites</h2>
        <?php if (count($favorites) > 0): ?>
            <ul>
                <?php foreach ($favorites as $favorite): ?>
                    <li>
                        <img src="<?php echo $favorite['image']; ?>" alt="<?php echo $favorite['item_name']; ?>">
                        <div class="favorite-item-content">
                            <p><strong><?php echo $favorite['item_name']; ?></strong> (<?php echo $favorite['brand']; ?>)</p>
                            <!-- <p>Store: <?php echo $favorite['store_name']; ?></p> -->
                            <!-- <p>Added on: <?php echo $favorite['added_date']; ?></p> -->
                            <div class="button-container">
                                <?php if ($favorite['notification_enabled']): ?>
                                    <form method="post">
                                        <input type="hidden" name="item_id" value="<?php echo $favorite['item']; ?>">
                                        <input type="hidden" name="store_id" value="<?php echo $favorite['store']; ?>">
                                        <button class="notification-btn disable-notification" type="submit" name="toggle_notification">Disable Notification</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post">
                                        <input type="hidden" name="item_id" value="<?php echo $favorite['item']; ?>">
                                        <input type="hidden" name="store_id" value="<?php echo $favorite['store']; ?>">
                                        <button class="notification-btn enable-notification" type="submit" name="toggle_notification">Enable Notification</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post">
                                    <input type="hidden" name="item_id" value="<?php echo $favorite['item']; ?>">
                                    <input type="hidden" name="store_id" value="<?php echo $favorite['store']; ?>">
                                    <button class="notification-btn remove-favorite" type="submit" name="remove_favorite">Remove</button>
                                </form>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have no favorite items yet.</p>
        <?php endif; ?>
    </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
                $(".enable-notification-btn, .disable-notification-btn").click(function() {
                    var itemId = $(this).data("item-id");
                    var storeId = $(this).data("store-id");
                    var action = $(this).hasClass("enable-notification-btn") ? "enable" : "disable";
                    $.ajax({
                        url: "toggle-notification.php", // PHP script that handles the toggle action
                        method: "POST",
                        data: { item_id: itemId, store_id: storeId, action: action },
                        success: function(response) {
                            // Handle success response, e.g., update UI
                            console.log(response);
                        },
                        error: function(xhr, status, error) {
                            // Handle error response
                            console.error(xhr.responseText);
                        }
                    });
                });
            });
    </script>
</body>
</html>

<?php include 'footer.php'; ?>