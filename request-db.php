<?php

function testing() {
    echo "testtesttest fucker";
}

function hashPassword($password) {
    // password_hash() is function in PHP
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    return $hashedPassword;
}

function verifyPassword($password, $hashedPassword) {
    // password_verity() is function in PHP
    if (password_verify($password, $hashedPassword)) {
        return true;
    } else {
        return false;
    }
}

function changePassword($user_ID, $newPassword, $conf) {
    $equal = $newPassword == $conf;    
    if (!$equal) {
        return array("success"=>false, "cause"=>"different");
    }

    $password = hashPassword($newPassword);

    global $db;

    $query = "UPDATE _User SET password = :password WHERE user_ID = :user_ID";

    $statement = $db->prepare($query);

    $statement->bindValue(':password', $password, PDO::PARAM_STR);
    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();

    return array("success"=>true, "cause"=>"none");
}

function checkLogin($email, $password) {
    global $db;

    $query = "SELECT user_ID, email, password, admin FROM _User WHERE email = :email";

    $statement = $db->prepare($query);

    $statement->bindValue(':email', $email);

    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    // echo "got here too";

    if (!$result) {
        return array("success"=>false, "cause"=>"exist");
    } else if (!verifyPassword($password, $result['password'])){
        return array("success"=>false, "cause"=>"password");
    } else {
        return array("success"=>true, "cause"=>"none", "user"=>$result);
    }
}

function signUp($first_name, $last_name, $email, $password, $password_conf) {
    global $db;

    $query = "SELECT * FROM _User WHERE email = :email";
    $statement = $db->prepare($query);

    $emailString = "$email";
    $statement->bindValue(':email', $emailString, PDO::PARAM_STR);

    $statement->execute();
    $result = $statement->fetchAll();
    $statement->closeCursor();

    if (count($result) > 0) {
        return array("success"=>false, "cause"=>"exist");
    }

    $password_match = $password == $password_conf;
    
    if (!$password_match) {
        return array("success"=>false, "cause"=>"password");
    }

    $passwordHashed = hashPassword($password);

    $query = "INSERT INTO _User (first_name, last_name, email, password) 
                VALUES (:first_name, :last_name, :email, :password)";
    $statement = $db->prepare($query);

    $statement->bindValue(':first_name', "$first_name");
    $statement->bindValue(':last_name', $last_name);
    $statement->bindValue(':email', $email);
    $statement->bindValue(':password', $passwordHashed);

    $statement->execute();
    $statement->closeCursor();

    return array("success"=>true, "cause"=>"none");
}

function checkAdmin($user_ID) {
    global $db;

    $query = "SELECT admin FROM _User WHERE user_ID = :user_ID";

    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);

    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    return $result["admin"];
}

function searchStoreNearMe($user) {
    global $db;
    $userZipQuery = "SELECT Address.zipcode
    FROM Address JOIN _User ON Address.address_ID = _User.address
    WHERE _User.user_ID = :user";

    $zipStatement = $db->prepare($userZipQuery);
    $zipStatement->bindValue(':user', $user, PDO::PARAM_INT);
    $zipStatement->execute();
    $myZip = $zipStatement->fetchColumn(); 
    $zipStatement->closeCursor();

    $query = "SELECT *,
                ABS(Address.zipcode - :myZip) AS zip_difference
              FROM
                Store
                JOIN Address ON Store.address = Address.address_ID
              WHERE
                ABS(Address.zipcode - :myZip) <= 30
              ORDER BY
                zip_difference";

    $statement = $db->prepare($query);
    $statement->bindValue(':myZip', $myZip, PDO::PARAM_INT);
    $statement->execute(); 
    $result = $statement->fetchAll();
    $statement->closeCursor();
    return $result; 
}


