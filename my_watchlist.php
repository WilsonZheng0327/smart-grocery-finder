<?php session_start(); ?>

<?php
include 'header.php';

if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Function to get watchlist items for a user
function getWatchlistItems($user_id) {
    global $db;
    $query = "SELECT f.item, f.store, i.name AS item_name, i.brand, i.image, s.name AS store_name,
                 ns.notification_type, sa.sale_price, sa.start_date, sa.end_date 
                 FROM Favorites f
                 JOIN Item i ON f.item = i.item_ID
                 JOIN Store s ON f.store = s.store_ID
                 LEFT JOIN NotifySale ns ON f.user = ns.user AND f.item = ns.item AND f.store = ns.store
                 LEFT JOIN Sale sa ON f.item = sa.item AND f.store = sa.store
                 WHERE f.user = :user_id AND f.notification_enabled = TRUE";
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();
    $watchlist = $statement->fetchAll();
    $statement->closeCursor();
    return $watchlist;
}

$watchlist = getWatchlistItems($_SESSION["user_id"]);
// Group items by sale status
$onSaleItems = [];
$upcomingSaleItems = [];
$noSaleItems = [];
$currentDate = date('Y-m-d H:i:s');
foreach ($watchlist as $item) {
    if ($item['sale_price'] && $item['start_date'] <= $currentDate && $item['end_date'] >= $currentDate) {
        $onSaleItems[] = $item;
    } elseif ($item['sale_price'] && $item['start_date'] > $currentDate) {
        $upcomingSaleItems[] = $item;
    } else {
        $noSaleItems[] = $item;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Watchlist</title>
    <style>
        /* Add the CSS code here */
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .item-card {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }
        img {
            max-width: 300px;
            height: auto;
        }
        .progress-bar {
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress-bar .progress {
            height: 100%;
            background-color: #4caf50;
            transition: width 0.5s ease;
        }

        .progress-bar .progress.sale-ended {
            background-color: #f44336; /* Change color if sale has ended */
            height: 100%
        }
        .time-remaining {
            font-size: 18px;
            font-weight: bold;
            color: orange; /* Orange color for time remaining */
            margin-top: 10px;
        }

        .time-until-sale {
            font-size: 18px;
            font-weight: bold;
            color:  #4caf50; /* Green color for time until sale starts */
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <h2>My Watchlist</h2>

            <?php if (!empty($onSaleItems)): ?>
                <h3>On Sale</h3>
                <div class="item-grid"> 
                    <?php foreach ($onSaleItems as $item): ?>
                        <div class="item-card">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['item_name']; ?>"> 
                            <h3><?php echo $item['item_name']; ?></h3>
                            <p>Brand: <?php echo $item['brand']; ?></p>
                            <p>Store: <?php echo $item['store_name']; ?></p>
                            <p>Notification: <?php echo $item['notification_type']; ?></p>
                            <?php if ($item['sale_price']): ?>
                                <p>Sale Price: $<?php echo $item['sale_price']; ?></p>
                                <p>Sale Dates: <?php echo $item['start_date']; ?> - <?php echo $item['end_date']; ?></p>
                                <!-- Progress bar for time remaining in the sale -->
                                <?php
                                    $startDate = new DateTime($item['start_date']);
                                    $endDate = new DateTime($item['end_date']);
                                    $currentDate = new DateTime();
                                    $saleProgress = ($currentDate->getTimestamp() - $startDate->getTimestamp()) / ($endDate->getTimestamp() - $startDate->getTimestamp()) * 100;
                                    $saleProgress = max(0, min(100, $saleProgress)); // Ensure progress is between 0 and 100
                                ?>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo $saleProgress; ?>%"></div>
                                </div>
                                <!-- Time remaining until sale ends -->
                                <?php
                                    $saleEndTime = $endDate->getTimestamp() - $currentDate->getTimestamp();
                                    $saleEndTimeDays = floor($saleEndTime / (60 * 60 * 24));
                                    $saleEndTimeHours = floor(($saleEndTime % (60 * 60 * 24)) / (60 * 60));
                                    $saleEndTimeMinutes = floor(($saleEndTime % (60 * 60)) / 60);
                                ?>
                                <p class="time-remaining">Time Remaining: <?php echo $saleEndTimeDays . " days " . $saleEndTimeHours . " hours " . $saleEndTimeMinutes . " minutes"; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($upcomingSaleItems)): ?>
                <h3>Upcoming Sales</h3>
                <div class="item-grid">
                    <?php foreach ($upcomingSaleItems as $item): ?>
                        <div class="item-card">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['item_name']; ?>"> 
                            <h3><?php echo $item['item_name']; ?></h3>
                            <p>Brand: <?php echo $item['brand']; ?></p>
                            <p>Store: <?php echo $item['store_name']; ?></p>
                            <p>Notification: <?php echo $item['notification_type']; ?></p>
                            <?php if ($item['sale_price']): ?>
                                <p>Sale Price: $<?php echo $item['sale_price']; ?></p>
                                <p>Sale Dates: <?php echo $item['start_date']; ?> - <?php echo $item['end_date']; ?></p>
                                <!-- Progress bar for time left until the sale -->
                                <?php
                                    $startDate = new DateTime($item['start_date']);
                                    $currentDate = new DateTime();
                                    $timeLeft = $currentDate->diff($startDate);
                                    $timeLeftPercent = ($timeLeft->days * 24 * 60 + $timeLeft->h * 60 + $timeLeft->i) / (($timeLeft->days + 1) * 24 * 60) * 100;
                                    $timeLeftPercent = max(0, min(100, $timeLeftPercent)); // Ensure progress is between 0 and 100
                                ?>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo $timeLeftPercent; ?>%"></div>
                                </div>
                                <!-- Time remaining until sale starts -->
                                <?php
                                    $saleStartTime = $startDate->getTimestamp() - $currentDate->getTimestamp();
                                    $saleStartTimeDays = floor($saleStartTime / (60 * 60 * 24));
                                    $saleStartTimeHours = floor(($saleStartTime % (60 * 60 * 24)) / (60 * 60));
                                    $saleStartTimeMinutes = floor(($saleStartTime % (60 * 60)) / 60);
                                ?>
                                <p class="time-until-sale">Time Until Sale Starts: <?php echo $saleStartTimeDays . " days " . $saleStartTimeHours . " hours " . $saleStartTimeMinutes . " minutes"; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($noSaleItems)): ?>
                <h3>No Current Sale</h3>
                <div class="item-grid">
                    <?php foreach ($noSaleItems as $item): ?>
                        <div class="item-card">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['item_name']; ?>"> 
                            <h3><?php echo $item['item_name']; ?></h3>
                            <p>Brand: <?php echo $item['brand']; ?></p>
                            <p>Store: <?php echo $item['store_name']; ?></p>
                            <p>Notification: <?php echo $item['notification_type']; ?></p>
                            <?php if ($item['sale_price']): ?>
                                <p>Sale Price: $<?php echo $item['sale_price']; ?></p>
                                <p>Sale Dates: <?php echo $item['start_date']; ?> - <?php echo $item['end_date']; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($watchlist)): ?>
                <p>You have no items on your watchlist. Add items to your favorites and enable notifications to start tracking them here.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>