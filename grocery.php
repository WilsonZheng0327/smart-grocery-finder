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
$list_of_results = [];
$result_type = "";
$search_input = "";
// handles all GET requests
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (!empty($_GET['searchBtn'])) 
    {
        if($_GET['searchType']=="store" && $_GET['searchInput']=="storeNearMe"){
            $list_of_results=searchStoreNearMe($_SESSION['user_id']);
            $result_type = $_GET['searchType'];
        }else{
            // search from database
            // $_GET['searchType'] decides which database it searches from
            // $_GET['searchInput'] decides the keyword in the SQL WHERE clause
            $list_of_results = searchReqeust($_GET['searchType'], $_GET['searchInput']);
            // echo count($list_of_results);
            $result_type = $_GET['searchType'];
            $search_input = $_GET['searchInput'];
        }
    } 
    else if (!empty($_GET['clearBtn'])) 
    {
        // clears the search results
        $list_of_results = [];
        $search_input = "";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Process the data (this is where you would add your logic)
    if (isset($data['itemID']) && isset($data['storeID'])) {
        requestChangeAddFavorites($_SESSION['user_id'],$data['itemID'],$data['storeID']);
    } else if(isset($data['itemID'])){
        requestChangeAddHistory($_SESSION['user_id'],$data['itemID']);
    }
    else {
        http_response_code(400); 
    }
}
?>
<!DOCTYPE html>
<html>
<body>  
    <div class="wrapper">
    <div class="container">
        <!-- <form method="get" action="<?php $_SERVER['PHP_SELF'] ?>" onsubmit="return validateInput()"> -->
        <form method="get" action="grocery.php" onsubmit="return validateInput()">
            <table style="width:98%">
                <tr>
                    <td width="30%">   
                        <div class='mb-3'>
                            Search from:
                            <select class='form-select' id='searchType' name='searchType'>
                                <option value='' <?php if ($result_type == '') echo 'selected'; ?>>
                                </option>
                                <option value='item' <?php if ($result_type == 'item') echo 'selected'; ?>>
                                    Item
                                </option>
                                <option value='store' <?php if ($result_type == 'store') echo 'selected'; ?>>
                                    Store
                                </option>
                                <option value='storeItemsID' <?php if ($result_type == 'storeItemsID') echo 'selected'; ?>>
                                    Items in ONE store (by StoreID)
                                </option>
                                <option value='itemInStores' <?php if ($result_type == 'itemInStores') echo 'selected'; ?>>
                                    ONE Item in stores (by ItemID)
                                </option>
                            </select>
                        </div>
                    </td>
                    <td width="60%">
                        <div class="mb-3">
                            Type in your keyword:
                            <input type='text' class='form-control' id='searchInput' name='searchInput'
                            value="<?php echo $search_input; ?>" />
                        </div>
                    </td>
                    <td width="10%">
                        <div class="mb-3">
                            <img src="https://iili.io/JglLem7.png" id="getstorelocation" width="50" height="50" class="btn" style="padding: 2px 2px; font-size: 10px; margin-top: 18px; margin-left: 13px; cursor: pointer;">
                        </div>
                    </td>
                </tr>
            </table>
            <div class="row g-3 mx-auto">    
                <div class="col-4 d-grid ">
                    <input type="submit" value="Search" id="searchBtn" name="searchBtn" class="btn btn-primary"
                        title="Search for an item" />                  
                </div>	       
                <div class="col-4 d-grid ">
                    <input type="submit" value="Clear" id="clearBtn" name="clearBtn" class="btn btn-dark"
                        title="Clear search results" />                  
                </div>	 
            </div>  
        </form> 
    </div>

    <div class="container">
        <h3>Search Results</h3>
        <div class="row justify-content-center">  
            <?php if ($result_type == ""): ?>

            <?php elseif ($result_type == "item"): ?>
                
            <?php elseif ($result_type == "store"): ?>

            <?php elseif ($result_type == "storeItemsID"): ?>
                
            <?php elseif ($result_type == "itemInStores"): ?>
                
            <?php endif; ?>

            <?php if (empty($list_of_results)): ?>
                <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                    <tr>
                        <td colspan="5" style="text-align: center;">No results found</td>
                    </tr>
                </table>
            <?php elseif ($result_type == "item"): ?>
                <div id="itemContainer"> </div>
            <?php elseif ($result_type == "store"): ?>
                <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                <thead>
                    <tr style="background-color:#B0B0B0">
                        <th width="10%"><b>StoreID</b></th>
                        <th width="20%"><b>Name</b></th> 
                        <th width="15%"><b>Store Category</b></th>        
                        <th width="10%"><b>Street #</b></th>        
                        <th width="20%"><b>Street Name</b></th>        
                        <th width="10%"><b>City</b></th>        
                        <th width="5%"><b>State</b></th>        
                        <th width="10%"><b>ZIP Code</b></th>   
                        <th width="10%"><b>Items</b></th>
                    </tr>
                </thead>
                <?php foreach ($list_of_results as $store_info): ?>
                    <tr>
                        <td><?php echo $store_info['store_ID']; ?></td>
                        <td><?php echo $store_info['name']; ?></td>
                        <td><?php echo $store_info['store_category']; ?></td>
                        <td><?php echo $store_info['street_num']; ?></td>
                        <td><?php echo $store_info['street_name']; ?></td>
                        <td><?php echo $store_info['city']; ?></td>
                        <td><?php echo $store_info['state']; ?></td>
                        <td><?php echo $store_info['zipcode']; ?></td>
                        <td>
                            <form method="get" action="<?php $_SERVER['PHP_SELF'] ?>">
                                <input type="hidden" name="searchType" value="storeItemsID">
                                <input type="hidden" name="searchInput" value="<?php echo $store_info['store_ID']; ?>">
                                <input type="submit" value="Search" id="searchBtn" name="searchBtn" class="btn btn-primary"/>
                            </form>
                        </td>                        
                    </tr>
                <?php endforeach; ?>
                </table>
            <?php elseif ($result_type == "storeItemsID"): ?>
                <div id="storeItemContainer"> </div>
            <?php elseif ($result_type == "itemInStores"): ?>
                <div id="itemStoreContainer"> </div>
            <?php endif; ?>
        </div>   
    </div>
    </div>

    <script>
        var list_of_results = <?php echo json_encode($list_of_results); ?>;
        var order = false;

        function updateResults(){
            list_of_results = <?php echo json_encode($list_of_results);?>;
        }

        function sortItemsBy(property, whichFunc) {
            order = !order;
            list_of_results.sort((a, b) => {
                if (order) {
                    return a[property] - b[property];
                } else {
                    return b[property] - a[property];
                }
            });
            if(whichFunc=='1'){
                generateItemStoreTable();
            }else{
                generateStoreItemTable();
            }
        }

        function addToFavorites(itemID, storeID){
            alert(`Liked item: ${itemID} from store: ${storeID}`);
            fetch('grocery.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ itemID: itemID, storeID: storeID}) // Convert the data to JSON string
            }).then(response => {
                console.log(response);
            })
        }

        function addToHistory(itemID){
            alert(`I bought item: ${itemID}`);
            fetch('grocery.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ itemID: itemID}) // Convert the data to JSON string
            }).then(response => {
                console.log(response);
            })
        }

        function generateItemTable(){
            let html = `<table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                <thead>
                    <tr style="background-color:#B0B0B0">
                        <th width="5%"><b>ItemID</b></th>
                        <th width="15%"><b>Name</b></th> 
                        <th width="30%"><b>Description</b></th>        
                        <th width="10%"><b>Brand</b></th>
                        <th width="20%"><b>Item Category</b></th>        
                        <th width="10%"><b>Stores</b></th>
                        <th width="10%"></th>
                    </tr>
                </thead>`;

            list_of_results.forEach(item_info => {
            html += `<tr>
            <td>${item_info.item_ID}</td>
            <td>${item_info.name}</td>
            <td>${item_info.description}</td>
            <td>${item_info.brand}</td>
            <td>${item_info.item_category}</td>
            <td>
                <form method="get" action="">
                    <input type="hidden" name="searchType" value="itemInStores">
                    <input type="hidden" name="searchInput" value="${item_info.item_ID}">
                    <input type="submit" value="Search" id="searchBtn" name="searchBtn" class="btn btn-primary"/>
                </form>
            </td>
            <td><button class="btn btn-primary" style="font-size: 13px;" onclick="addToHistory(${item_info.item_ID})">Bought</button></td>
            </tr>`;
            });

            html += `
            </table>`;

            document.getElementById('itemContainer').innerHTML = html;
        }

        function generateItemStoreTable(){
            let html = `
                <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                <thead>
                <tr style="background-color:#B0B0B0">
                <th width="5%"><b>ItemID</b></th>
                <th width="15%"><b>Item Name</b></th>
                <th width="13%"><b>Brand</b></th>
                <th width="2%"><b>StoreID</b></th>
                <th width="15%"><b>Store Name</b></th>
                <th width="5%"><b>ZIP</b></th>
                <th width="8%"><b>Price</b> <button class="btn btn-primary" onclick="sortItemsBy('price','1')" style="padding: 2px 2px; font-size: 10px; margin-left: 5px;">Sort</button></th>
                <th width="5%"><b>Weight</b></th>
                <th width="3%"><b>Unit</b></th>
                <th width="10%"><b>Unit Price</b><button class="btn btn-primary" onclick="sortItemsBy('price_per_unit','1')" style="padding: 2px 2px; font-size: 10px; margin-left: 5px;">Sort</button></th>
                <th width="3%">Rating</th>
                <th width="7%"></th> 
                </thead>`;

            list_of_results.forEach(item_info => {
                html += `
                <tr>
                    <td>${item_info.item_ID}</td>
                    <td>${item_info.item_name}</td>
                    <td>${item_info.item_brand}</td>
                    <td>${item_info.store_ID}</td>
                    <td>${item_info.store_name}</td>
                    <td>${item_info.zipcode}</td>
                    <td>${item_info.price}</td>
                    <td>${parseFloat(item_info.weight).toFixed(2)}</td>
                    <td>${item_info.unit}</td>
                    <td>${item_info.price_per_unit}</td>
                    <td>${parseFloat(item_info.average_rating).toFixed(1)}</td>
                    <td><button class="btn btn-primary" style="font-size: 13px;" onclick="addToFavorites(${item_info.item_ID},${item_info.store_ID})">Like</button></td>
                </tr>`;
            });

            html += `
            </table>`;

            document.getElementById('itemStoreContainer').innerHTML = html;
        }

        function generateStoreItemTable(){
            let html = `
            <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                <thead>
                    <tr style="background-color:#B0B0B0">
                        <th width="2%"><b>StoreID</b></th>
                        <th width="15%"><b>Store Name</b></th> 
                        <th width="5%"><b>ZIP</b></th>        
                        <th width="5%"><b>ItemID</b></th>
                        <th width="15%"><b>Item Name</b></th> 
                        <th width="13%"><b>Brand</b></th> 
                        <th width="8%"><b>Price</b> <button class="btn btn-primary" onclick="sortItemsBy('price','2')" style="padding: 2px 2px; font-size: 10px; margin-left: 5px;">Sort</button></th>
                        <th width="5%"><b>Weight</b></th> 
                        <th width="3%"><b>Unit</b></th> 
                        <th width="10%"><b>Unit Price</b><button class="btn btn-primary" onclick="sortItemsBy('price_per_unit','2')" style="padding: 2px 2px; font-size: 10px; margin-left: 5px;">Sort</button>
                        <th width="3%">Rating</th>
                        <th width=7%"></th> 
                    </tr>
                </thead>`;

            list_of_results.forEach(item_info => {
                html += `
                <tr>
                    <td>${item_info.store_ID}</td>
                    <td>${item_info.store_name}</td>
                    <td>${item_info.zipcode}</td>
                    <td>${item_info.item_ID}</td>
                    <td>${item_info.item_name}</td>
                    <td>${item_info.item_brand}</td>
                    <td>${item_info.price}</td>
                    <td>${parseFloat(item_info.weight).toFixed(2)}</td>
                    <td>${item_info.unit}</td>
                    <td>${item_info.price_per_unit}</td>
                    <td>${parseFloat(item_info.average_rating).toFixed(1)}</td>
                    <td><button class="btn btn-primary" style="font-size: 13px;" onclick="addToFavorites(${item_info.item_ID},${item_info.store_ID})">Like</button></td>
                </tr>`;
            });

            html += `
            </table>`;
            document.getElementById('storeItemContainer').innerHTML = html;
        }
        console.log(list_of_results[0]);
        try{
            generateItemTable();
        }catch(error){
            console.log(error);
        }
        try{
            generateStoreItemTable();
        }catch(error){
            console.log(error);
        }
        try{
            generateItemStoreTable();
        }catch(error){
            console.log(error);
        }

        document.getElementById('getstorelocation').addEventListener('click', function() {
        fetch('grocery.php?searchType=store&searchInput=storeNearMe&searchBtn=Search')
        .then(response => {
            if(response.ok){
                window.location.href = response.url;
            }
            console.log(response);
        })
    });

    </script>

</body>

</html>

<?php include 'footer.php'; ?>