function searchReqeust($searchType, $searchInput) {
    global $db;

    if ($searchType == "item") {
        $query = "SELECT * FROM Item WHERE name LIKE :searchInput";
    } elseif ($searchType == "store") {
        $query = "SELECT * 
                  FROM Store JOIN Address ON Store.address = Address.address_ID 
                  WHERE name LIKE :searchInput";
    } elseif ($searchType == "storeItemsID") {
        $query = 
               "SELECT
                    s.store_ID AS store_ID,
                    s.name AS store_name,
                    a.zipcode,
                    i.item_ID AS item_ID,
                    i.name AS item_name,
                    i.brand AS item_brand,
                    si.price,
                    si.weight,
                    si.unit,
                    si.price_per_unit,
                    COALESCE(AVG(r.rating), 0) AS average_rating
                FROM
                    Store s
                    JOIN Address a ON s.address = a.address_ID
                    JOIN StoreItems si ON s.store_ID = si.store
                    JOIN Item i ON si.item = i.item_ID
                    LEFT JOIN Review r ON si.item = r.item AND si.store = r.store
                WHERE
                    s.store_ID = :searchInput
                GROUP BY
                    i.item_ID, s.store_ID;"
                ;  
    } elseif ($searchType == "itemInStores") {
        $query = 
               "SELECT
                    i.item_ID AS item_ID,
                    i.name AS item_name,
                    i.brand AS item_brand,
                    s.store_ID AS store_ID,
                    s.name AS store_name,
                    a.zipcode,
                    si.price,
                    si.weight,
                    si.unit,
                    si.price_per_unit,
                    COALESCE(AVG(r.rating), 0) AS average_rating
                FROM
                    Store s
                    JOIN Address a ON s.address = a.address_ID
                    JOIN StoreItems si ON s.store_ID = si.store
                    JOIN Item i ON si.item = i.item_ID
                    LEFT JOIN Review r ON si.item = r.item AND si.store = r.store
                WHERE
                    i.item_ID = :searchInput
                GROUP BY
                    i.item_ID, s.store_ID;";
    } else {
        // handle invalid $searchType value
        echo "Invalid search type...";
        return [];
    }

    $statement = $db->prepare($query);

    $searchPattern = "%$searchInput%";
    if ($searchType == "storeItemsID" || $searchType == "itemInStores") {
        $statement->bindValue(':searchInput', $searchInput, PDO::PARAM_INT); // Bind $searchInput as an integer
    } else {
        $statement->bindValue(':searchInput', $searchPattern, PDO::PARAM_STR);
    }

    $statement->execute();
    $result = $statement->fetchAll();
    $statement->closeCursor();

    $itemCount = count($result);

    return $result;
}

function getAllItems() {
    global $db;

    $query = "SELECT * FROM Item";
    $statement = $db->prepare($query);       // compile
    $statement->execute();
    $result = $statement->fetchAll();      // fetch all results
    $statement->closeCursor();

    $itemCount = count($result);

    echo "Number of total items: " . $itemCount;

    return $result;
}

function getUserInformation($user_id) {
    global $db;

    $query = "SELECT * FROM _User 
              WHERE user_ID = :user_ID";

    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_id, PDO::PARAM_INT);

    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    if (!$result["address"]) {
        return $result;
    }

    $query = "SELECT * FROM _User 
              JOIN Address ON _User.address = Address.address_ID
              WHERE user_ID = :user_ID";

    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_id, PDO::PARAM_INT);

    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    return $result;
}

function updateUserInformation($user_ID, $first_name, $last_name, $age) {
    global $db;

    $query = "UPDATE _User
                SET first_name = :first_name,
                    last_name = :last_name,
                    age = :age
                WHERE user_ID = :user_ID";

    $statement = $db->prepare($query);

    $statement->bindValue(':first_name', $first_name, PDO::PARAM_STR);
    $statement->bindValue(':last_name', $last_name, PDO::PARAM_STR);
    $statement->bindValue(':age', $age, PDO::PARAM_INT);
    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->execute();

    $statement->closeCursor();
}

function checkUserAddress($user_ID) {
    // checks how many address user have in record
    global $db;

    $query = "SELECT * FROM AddressBook WHERE user = :user_ID";

    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetchAll();
    $statement->closeCursor();

    return count($result);
}

/**
 * adds into Address & adds into AddressBook
 * @return int new address_ID
 */
