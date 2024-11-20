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
$list_of_results = getHistory($_SESSION["user_id"]);
?>
<!DOCTYPE html>
<html>
<body>
    <div class="wrapper">
    <div class="container">
        <h3 style="margin-bottom:20px;">My Purchase History</h3>
        <div class="row justify-content-center">  
                <?php if (empty($list_of_results)): ?>
                  <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                  <thead>
                      <tr style="background-color:#B0B0B0">
                          <th width="10%"><b>ItemID</b></th>
                          <th width="20%"><b>Name</b></th>       
                          <th width="20%"><b>Brand</b></th>
                          <th width="20%"><b>Item Category</b></th>     
                          <th width="10%"><b>Quantity</b></th>   
                          <th width="10%"><b>Stores</b></th>
                      </tr>
                  </thead>
                    <tr>
                        <td colspan="5" style="text-align: center;">No results found</td>
                    </tr>
                  </table>
                  <?php else: ?>
                <div id="historyContainer"></div>
                <?php endif; ?>
        </div>   
        </div>
    </div>   

    <script>
      var list_of_results = <?php echo json_encode($list_of_results); ?>;
      function generateHistoryTable(){
            let html = `<table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                  <thead>
                      <tr style="background-color:#B0B0B0">
                          <th width="10%"><b>ItemID</b></th>
                          <th width="20%"><b>Name</b></th>       
                          <th width="30%"><b>Brand</b></th>
                          <th width="30%"><b>Item Category</b></th>     
                          <th width="10%"><b>Quantity</b></th>   
                      </tr>
                  </thead>`;

            list_of_results.forEach(item_info => {
            html += `<tr>
            <td>${item_info.item}</td>
            <td>${item_info.name}</td>
            <td>${item_info.brand}</td>
            <td>${item_info.item_category}</td>
            <td>${item_info.total_quantity}</th> 
            </tr>`;
            });

            html += `
            </table>`;

            document.getElementById('historyContainer').innerHTML = html;
        }

        console.log(list_of_results[0]);
        try{
            generateHistoryTable();
        }catch(error){
            console.log(error);
        }
    </script>
</body>
</html>

<?php include 'footer.php'; ?>