<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php   // form handling

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
// echo $_SESSION["user_id"];
$list_of_results = getReviews($_SESSION["user_id"]);

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (!empty($_GET['deleteBtn'])) 
    {
        deleteReview($_GET['reviewID']);
        echo "<script>window.location.href = 'my_reviews.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<body>
    <div class="wrapper">
    <div class="container">
        <h3 style="margin-bottom:20px;">My Reviews</h3>
        <div class="row justify-content-center">  
            <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                <thead>
                    <tr style="background-color:#B0B0B0">
                        <th width="3%"><b>ID</b></th>
                        <th width="3%"><b>Store</b></th> 
                        <th width="5%"><b>Name</b></th>        
                        <th width="3%"><b>Item</b></th>        
                        <th width="5%"><b>Name</b></th>        
                        <th width="5%"><b>Rating</b></th>        
                        <th width="20%"><b>Comment</b></th>        
                        <th width="15%"><b>Image</b></th>   
                        <th width="10%"><b>Review Time</b></th>
                        <th width="5%"><b>Delete</b></th>
                    </tr>
                </thead>
                <?php if (empty($list_of_results)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No results found</td>
                    </tr>
                <?php endif; ?>

                <?php $reviewID = 1; ?>
                <?php foreach ($list_of_results as $review_info): ?>
                    <tr>
                        <td><?php echo $reviewID; ?></td>
                        <td><?php echo $review_info['store']; ?></td>
                        <td><?php echo $review_info['store_name']; ?></td>
                        <td><?php echo $review_info['item']; ?></td>
                        <td><?php echo $review_info['item_name']; ?></td>
                        <td><?php echo $review_info['rating']; ?></td>
                        <td><?php echo $review_info['comment']; ?></td>
                        <td><?php echo $review_info['image']; ?></td>
                        <td><?php echo $review_info['review_time']; ?></td>
                        <td>
                            <form method="get" action="my_reviews.php">
                                <input type="hidden" name="reviewID" value="<?php echo $review_info['review_ID']; ?>">
                                <input type="submit" value="Delete" id="deleteBtn" name="deleteBtn" class="btn btn-primary"/>
                            </form>
                        </td>
                    </tr>
                    <?php $reviewID++; ?>
                <?php endforeach; ?>
            </table>
        </div>   
        </div>
    </div>   
</body>

</html>

<?php include 'footer.php'; ?>