function addUserAddress($user_ID, $street_num, $street_name, $city, $state, $zipcode) {
    global $db;

    $addressQuery = "
        INSERT INTO Address (street_num, street_name, city, state, zipcode)
        VALUES (:street_num, :street_name, :city, :state, :zipcode)
    ";
    $addressStatement = $db->prepare($addressQuery);
    $addressStatement->bindValue(':street_num', $street_num, PDO::PARAM_STR);
    $addressStatement->bindValue(':street_name', $street_name, PDO::PARAM_STR);
    $addressStatement->bindValue(':city', $city, PDO::PARAM_STR);
    $addressStatement->bindValue(':state', $state, PDO::PARAM_STR);
    $addressStatement->bindValue(':zipcode', $zipcode, PDO::PARAM_STR);
    $addressStatement->execute();

    // Get the address_ID of the newly inserted address
    $address_ID = $db->lastInsertId();
    $addressStatement->closeCursor();

    // set new primary address 
    $query = "
        INSERT INTO AddressBook (user, address, is_primary)
        VALUES (:user_ID, :address_ID, true)
    ";
    $statement = $db->prepare($query);
    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_STR);
    $statement->bindValue(':address_ID', $address_ID, PDO::PARAM_STR);
    $statement->execute();

    $statement->closeCursor();

    return $address_ID;
}

function getStores() {
    global $db;

    $query = "SELECT * FROM Store";
    $statement = $db->prepare($query);       // compile
    $statement->execute();
    $result = $statement->fetchAll();      // fetch all results
    $statement->closeCursor();

    $itemCount = count($result);

    echo "Number of total stores: " . $itemCount;

    return $result;
}

function getItems() {
    global $db;

    $query = "SELECT * FROM Item";
    $statement = $db->prepare($query);       // compile
    $statement->execute();
    $result = $statement->fetchAll();      // fetch all results
    $statement->closeCursor();

    $itemCount = count($result);

    echo "Number of total items: " . $itemCount;

    return $result;
}

function requestChangeAddStore(
    $user_ID, $store_name, $street_number, $street_name,
    $city, $state, $zip_code, $notes
) {
    global $db;

    $change_details = "(add store) Name: \"$store_name\", \"$street_number $street_name, $city $state $zip_code\" (notes) \"$notes\"";
    $dateTime = new DateTime();
    $dateTime = $dateTime->format('Y-m-d_H:i:s');

    $query = "INSERT INTO ChangeRequest (user, request_time, change_details, accepted) 
                VALUES (:user_ID, :time, :change_details, false)";
    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':time', $dateTime, PDO::PARAM_STR);
    $statement->bindValue(':change_details', $change_details, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
    
    // echo "Added \"add store\" change request.";
}

function requestChangeRemoveStore($user_ID, $store, $reason, $notes) {
    global $db;

    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));
    $store_name = trim($store_name);
    $dateTime = new DateTime();
    $dateTime = $dateTime->format('Y-m-d_H:i:s');

    $change_details = "(remove store) Name: \"$store_name\", reason: \"$reason\" (notes) \"$notes\"";

    $query = "INSERT INTO ChangeRequest (user, store, request_time, change_details, accepted) 
                VALUES (:user_ID, :store_ID, :time, :change_details, false)";
    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':store_ID', $store_ID, PDO::PARAM_INT);
    $statement->bindValue(':time', $dateTime, PDO::PARAM_STR);
    $statement->bindValue(':change_details', $change_details, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
    
    // echo "Added \"remove store\" change request.";
}

function requestChangeAddStoreItem(
    $user_ID, $store, $item_name, $item_brand, 
    $price, $weight, $unit, $notes
) {
    global $db;

    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));
    $store_name = trim($store_name);
    $dateTime = new DateTime();
    $dateTime = $dateTime->format('Y-m-d_H:i:s');

    $change_details = "(add store item) Store: \"$store_name\", Item info: name-\"$item_name\" brand-\"$item_brand\" price-\"$price\" weight-\"$weight\" unit-\"$unit\" (notes) \"$notes\"";

    $query = "INSERT INTO ChangeRequest (user, store, request_time, change_details, accepted) 
                VALUES (:user_ID, :store_ID, :time, :change_details, false)";
    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':store_ID', $store_ID, PDO::PARAM_INT);
    $statement->bindValue(':time', $dateTime, PDO::PARAM_STR);
    $statement->bindValue(':change_details', $change_details, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
    
    // echo "Added \"add store item\" change request.";
}     

