<?php session_start(); ?>

<?php include 'header.php'; ?>

<?php

if (!$_SESSION["user_id"]) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// echo $_SESSION["user_id"];
$user_information = getUserInformation($_SESSION["user_id"]);
$user_addresses = getUserAddresses($_SESSION["user_id"]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    if (!empty($_POST['editProfileBtn'])) 
    {
        updateUserInformation(
            $_SESSION["user_id"], $_POST['first_name'], 
            $_POST['last_name'], $_POST['age']
        );
    } 
    else if (!empty($_POST["editAddressBtn"]))
    {
        $num_addresses = checkUserAddress($_SESSION["user_id"]);
        if ($num_addresses >= 5) {
            echo "Must delete 1 address in record before adding a new one";
        }
        else if ($num_addresses == 0) {
            addUserAddress(
                $_SESSION["user_id"], $_POST["streetNumber"], $_POST["streetName"], 
                $_POST["city"], $_POST["state"], $_POST["zipcode"]
            );
        } else {
            $address_ID = addUserAddress(
                $_SESSION["user_id"], $_POST["streetNumber"], $_POST["streetName"], 
                $_POST["city"], $_POST["state"], $_POST["zipcode"]
            );
            makeAddressPrimary($_SESSION["user_id"], $address_ID);
        }
        $user_information = getUserInformation($_SESSION["user_id"]);
    }
    else if (!empty($_POST["addMembershipBtn"]))
    {
        addMembership($_SESSION["user_id"], $_POST["storeSearch"]);
        echo "Membership added";
    }

    $user_information = getUserInformation($_SESSION["user_id"]);
    $user_addresses = getUserAddresses($_SESSION["user_id"]);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    if (!empty($_GET['makePrimaryBtn']))
    {
        makeAddressPrimary($_SESSION["user_id"], $_GET["addressID"]);
        echo "<script>window.location.href = 'my_profile.php';</script>";
    }
    else if (!empty($_GET['deleteBtn']))
    {   
        deleteAddress($_SESSION["user_id"], $_GET["addressID2"]);
        echo "<script>window.location.href = 'my_profile.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Information</title>
    <style> 
        label {
            width: 100px;
        }
        .info-box {
            margin-bottom: 10px;
        }
        h2 {
            margin-bottom: 20px;
        }
        body {
            padding-left: 
        }
        #editProfileBtn {
            margin-top: 10px;
        }
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
        <h2>User Information</h2>
        <form method="post" action="my_profile.php">
            <div class=info-box>
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name"name="first_name" value="<?php echo $user_information["first_name"]; ?>"><br>
            </div>
            <div class=info-box>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo $user_information["last_name"]; ?>"><br>
            </div>
            <div class=info-box>
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo $user_information["age"]; ?>"><br>
            </div>
            <div class=info-box>
                <label for="email">Email:</label>
                <label type="text" id="email" name="email"><?php echo $user_information["email"]; ?></label><br>
            </div>
            <input type="submit" value="Update" id="editProfileBtn" name="editProfileBtn" class="btn btn-primary"/>
        </form>
        <div style="margin-top:10px;">
            <a href="changePassword.php">Change my password</a>
        </div>

        <h2 style="margin-top:20px;">Edit Address (max 5 in record)</h2>
        <form method="post" action="my_profile.php">
            <div class=info-box>
                <label for="streetNumber">Street #:</label>
                <input type="text" id="streetNumber" name="streetNumber" value="<?php echo (array_key_exists('street_num', $user_information)) ? $user_information["street_num"] : ""; ?>"><br>
            </div>
            <div class=info-box>
                <label for="streetName">Street Name:</label>
                <input type="text" id="streetName" name="streetName" value="<?php echo (array_key_exists('street_name', $user_information)) ? $user_information["street_name"] : ""; ?>"><br>
            </div>
            <div class=info-box>
                <label for="city">City:</label>
                <input type="text" id="city" name="city" value="<?php echo (array_key_exists('city', $user_information)) ? $user_information["city"] : ""; ?>"><br>
            </div>
            <div class=info-box>
                <label for="state">State:</label>
                <input type="text" id="state" name="state" value="<?php echo (array_key_exists('state', $user_information)) ? $user_information["state"] : ""; ?>"><br>
            </div>
            <div class=info-box>
                <label for="zipcode">Zipcode:</label>
                <input type="text" id="zipcode" name="zipcode" value="<?php echo (array_key_exists('zipcode', $user_information)) ? $user_information["zipcode"] : ""; ?>"><br>
            </div>
            <input style="margin-top:10px;" type="submit" value="Update" id="editAddressBtn" name="editAddressBtn" class="btn btn-primary"/>
        </form>
    </div>

    <div class="container address-container">
        <h2 style="margin-bottom:20px; margin-top:10px;">My Addresses</h2>
        <div class="row justify-content-center">  
            <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
                <thead>
                    <tr style="background-color:#B0B0B0">
                        <th width="10%"><b>ID</b></th>
                        <th width="10%"><b>Street Number</b></th>
                        <th width="20%"><b>Street Name</b></th>
                        <th width="10%"><b>City</b></th>
                        <th width="10%"><b>State</b></th>
                        <th width="10%"><b>Zipcode<b></th>
                        <th width="10%"><b>Is Primary</b></th>
                        <th width="10%"><b>Make Primary</b></th>
                        <th width="10%"><b>Delete</b></th>
                    </tr>
                </thead>
                <?php if (empty($user_addresses)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No addresses stored</td>
                    </tr>
                <?php endif; ?>

                <?php $addressID = 1; ?>
                <?php foreach ($user_addresses as $address): ?>
                    <tr>
                        <td><?php echo $addressID; ?></td>
                        <td><?php echo $address['street_num']; ?></td>
                        <td><?php echo $address['street_name']; ?></td>
                        <td><?php echo $address['city']; ?></td>
                        <td><?php echo $address['state']; ?></td>
                        <td><?php echo $address['zipcode']; ?></td>
                        <td><?php echo $address['is_primary']; ?></td>
                        <td>
                            <?php if ($address['is_primary'] != 1): ?>
                                <form method="get" action="my_profile.php">
                                    <input type="hidden" name="addressID" value="<?php echo $address['address']; ?>">
                                    <input type="submit" value="Change" id="makePrimaryBtn" name="makePrimaryBtn" class="btn btn-primary"/>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($address['is_primary'] != 1): ?>
                                <form method="get" action="my_profile.php">
                                    <input type="hidden" name="addressID2" value="<?php echo $address['address']; ?>">
                                    <input type="submit" value="X" id="deleteBtn" name="deleteBtn" class="btn btn-primary"/>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $addressID++; ?>
                <?php endforeach; ?>
            </table>
        </div>   
    </div>   

    <div class="container">
        <h2>Add a membership</h2>
        <form method="post" action="my_profile.php" onsubmit="return validateForm()">
            <div width="20%">
                Select Store:
                <div class="search-container">
                    <input type="text" class="search-input form-input" name="storeSearch" id="storeSearch" placeholder="Search for a store" onkeyup="filterStores('storeSearch', 'storeList')">
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
            <input style="margin-top:20px; margin-bottom:50px;" type="submit" value="Add" id="addMembershipBtn" name="addMembershipBtn" class="btn btn-primary"/>
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

    function validateForm() {
        const inputField = document.getElementById("storeSearch");
        if (inputField && inputField.value.trim() === '') {
            alert(`Please fill in the store field.`);
            inputField.focus();
            return false;
        }
    }
</script>

</html>

<?php include 'footer.php'; ?>