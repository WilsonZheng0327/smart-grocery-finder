<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php   // form handling

if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
// echo $_SESSION["user_id"];
$list_of_results = getMemberships($_SESSION["user_id"]);

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (!empty($_GET['deleteBtn'])) 
    {
        deleteMembership($_SESSION["user_id"], $_GET['storeID']);
        echo "<script>window.location.href = 'my_memberships.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<body>
    <div class="wrapper">
    <div class="container">
        <h3 style="margin-bottom:20px;">My Memberships</h3>
        <div class="row justify-content-center">  
            <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                <thead>
                    <tr style="background-color:#B0B0B0">
                        <th width="5%"><b>ID</b></th>
                        <th width="5%"><b>StoreID</b></th> 
                        <th width="10%"><b>Name</b></th>
                        <th width="5%"><b>Street #</b></th>        
                        <th width="15%"><b>Street Name</b></th>        
                        <th width="15%"><b>City</b></th>   
                        <th width="5%"><b>State</b></th>
                        <th width="10%"><b>Zipcode</b></th>
                        <th width="10%"><b></b></th>
                    </tr>
                </thead>
                <?php if (empty($list_of_results)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No results found</td>
                    </tr>
                <?php endif; ?>

                <?php $membershipID = 1; ?>
                <?php foreach ($list_of_results as $membership_info): ?>
                    <tr>
                        <td><?php echo $membershipID; ?></td>
                        <td><?php echo $membership_info['store']; ?></td>
                        <td><?php echo $membership_info['name']; ?></td>
                        <td><?php echo $membership_info['street_num']; ?></td>
                        <td><?php echo $membership_info['street_name']; ?></td>
                        <td><?php echo $membership_info['city']; ?></td>
                        <td><?php echo $membership_info['state']; ?></td>
                        <td><?php echo $membership_info['zipcode']; ?></td>
                        <td>
                            <form method="get" action="my_memberships.php">
                                <input type="hidden" name="storeID" value="<?php echo $membership_info['store']; ?>">
                                <input type="submit" value="Delete" id="deleteBtn" name="deleteBtn" class="btn btn-primary"/>
                            </form>
                        </td>
                    </tr>
                    <?php $membershipID++; ?>
                <?php endforeach; ?>
            </table>
        </div>   
        </div>
    </div>   
</body>

</html>

<?php include 'footer.php'; ?>