function requestChangeChangePrice(
    $user_ID, $store, $item_name, $item_brand, 
    $price, $weight, $unit, $notes
) {
    global $db;

    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));
    $store_name = trim($store_name);
    $dateTime = new DateTime();
    $dateTime = $dateTime->format('Y-m-d_H:i:s');

    $change_details = "(change price) Store: \"$store_name\", Item info: name-\"$item_name\" brand-\"$item_brand\" new price-\"$price\" new weight-\"$weight\" new unit-\"$unit\" (notes) \"$notes\"";

    $query = "INSERT INTO ChangeRequest (user, store, request_time, change_details, accepted) 
                VALUES (:user_ID, :store_ID, :time, :change_details, false)";
    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':store_ID', $store_ID, PDO::PARAM_INT);
    $statement->bindValue(':time', $dateTime, PDO::PARAM_STR);
    $statement->bindValue(':change_details', $change_details, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
    
    // echo "Added \"change price\" change request.";
}                                    

function requestChangeAddSale(
    $user_ID, $store, $item_name, $sale_price, 
    $start_date, $end_date, $notesText
) {
    global $db; 
    
    // 1. Extract store ID from the combined string
    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));
    $store_name = trim($store_name);
    
    // 2. Get item ID based on name (assuming unique item names)
    $item_ID = getItemIDByName($item_name);
    
    // 3. Insert sale details into the Sale table
    $sql = "INSERT INTO Sale (item, store, start_date, end_date, sale_price)
            VALUES (:item_ID, :store_ID, :start_date, :end_date, :sale_price)";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':item_ID', $item_ID, PDO::PARAM_INT);
    $stmt->bindValue(':store_ID', $store_ID, PDO::PARAM_INT);
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
    $stmt->bindValue(':sale_price', $sale_price, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();

    // 4. Log the request in the ChangeRequest table
    $change_details = "Reported sale for item '$item_name' at store ID $store_ID: $$sale_price from $start_date to $end_date. Notes: $notesText";
    $sql = "INSERT INTO ChangeRequest (user, item, store, request_time, change_details, accepted)
            VALUES (:user_ID, :item_ID, :store_ID, :request_time, :change_details, 0)"; // 0 indicates not yet accepted
    
    $dateTime = new DateTime();
    $request_time = $dateTime->format('Y-m-d_H:i:s');

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $stmt->bindValue(':item_ID', $item_ID, PDO::PARAM_INT);
    $stmt->bindValue(':store_ID', $store_ID, PDO::PARAM_INT);
    $stmt->bindValue(':request_time', $request_time, PDO::PARAM_STR);
    $stmt->bindValue(':change_details', $change_details, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();
}

function getItemIDByName($item_name) {
    global $db; 
    $sql = "SELECT item_ID FROM Item WHERE name = :item_name";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':item_name', $item_name, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch();
    $stmt->closeCursor();
    return $result['item_ID'];
}

function getChangeRequests() {
    global $db;

    $query = "SELECT * FROM ChangeRequest WHERE accepted=false ORDER BY request_time ASC";
    $statement = $db->prepare($query);       // compile
    $statement->execute();
    $result1 = $statement->fetchAll();      // fetch all results
    $statement->closeCursor();

    $query = "SELECT * FROM ChangeRequest WHERE accepted=true ORDER BY request_time DESC LIMIT 10";
    $statement = $db->prepare($query);       // compile
    $statement->execute();
    $result2 = $statement->fetchAll();      // fetch all results
    $statement->closeCursor();

    $result = array("unprocessed"=>$result1, "processed"=>$result2);

    return $result;
}

function adminAddStore(
    $store_name, $street_num, $street_name, $city, $state, $zip_code
) {
    global $db;

    $query = "CALL addStore(:store_name, :street_num, :street_name, :city, :state, :zipcode);";
    $statement = $db->prepare($query);

    $statement->bindValue(':store_name', $store_name, PDO::PARAM_STR);
    $statement->bindValue(':street_num', $street_num, PDO::PARAM_STR);
    $statement->bindValue(':street_name', $street_name, PDO::PARAM_STR);
    $statement->bindValue(':city', $city, PDO::PARAM_STR);
    $statement->bindValue(':state', $state, PDO::PARAM_STR);
    $statement->bindValue(':zipcode', $zip_code, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
    
    echo "Added store \"$store_name\".";
}

function adminRemoveStore($store) {
    global $db;

    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));
    $store_name = trim($store_name);

    $query = "DELETE FROM Store WHERE store_ID = :store_id";
    $statement = $db->prepare($query);

    $statement->bindValue(':store_id', $store_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();
    
    echo "Removed store \"$store_name\".";
}

function adminAddItem($item_name, $item_brand, $item_category,
                        $item_description)
{
    global $db;

    $query = "INSERT INTO Item (name, brand, item_category, description) 
                VALUES (:item_name, :item_brand, :item_category, :item_description)";
    $statement = $db->prepare($query);

    $statement->bindValue(':item_name', $item_name, PDO::PARAM_STR);
    $statement->bindValue(':item_brand', $item_brand, PDO::PARAM_STR);
    $statement->bindValue(':item_category', $item_category, PDO::PARAM_STR);
    $statement->bindValue(':item_description', $item_description, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
    
    // echo "Added item \"$item_name\".";
}

function adminAddStoreItem($store, $item, $price, $weight, $unit) {
    // does not check if item already exists in the store

    global $db;

    $price = intval($price);
    $weight = intval($weight);
    $price_per_unit = round($price/$weight, 2);

    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));

    list($item_ID, $item_name) = explode(':', $item, 2);
    $item_ID = intval(trim($item_ID));

    $query = "INSERT INTO StoreItems (store, item, price, weight, unit, price_per_unit)
                values (:store, :item, :price, :weight, :unit, :price_per_unit)";
    $statement = $db->prepare($query);

    $statement->bindValue(':store', $store_ID, PDO::PARAM_INT);
    $statement->bindValue(':item', $item_ID, PDO::PARAM_INT);
    $statement->bindValue(':price', $price, PDO::PARAM_STR);
    $statement->bindValue(':weight', $weight, PDO::PARAM_STR);
    $statement->bindValue(':unit', $unit, PDO::PARAM_STR);
    $statement->bindValue(':price_per_unit', $price_per_unit, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
    
    echo "Added item \"$item_name\" to store \"$store_name\".";
}

function adminChangePrice($store, $item, $price, $weight, $unit) {
    global $db;

    $price = intval($price);
    $weight = intval($weight);
    $price_per_unit = round($price/$weight, 2);

    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));

    list($item_ID, $item_name) = explode(':', $item, 2);
    $item_ID = intval(trim($item_ID));

    $query = "UPDATE StoreItems
                SET price = :price, weight = :weight, unit = :unit, price_per_unit = :price_per_unit
                WHERE store = :store AND item = :item";
    $statement = $db->prepare($query);

    $statement->bindValue(':store', $store_ID, PDO::PARAM_INT);
    $statement->bindValue(':item', $item_ID, PDO::PARAM_INT);
    $statement->bindValue(':price', $price, PDO::PARAM_STR);
    $statement->bindValue(':weight', $weight, PDO::PARAM_STR);
    $statement->bindValue(':unit', $unit, PDO::PARAM_STR);
    $statement->bindValue(':price_per_unit', $price_per_unit, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
    
    echo "Change price of item \"$item_name\" from store \"$store_name\".";
}

