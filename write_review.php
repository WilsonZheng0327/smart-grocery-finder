<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php   // form handling

if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')   // GET
{
    if (!empty($_POST['submitBtn']))    // $_GET['....']
    {
        addReview(
            $_SESSION["user_id"], $_POST['itemSearch'], 
            $_POST['storeSearch'], $_POST['image'], 
            $_POST['comment'], $_POST['rating']
        );
        echo "<script>window.location.href = 'write_review_success.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .search-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .search-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .store-list, .item-list{
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 4px 4px;
            display: none;
        }

        .store-list div, .item-list div{
            padding: 8px;
            cursor: pointer;
        }

        .store-list div:hover, .item-list div:hover{
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>  
    <div class="wrapper">
    <div class="container">
        <div class="row g-3 mt-2">
            <div class="col">
                <h2>Write A Review</h2>
            </div>
        </div>

        <!---------------->
        <form method="post" action="write_review.php" onsubmit="return validateForm()">
            <table style="width:98%">
                <tr id='storeItem'>
                    <td colspan=1>
                        <div class='mb-3' width="100%">
                            Select Store:
                            <div class="search-container">
                                <input width="100%" type="text" class="search-input form-input" name="storeSearch" id="storeSearch" placeholder="Search for a store" onkeyup="filterStores('storeSearch', 'storeList')">
                                <div class="store-list" id="storeList">
                                    <?php
                                    // Retrieve the list of stores from the database
                                    $stores = getStores();
                                    
                                    foreach ($stores as $store) {
                                        echo "<div onclick=\"selectStore('storeSearch', 'storeList', '" . $store['store_ID'] . "', '" . $store['name'] . "')\">" . $store['store_ID'] . ": " . $store['name'] . "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td colspan=1>
                        <div class='mb-3' width="100%">
                            Select Item:
                            <div class="search-container">
                                <input width="100%" type="text" class="search-input form-input" name="itemSearch" id="itemSearch" placeholder="Search for a item" onkeyup="filterItems('itemSearch', 'itemList')">
                                <div class="item-list" id="itemList">
                                    <?php
                                    // Retrieve the list of stores from the database
                                    $items = getItems();
                                    
                                    foreach ($items as $item) {
                                        echo "<div onclick=\"selectItem('itemSearch', 'itemList', '" . $item['item_ID'] . "', '" . $item['name'] . "')\">" . $item['item_ID'] . ": " . $item['name'] . "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr id='reviewRow1'>
                    <td colspan=1>
                        <div class='mb-3'>
                            Image URL:
                            <input type='text' class='form-control' id='image' name='image'
                                value="" />
                        </div>
                    </td>
                </tr>
                <tr id='reviewRow2'>
                    <td colspan=2>
                        <div class='mb-3'>
                            Comment:
                            <textarea class='form-control' id='comment' name='comment' rows='4'></textarea>
                        </div>
                    </td>
                </tr>
                <tr id='reviewRow3'>
                    <td>
                        <div class='mb-3'>
                        Rating:
                        <fieldset>
                            <input type="radio" id="rating1" name="rating" value="1">
                            <label for="rating1">1</label><br>
                            <input type="radio" id="rating2" name="rating" value="2">
                            <label for="rating2">2</label><br>
                            <input type="radio" id="rating3" name="rating" value="3">
                            <label for="rating3">3</label><br>
                            <input type="radio" id="rating4" name="rating" value="4">
                            <label for="rating4">4</label><br>
                            <input type="radio" id="rating5" name="rating" value="5">
                            <label for="rating5">5</label><br>
                        </fieldset>
                    </div>
                    </td>
                </tr>
            </table>

            <div class="row g-3 mx-auto">
                <div class="col-4 d-grid ">
                    <input type="submit" value="Submit" id="submitBtn" name="submitBtn" class="btn btn-dark"
                        title="Submit database change" />
                </div>
                <div class="col-4 d-grid">
                    <input type="reset" value="Clear form" name="clearBtn" id="clearBtn" class="btn btn-secondary" />
                </div>
            </div>
        </form>

    </div>
    </div>
</body>


<script>
function filterStores(storeSearch, storeList) {
    var input = document.getElementById(storeSearch);
    var filter = input.value.toUpperCase();
    var storeList = document.getElementById(storeList);
    var stores = storeList.getElementsByTagName('div');

    for (var i = 0; i < stores.length; i++) {
        var storeName = stores[i].textContent || stores[i].innerText;
        if (storeName.toUpperCase().indexOf(filter) > -1) {
            stores[i].style.display = '';
        } else {
            stores[i].style.display = 'none';
        }
    }

    storeList.style.display = 'block';
}

function filterItems(itemSearch, itemList) {
    var input = document.getElementById(itemSearch);
    var filter = input.value.toUpperCase();
    var itemList = document.getElementById(itemList);
    var items = itemList.getElementsByTagName('div');

    for (var i = 0; i < items.length; i++) {
        var itemName = items[i].textContent || items[i].innerText;
        if (itemName.toUpperCase().indexOf(filter) > -1) {
            items[i].style.display = '';
        } else {
            items[i].style.display = 'none';
        }
    }

    itemList.style.display = 'block';
}

function selectStore(storeSearch, storeList, storeID, storeName) {
    var input = document.getElementById(storeSearch);
    input.value = storeID + ": " + storeName;
    document.getElementById(storeList).style.display = 'none';
}

function selectItem(itemSearch, itemList, itemID, itemName) {
    var input = document.getElementById(itemSearch);
    input.value = itemID + ": " + itemName;
    document.getElementById(itemList).style.display = 'none';
}

document.addEventListener('click', function(event) {
    var searchContainer = document.querySelector('.search-container');
    if (!searchContainer.contains(event.target)) {
        document.getElementById('storeList').style.display = 'none';
    }
});

document.addEventListener('click', function(event) {
    var searchContainer = document.querySelector('.search-container');
    if (!searchContainer.contains(event.target)) {
        document.getElementById('itemList').style.display = 'none';
    }
});
</script>

</html>

<?php include 'footer.php'; ?>