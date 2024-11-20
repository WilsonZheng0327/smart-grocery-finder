<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php   // form handling

if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$isFormSubmitted = false;
$request_type = "";
$removeStoreReason = "";

// $list_of_requests = getAllRequests();
// // var_dump($list_of_requests);   // debug

if ($_SERVER['REQUEST_METHOD'] == 'POST')   // GET
{
    if (!empty($_POST['submitBtn']))    // $_GET['....']
    {
        if ($_POST['requestType'] == 'addStore') {
            requestChangeAddStore(
                $_SESSION["user_id"], $_POST['storeName'], $_POST['streetNumber'], 
                $_POST['streetName'], $_POST['city'], $_POST['state'], 
                $_POST['zipCode'], $_POST['notesText']
            );
        } else if ($_POST['requestType'] == 'removeStore') {
            requestChangeRemoveStore(
                $_SESSION["user_id"], $_POST['storeSearch'], 
                $_POST['removeStoreReason'], $_POST['notesText']
            );
        } else if ($_POST['requestType'] == 'addStoreItem') {
            requestChangeAddStoreItem(
                $_SESSION["user_id"], $_POST['storeSearch2'], $_POST['itemName'],
                $_POST['itemBrand'], $_POST['price'], $_POST['weight'], 
                $_POST['unit'], $_POST['notesText']
            );
        } else if ($_POST['requestType'] == 'changePrice') {
            requestChangeChangePrice(
                $_SESSION["user_id"], $_POST['storeSearch2'], $_POST['itemName'],
                $_POST['itemBrand'], $_POST['newPrice'], $_POST['newWeight'], 
                $_POST['newUnit'], $_POST['notesText']
            );
        } else if ($_POST['requestType'] == 'addSale') {
            requestChangeAddSale(
                $_SESSION["user_id"], 
                $_POST['storeSearch3'], 
                $_POST['saleItemName'], 
                $_POST['salePrice'], 
                $_POST['startDate'],
                $_POST['endDate'], 
                $_POST['notesText']
            );
        } 

        $isFormSubmitted = true;
        echo "<script>window.location.href = 'request_change_success.php';</script>";

    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
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

        .store-list {
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

        .store-list div {
            padding: 8px;
            cursor: pointer;
        }

        .store-list div:hover {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="wrapper">
    <div class="container">
        <div class="row g-3 mt-2">
            <div class="col">
                <h2>Request Changes to Grocery Information</h2>
            </div>
        </div>

        <!---------------->
        <form method="post" action="request_change.php" onsubmit="return validateForm()">
            <table style="width:98%">
                <tr id='request-type-row'>
                    <td colspan=3>
                        <div class='mb-3'>
                            Change Request Type:
                            <select class='form-select' id='requestType' name='requestType' onchange="updateFormFields()">
                                <option value='' <?php if ($request_type == '') echo 'selected'; ?>>
                                </option>
                                <option value='addStore' <?php if ($request_type == 'addStore') echo 'selected'; ?>>
                                    Add a store location
                                </option>
                                <option value='removeStore' <?php if ($request_type == 'removeStore') echo 'selected'; ?>>
                                    Remove a store location
                                </option>
                                <option value='addStoreItem' <?php if ($request_type == 'addStoreItem') echo 'selected'; ?>>
                                    Add item (specific store)
                                </option>
                                <option value='changePrice' <?php if ($request_type == 'changePrice') echo 'selected'; ?>>
                                    Change price of item (specific store)
                                </option>
                                <option value='addSale' <?php if ($request_type == 'addSale') echo 'selected'; ?>>
                                    Report an Item on Sale
                                </option>                                
                            </select>
                        </div>
                    </td>
                </tr>
                <tr id='addStore1' class='hidden'>
                    <td width="33%">
                        <div class='mb-3'>
                            Store Name:
                            <input type='text' class='form-control' id='storeName' name='storeName'
                                value="" />
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            Street Number:
                            <input type='text' class='form-control' id='streetNumber' name='streetNumber'
                                value="" />
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            Street Name:
                            <input type='text' class='form-control' id='streetName' name='streetName'
                                value="" />
                        </div>
                    </td>
                </tr>
                <tr id='addStore2' class='hidden'>
                    <td width="33%">
                        <div class='mb-3'>
                            City:
                            <input type='text' class='form-control' id='city' name='city'
                                value="" />
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            State:
                            <input type='text' class='form-control' id='state' name='state'
                                value="" />
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            Zip Code:
                            <input type='text' class='form-control' id='zipCode' name='zipCode'
                                value="" />
                        </div>
                    </td>
                </tr>
                <tr id='removeStore' class='hidden'>
                    <td width="50%">
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
                    <td>
                        <div class='mb-3'>
                            Reason:
                            <select class='form-select' id='removeStoreReason' name='removeStoreReason'>
                                <option selected></option>
                                <option value='moved' <?php if ($removeStoreReason == 'moved') echo 'selected' ?> >
                                    Moved</option>
                                <option value='renovating' <?php if ($removeStoreReason == 'renovating') echo 'selected' ?> >
                                    Renovating</option>
                                <option value='closed' <?php if ($removeStoreReason == 'closed') echo 'selected' ?> >
                                    Closed</option>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr id='storeItem' class='hidden'>
                    <td width="33%">
                        <div class='mb-3' width="100%">
                            Select Store:
                            <div class="search-container">
                                <input width="100%" type="text" class="search-input form-input" name="storeSearch2" id="storeSearch2" placeholder="Search for a store" onkeyup="filterStores('storeSearch2', 'storeList2')">
                                <div class="store-list" id="storeList2">
                                    <?php
                                    // Retrieve the list of stores from the database
                                    $stores = getStores();
                                    
                                    foreach ($stores as $store) {
                                        echo "<div onclick=\"selectStore('storeSearch2', 'storeList2', '" . $store['store_ID'] . "', '" . $store['name'] . "')\">" . $store['store_ID'] . ": " . $store['name'] . "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            Item Name:
                            <input type='text' class='form-control' id='itemName' name='itemName'
                                value="" />
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            Item Brand:
                            <input type='text' class='form-control' id='itemBrand' name='itemBrand'
                                value="" />
                        </div>
                    </td>
                </tr>
                <tr id='addStoreItem' class='hidden'>
                    <td>
                        <div class='mb-3'>
                            Price ($):
                            <input type='text' class='form-control' id='price' name='price'
                                value="" />
                        </div>
                    </td>
                    <td>
                        <div class='mb-3'>
                            Weight:
                            <input type='text' class='form-control' id='weight' name='weight'
                                value="" />
                        </div>
                    </td>
                    <td>
                        <div class='mb-3'>
                            Unit (e.g. kg):
                            <input type='text' class='form-control' id='unit' name='unit'
                                value="" />
                        </div>
                    </td>
                </tr>
                <tr id='changePrice' class='hidden'>
                    <td>
                        <div class='mb-3'>
                            New Price ($):
                            <input type='text' class='form-control' id='newPrice' name='newPrice'
                                value="" />
                        </div>
                    </td>
                    <td>
                        <div class='mb-3'>
                            Weight:
                            <input type='text' class='form-control' id='newWeight' name='newWeight'
                                value="" />
                        </div>
                    </td>
                    <td>
                        <div class='mb-3'>
                            Unit (e.g. kg):
                            <input type='text' class='form-control' id='newUnit' name='newUnit'
                                value="" />
                        </div>
                    </td>
                </tr>
                <tr id='addSale1' class='hidden'>
                    <td width="33%">
                        <div class='mb-3' width="100%">
                            Select Store:
                            <div class="search-container">
                                <input width="100%" type="text" class="search-input form-input" name="storeSearch3" id="storeSearch3" placeholder="Search for a store" onkeyup="filterStores('storeSearch3', 'storeList3')">
                                <div class="store-list" id="storeList3">
                                    <?php
                                    // Retrieve the list of stores from the database
                                    $stores = getStores();
                                    foreach ($stores as $store) {
                                        echo "<div onclick=\"selectStore('storeSearch3', 'storeList3', '" . $store['store_ID'] . "', '" . $store['name'] . "')\">" . $store['store_ID'] . ": " . $store['name'] . "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            Item Name:
                            <input type='text' class='form-control' id='saleItemName' name='saleItemName' value="" />
                        </div>
                    </td>
                </tr>
                <tr id='addSale2' class='hidden'>
                    <td width="33%">
                        <div class='mb-3'>
                            Sale Price ($):
                            <input type='text' class='form-control' id='salePrice' name='salePrice' value="" />
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            Sale Start Date:
                            <input type='date' class='form-control' id='startDate' name='startDate' value="" />
                        </div>
                    </td>
                    <td width="33%">
                        <div class='mb-3'>
                            Sale End Date:
                            <input type='date' class='form-control' id='endDate' name='endDate' value="" />
                        </div>
                    </td>
                </tr>
                <tr id='notes' class='hidden'>
                    <td colspan=3>
                        <div class="mb-3">
                            Other Notes:
                            <textarea class='form-control' id='notesText' name='notesText' rows='4'></textarea>
                        </div>
                    </td>
                </tr>

            </table>

            <div class="row g-3 mx-auto">
                <div class="col-4 d-grid ">
                    <input type="submit" value="Submit" id="submitBtn" name="submitBtn" class="btn btn-dark"
                        title="Submit a change request" />
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

    function selectStore(storeSearch, storeList, storeID, storeName) {
        var input = document.getElementById(storeSearch);
        input.value = storeID + ": " + storeName;
        document.getElementById(storeList).style.display = 'none';
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
            document.getElementById('storeList2').style.display = 'none';
        }
    });

    document.addEventListener('click', function(event) {
        var searchContainer = document.querySelector('.search-container');
        if (!searchContainer.contains(event.target)) {
            document.getElementById('storeList3').style.display = 'none';
        }
    });

const validationConfig = {
    addStore: ['storeName', 'streetNumber', 'streetName', 'city', 'state', 'zipCode'],
    removeStore: ['storeSearch', 'removeStoreReason'],
    addStoreItem: ['storeSearch2', 'itemName', 'itemBrand', 'price', 'weight', 'unit'],
    changePrice: ['storeSearch2', 'itemName', 'itemBrand', 'newPrice', 'newWeight', 'newUnit'],
    addSale: ['storeSearch3', 'saleItemName', 'salePrice', 'startDate', 'endDate']
};

function validateForm() {
    const requestType = document.getElementById('requestType').value;
    const requiredFields = validationConfig[requestType];

    if (requiredFields) {
        for (const field of requiredFields) {
            const inputField = document.getElementById(field);
            if (inputField && inputField.value.trim() === '') {
                alert(`Please fill in the ${field} field.`);
                inputField.focus();
                return false;
            }
        }
    }

    return true;
}

function hideFormFields() {
    var formRows = document.querySelectorAll('#addStore1, #addStore2, #removeStore, #storeItem, #addStoreItem, #changePrice, #addSale1, #addSale2, #notes');
    formRows.forEach(function(row) {
        row.classList.add('hidden');
    });
}

function updateFormFields() {
    var requestType = document.getElementById('requestType').value;
    var addStoreFields1 = document.getElementById('addStore1');
    var addStoreFields2 = document.getElementById('addStore2');
    var removeStoreFields = document.getElementById('removeStore');
    var storeItemFields = document.getElementById('storeItem');
    var addStoreItemFields = document.getElementById('addStoreItem');
    var changePriceFields = document.getElementById('changePrice');
    var addSaleFields1 = document.getElementById('addSale1');
    var addSaleFields2 = document.getElementById('addSale2');
    var notesField = document.getElementById('notes');

    // Hide all fields
    hideFormFields();

    // Show fields based on the selected request type
    if (requestType === 'addStore') {
        addStoreFields1.classList.remove('hidden');
        addStoreFields2.classList.remove('hidden');
        notesField.classList.remove('hidden');
    } else if (requestType === 'removeStore') {
        removeStoreFields.classList.remove('hidden');
        notesField.classList.remove('hidden');
    } else if (requestType === 'addStoreItem') {
        storeItemFields.classList.remove('hidden');
        addStoreItemFields.classList.remove('hidden');
        notesField.classList.remove('hidden');
    } else if (requestType === 'changePrice') {
        storeItemFields.classList.remove('hidden');
        changePriceFields.classList.remove('hidden');
        notesField.classList.remove('hidden');
    } else if (requestType === 'addSale') {
        addSaleFields1.classList.remove('hidden');
        addSaleFields2.classList.remove('hidden');
        notesField.classList.remove('hidden');
    }
}
</script>

</html>

<?php include 'footer.php'; ?>