function adminAddSale($store, $item_ID, $sale_price, $start_date, $end_date) {
    global $db; 
    
    // 1. Extract store ID from the combined string
    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));
    $store_name = trim($store_name);
    
    // 2. Get item ID based on name (assuming unique item names)
    // $item_ID = getItemIDByName($item_name);
    
    // 3. Insert sale details into the Sale table
    $sql = "INSERT INTO Sale (item, store, start_date, end_date, sale_price)
            VALUES (:item_ID, :store_ID, :start_date, :end_date, :sale_price)";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':item_ID', $item_ID, PDO::PARAM_INT);
    $stmt->bindValue(':store_ID', $store_ID, PDO::PARAM_INT);
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
    $stmt->bindValue(':sale_price', $sale_price, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();
}

function acceptChangeRequest($request_ID) {
    global $db;
    
    $query = "UPDATE ChangeRequest
                SET accepted = true
                WHERE request_ID = :request_ID";
    $statement = $db->prepare($query);

    $statement->bindValue(':request_ID', $request_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();
}

function rejectChangeRequest($request_ID) {
    global $db;

    $query = "UPDATE ChangeRequest
                SET accepted = false
                WHERE request_ID = :request_ID";
    $statement = $db->prepare($query);

    $statement->bindValue(':request_ID', $request_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();
}

function addReview($user_ID, $item, $store, $image=null, $comment, $rating) {
    global $db;

    $query = "INSERT INTO Review (user, item, store, image, comment, rating, review_time) 
                VALUES (:user, :item, :store, :image, :comment, :rating, :review_time)";
    $statement = $db->prepare($query);

    $dateTime = new DateTime();
    $review_time = $dateTime->format('Y-m-d_H:i:s');

    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));

    list($item_ID, $item_name) = explode(':', $item, 2);
    $item_ID = intval(trim($item_ID));

    $statement->bindValue(':user', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':item', $item_ID, PDO::PARAM_INT);
    $statement->bindValue(':store', $store_ID, PDO::PARAM_INT);
    $statement->bindValue(':image', $image, PDO::PARAM_STR);
    $statement->bindValue(':comment', $comment, PDO::PARAM_STR);
    $statement->bindValue(':rating', $rating, PDO::PARAM_INT);
    $statement->bindValue(':review_time', $review_time, PDO::PARAM_STR);

    $statement->execute();
    $statement->closeCursor();
}

function getReviews($user_ID) {
    global $db;

    $query = "SELECT r.review_ID, r.item, r.store, r.comment, r.rating, r.review_time,
              i.name AS item_name, i.brand, s.name AS store_name, r.image
              FROM Review r
              JOIN Item i ON r.item = i.item_ID
              JOIN Store s ON r.store = s.store_ID
              WHERE r.user = :user_id
              ORDER BY r.review_time DESC";

    $statement = $db->prepare($query);

    $statement->bindValue(':user_id', $user_ID, PDO::PARAM_INT);

    $statement->execute();
    $favorites = $statement->fetchAll();
    $statement->closeCursor();
    
    return $favorites;
}

function deleteReview($review_ID) {
    global $db;

    $query = "DELETE FROM Review WHERE review_ID = :review_ID";
    $statement = $db->prepare($query);

    $statement->bindValue(':review_ID', $review_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();
}

function getUserAddresses($user_ID) {
    global $db;

    $query = "SELECT *
              FROM AddressBook ab
              JOIN Address a ON ab.address = a.address_ID
              WHERE ab.user = :user_id";

    $statement = $db->prepare($query);

    $statement->bindValue(':user_id', $user_ID, PDO::PARAM_INT);

    $statement->execute();
    $favorites = $statement->fetchAll();
    $statement->closeCursor();
    
    return $favorites;
}

/**
 * Reset is_primary in AddressBook, set new primary, update _User address
 * @param int $user_ID target user_ID
 * @param int $address_ID new address_ID to be made primary
 */
function makeAddressPrimary($user_ID, $address_ID) {
    global $db;

    $query = "UPDATE AddressBook
            SET is_primary = false 
            WHERE AddressBook.user = :user_ID AND is_primary = true";
    
    $statement = $db->prepare($query);
    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();

    $query = "UPDATE AddressBook
            SET is_primary = true 
            WHERE AddressBook.user = :user_ID AND address = :address_ID";

    $statement = $db->prepare($query);
    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':address_ID', $address_ID, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();

    $query = "UPDATE _User
            SET address = :address_ID 
            WHERE user_ID = :user_ID";

    $statement = $db->prepare($query);
    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':address_ID', $address_ID, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();
}

function deleteAddress($user_ID, $address_ID) {
    global $db;

    $query = "DELETE FROM AddressBook WHERE user = :user_ID AND address = :address_ID";
    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':address_ID', $address_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();

    $query = "DELETE FROM Address WHERE address_ID = :address_ID";
    $statement = $db->prepare($query);

    $statement->bindValue(':address_ID', $address_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();
}

function requestChangeAddFavorites($user_ID,  $item_ID, $store_ID)
{
    echo "request favorite";
    global $db;

    $dateTime = new DateTime();
    $dateTime = $dateTime->format('Y-m-d H:i:s');
    $query = "INSERT INTO Favorites (user, item, store, added_date, notification_enabled) 
                VALUES (:user, :item, :store, :added_date, false)";
    $statement = $db->prepare($query);

    $statement->bindValue(':user', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':item', $item_ID, PDO::PARAM_INT);
    $statement->bindValue(':store', $store_ID, PDO::PARAM_INT);
    $statement->bindValue(':added_date', $dateTime, PDO::PARAM_STR);

    try {
        $statement->execute();
        echo "Added 'add to favorite' request.";
    } catch (PDOException $e) {
        echo "Error adding favorite: " . $e->getMessage();
    }
    
    $statement->closeCursor();
}

function requestChangeAddHistory($user_ID, $item_ID) {
    echo "request history";
    global $db;

    $query = "INSERT INTO PurchaseHistory (user, item, quantity) 
                VALUES (:user, :item, 1)";
    $statement = $db->prepare($query);

    $statement->bindValue(':user', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':item', $item_ID, PDO::PARAM_INT);

    try {
        $statement->execute();
        echo "Added 'add to favorite' request.";
    } catch (PDOException $e) {
        echo "Error adding favorite: " . $e->getMessage();
    }

    $statement->closeCursor();
}

function getHistory($user_ID) {
    global $db;

    $query = "SELECT DISTINCT p.item, i.brand, i.name, i.item_category, SUM(p.quantity) AS total_quantity
              FROM PurchaseHistory p
              JOIN Item i ON p.item = i.item_ID
              WHERE p.user = :user_id
              GROUP BY p.item, i.brand, i.name, i.item_category";

    $statement = $db->prepare($query);

    $statement->bindValue(':user_id', $user_ID, PDO::PARAM_INT);

    $statement->execute();
    $favorites = $statement->fetchAll();
    $statement->closeCursor();
    
    return $favorites;
}

function getMemberships($user_ID) {
    global $db;

    $query = "SELECT m.is_VIP, m.store, s.name, a.street_num, 
              a.street_name, a.city, a.state, a.zipcode
              FROM Membership m
              JOIN Store s ON m.store = s.store_ID
              JOIN Address a ON s.address = a.address_ID
              WHERE m.user = :user_id";

    $statement = $db->prepare($query);

    $statement->bindValue(':user_id', $user_ID, PDO::PARAM_INT);

    $statement->execute();
    $favorites = $statement->fetchAll();
    $statement->closeCursor();
    
    return $favorites;
}

function deleteMembership($user_ID, $store_ID) {
    global $db;

    $query = "DELETE FROM Membership WHERE user = :user_ID AND store = :store_ID";
    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':store_ID', $store_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();
}

function addMembership($user_ID, $store) {
    global $db;

    list($store_ID, $store_name) = explode(':', $store, 2);
    $store_ID = intval(trim($store_ID));

    $query = "INSERT INTO Membership (user, store, is_VIP) 
            VALUES (:user_ID, :store_ID, true)";
    $statement = $db->prepare($query);

    $statement->bindValue(':user_ID', $user_ID, PDO::PARAM_INT);
    $statement->bindValue(':store_ID', $store_ID, PDO::PARAM_INT);

    $statement->execute();
    $statement->closeCursor();
}
?>