CREATE TABLE IF NOT EXISTS Address (
    address_ID INT AUTO_INCREMENT PRIMARY KEY,
    street_num VARCHAR(10),
    street_name VARCHAR(100),
    city VARCHAR(100),
    state VARCHAR(100),
    zipcode VARCHAR(10)
);

DROP TABLE IF EXISTS _User;
CREATE TABLE _User (
    user_ID INT AUTO_INCREMENT PRIMARY KEY,
    age INT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address INT,
    admin BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (address) REFERENCES Address(address_ID) ON DELETE CASCADE
);

CREATE TABLE AddressBook (
    user INT,
    address INT,
    is_primary BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user) REFERENCES _User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (address) REFERENCES Address(address_ID) ON DELETE CASCADE,
    PRIMARY KEY (user, address)
);

DROP TABLE IF EXISTS Store;
CREATE TABLE Store (
    store_ID INT AUTO_INCREMENT PRIMARY KEY,
    address INT,
    name VARCHAR(100),
    store_category VARCHAR(100),
    FOREIGN KEY (address) REFERENCES Address(address_ID) ON DELETE CASCADE
);

DROP TABLE IF EXISTS Item;
CREATE TABLE Item (
    item_ID INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255),
    description TEXT,
    name VARCHAR(100),
    brand VARCHAR(100),
    item_category VARCHAR(100)
);

DROP TABLE IF EXISTS Review;
CREATE TABLE Review (
    review_ID INT AUTO_INCREMENT PRIMARY KEY,
    user INT,
    item INT,
    store INT,
    image VARCHAR(255),
    comment TEXT,
    rating INT,
    review_time DATETIME,
    FOREIGN KEY (user) REFERENCES _User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (item) REFERENCES Item(item_ID) ON DELETE CASCADE,
    FOREIGN KEY (store) REFERENCES Store(store_ID) ON DELETE CASCADE
);

DROP TABLE IF EXISTS Membership;
CREATE TABLE Membership (
    user INT,
    store INT,
    is_VIP BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user) REFERENCES _User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (store) REFERENCES Store(store_ID) ON DELETE CASCADE,
    PRIMARY KEY (user, store)
);

DROP TABLE IF EXISTS StoreItems;
CREATE TABLE StoreItems (
    store INT,
    item INT,
    price DECIMAL(10, 2),
    weight DECIMAL(10, 3),
    unit VARCHAR(10),
    price_per_unit DECIMAL(10, 2),
    FOREIGN KEY (store) REFERENCES Store(store_ID) ON DELETE CASCADE,
    FOREIGN KEY (item) REFERENCES Item(item_ID) ON DELETE CASCADE,
    PRIMARY KEY (store, item)
);

DROP TABLE IF EXISTS PurchaseHistory;
CREATE TABLE PurchaseHistory (
    purchase_ID INT AUTO_INCREMENT PRIMARY KEY,
    user INT,
    item INT,
    quantity INT,
    purchase_time DATETIME,
    FOREIGN KEY (user) REFERENCES _User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (item) REFERENCES Item(item_ID) ON DELETE CASCADE
);

DROP TABLE IF EXISTS Favorites;
CREATE TABLE Favorites (
    user INT,
    item INT,
    store INT,
    added_date DATETIME,
    notification_enabled BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user) REFERENCES _User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (item) REFERENCES Item(item_ID) ON DELETE CASCADE,
    FOREIGN KEY (store) REFERENCES Store(store_ID) ON DELETE CASCADE,
    PRIMARY KEY (user, item, store)
);

DROP TABLE IF EXISTS NotifySale;
CREATE TABLE NotifySale (
    user INT,
    item INT,
    store INT,
    notification_type VARCHAR(100),
    last_notified DATETIME,
    FOREIGN KEY (user) REFERENCES _User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (item) REFERENCES Item(item_ID) ON DELETE CASCADE,
    FOREIGN KEY (store) REFERENCES Store(store_ID) ON DELETE CASCADE,
    PRIMARY KEY (user, item, store)
);

DROP TABLE IF EXISTS Sale;
CREATE TABLE Sale (
    sale_ID INT AUTO_INCREMENT PRIMARY KEY,
    item INT,
    store INT,
    start_date DATETIME,
    end_date DATETIME,
    sale_price DECIMAL(10, 2),
    FOREIGN KEY (item) REFERENCES Item(item_ID) ON DELETE CASCADE,
    FOREIGN KEY (store) REFERENCES Store(store_ID) ON DELETE CASCADE
);

DELIMITER $$
CREATE TRIGGER check_sales_dates
BEFORE INSERT ON Sale
FOR EACH ROW
BEGIN	
	IF NEW.end_date <= NEW.start_date THEN
    	SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'end_date must be later than start_date';
    END IF;
END
$$
DELIMITER ;

CREATE TABLE ChangeRequest (
    request_ID INT AUTO_INCREMENT PRIMARY KEY,
    user INT,
    item INT,
    store INT,
    request_time DATETIME,
    change_details VARCHAR(1000),
    accepted BOOLEAN,
    FOREIGN KEY (user) REFERENCES _User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (item) REFERENCES Item(item_ID) ON DELETE CASCADE,
    FOREIGN KEY (store) REFERENCES Store(store_ID) ON DELETE CASCADE
);


DELIMITER $$
CREATE PROCEDURE addStoreItem (
    IN image VARCHAR(255),
    IN description TEXT,
    IN name VARCHAR(100),
    IN brand VARCHAR(100),
    IN store_name VARCHAR(100),
    IN street_num VARCHAR(10),
    IN street_name VARCHAR(100),
    IN city VARCHAR(100),
    IN state VARCHAR(100),
    IN zipcode VARCHAR(10),
    IN price DECIMAL(10, 2),
    IN weight DECIMAL(10, 3),
    IN unit VARCHAR(10),
    IN price_per_unit DECIMAL(10, 2)
)
BEGIN
	DECLARE item_ID INT;
    DECLARE store_ID INT;
    DECLARE address_ID INT;
    
    SELECT item_ID INTO item_ID FROM Item WHERE name = name AND brand = brand;
    IF item_ID IS NULL THEN
        INSERT INTO Item (image, description, name, brand, item_category)
        VALUES (image, description, name, brand, item_category);
        SET item_ID = LAST_INSERT_ID();
    END IF;
    
    SELECT address_ID INTO address_ID FROM Address 
    WHERE street_num = street_num AND street_name = street_name AND city = city AND zipcode = zipcode;
	IF address_ID IS NULL THEN
    	INSERT INTO Address (street_num, street_name, city, state, zipcode)
        VALUES (street_num, street_name, city, state, zipcode);
        SET address_ID = LAST_INSERT_ID();
    END IF;
    
    SELECT store_ID INTO store_ID FROM Store WHERE address = address_ID;
    IF store_ID IS NULL THEN
        INSERT INTO Store (address, name, store_category)
        VALUES (address_ID, store_name, store_category);
        SET store_ID = LAST_INSERT_ID();
    END IF;
    
    -- Add entry to StoreItems
    INSERT INTO StoreItems (store, item, price, weight, unit, price_per_unit)
    VALUES (store_ID, item_ID, price, weight, unit, price_per_unit);
END
$$
DELIMITER ;


DELIMITER $$
CREATE PROCEDURE addStore (
    IN store_name VARCHAR(100),
    IN street_num VARCHAR(10),
    IN street_name VARCHAR(100),
    IN city VARCHAR(100),
    IN state VARCHAR(100),
    IN zipcode VARCHAR(10)
)
BEGIN
    DECLARE store_ID INT;
    DECLARE address_ID INT;
    
    SELECT address_ID INTO address_ID FROM Address 
    WHERE street_num = street_num AND street_name = street_name AND city = city AND zipcode = zipcode LIMIT 1;
	IF address_ID IS NULL THEN
    	INSERT INTO Address (street_num, street_name, city, state, zipcode)
        VALUES (street_num, street_name, city, state, zipcode);
        SET address_ID = LAST_INSERT_ID();
    END IF;
    
    INSERT INTO Store (address, name)
    VALUES (address_ID, store_name);
END
$$
DELIMITER ;


INSERT INTO Address (street_num, street_name, city, state, zipcode) VALUES
('975', 'Hilton Heights Rd', 'Charlottesville', 'VA', '22901'),
('1050', 'Jefferson Ave', 'Redwood City', 'CA', '94063'),
('204', 'Baker St', 'San Francisco', 'CA', '94107'),
('1560', 'Lincoln Ave', 'Pasadena', 'CA', '91103'),
('230', 'Market St', 'San Jose', 'CA', '95110'),
('748', 'Ames St', 'Baldwin Park', 'CA', '91706'),
('322', 'Fifth Ave', 'Los Angeles', 'CA', '90003'),
('1987', 'Main St', 'Irvine', 'CA', '92614'),
('450', 'Broadway St', 'San Diego', 'CA', '92101'),
('77', 'Vine St', 'Hollywood', 'CA', '90028'),
('605', 'Sunset Blvd', 'Santa Monica', 'CA', '90401');

INSERT INTO _User (age, first_name, last_name, email, password, address) VALUES
(34, 'Alicia', 'Ramirez', 'alicia.ram@example.com', 'pass123!', 1),
(29, 'Ben', 'Chen', 'ben.chen88@example.com', 'mySecurePass$', 2),
(42, 'Carla', 'Diaz', 'carladz@example.com', 'diaz1234', 3),
(31, 'David', 'Smith', 'dav.smith@example.com', 'smithPass', 4),
(27, 'Elena', 'Morales', 'elena_m@example.com', 'moralesSecure', 5),
(36, 'Frank', 'Gupta', 'fgupta@example.com', 'frank2024!', 6),
(24, 'Gina', 'Huang', 'gina_huang@example.com', 'passwordGina', 7),
(39, 'Harold', 'Kim', 'h.kim@example.com', 'kimPass123', 8),
(28, 'Iris', 'Johnson', 'irisj@example.com', 'irisSecure!', 9),
(45, 'Jake', 'Martinez', 'jake.m@example.com', 'jakePass45', 10),
(22, 'Wilson', 'Zheng', 'azw2rz@virignia.edu', '$2y$10$7jHRr2xJB1MVrn/NbEhmU.Bl.IJB1sUMgtacAtz6fCf2yc3.ybJhm', 1);

INSERT INTO Store (address, name, store_category) VALUES
(1, 'Fresh Farm Produce', 'Grocery'),
(2, 'Everyday Needs', 'Convenience'),
(3, 'Tech Gadgets Central', 'Electronics'),
(4, 'Homemade Bakery', 'Food'),
(5, 'Quick Mart', 'Convenience');

INSERT INTO Item (image, description, name, brand, item_category) VALUES
('img/apple.jpg', 'Fresh organic apples from local farms.', 'Organic Apples', 'Farm Fresh', 'Fruits'),
('img/bread.jpg', 'Whole wheat bread, baked fresh daily.', 'Whole Wheat Bread', 'Homemade Bakery', 'Bakery'),
('img/coffee_maker.jpg', '12-cup programmable coffee maker with auto shut-off.', 'Coffee Maker', 'BrewPlus', 'Electronics');

INSERT INTO Review (user, item, store, image, comment, rating, review_time) VALUES
(1, 1, 1, 'img/review_coffee_maker.jpg', 'Great coffee maker for the price!', 4, NOW()),
(2, 1, 1, 'img/review_apples.jpg', 'The freshest apples I have bought!', 5, NOW()),
(4, 2, 1, 'img/review_green_tea.jpg', 'Very refreshing tea. Will buy again.', 4, NOW()),
(5, 2, 1, 'img/review_sanitizer.jpg', 'Effective and smells nice.', 4, NOW()),
(6, 1, 2, 'img/review_ice_cream.jpg', 'Best vanilla ice cream out there!', 5, NOW()),
(7, 1, 2, 'img/review_bread.jpg', 'Bread was very fresh and tasty.', 5, NOW()),
(8, 2, 2, 'img/review_ebook_reader.jpg', 'Love my new e-book reader!', 5, NOW()),
(9, 2, 2, 'img/review_flowers.jpg', 'Beautiful bouquet, lasted a long time.', 5, NOW());

INSERT INTO Membership (user, store, is_VIP) VALUES
(1, 1, FALSE),
(2, 2, TRUE),
(3, 1, TRUE),
(4, 2, FALSE),
(5, 1, TRUE),
(6, 2, FALSE),
(7, 1, TRUE),
(8, 2, FALSE),
(9, 1, TRUE),
(10, 2, FALSE);

INSERT INTO StoreItems (store, item, price, weight, unit, price_per_unit) VALUES
(1, 1, 2.99, 1, 'kg', 2.99),
(1, 2, 3.99, 1, 'kg', 3.99),
(2, 1, 3.99, 1, 'kg', 3.99),
(2, 2, 4.50, 0.5, 'kg', 9.00),
(3, 3, 29.99, 1, 'pc', 29.99);

INSERT INTO PurchaseHistory (user, item, quantity, purchase_time) VALUES
(1, 1, 5, NOW()),
(2, 2, 2, NOW()),
(3, 1, 3, NOW()),
(4, 2, 1, NOW()),
(5, 1, 5, NOW()),
(6, 2, 2, NOW()),
(7, 1, 1, NOW()),
(8, 2, 1, NOW()),
(9, 1, 2, NOW()),
(10, 2, 1, NOW());

INSERT INTO Favorites (user, item, store, added_date, notification_enabled) VALUES
(1, 1, 1, NOW(), TRUE),
(2, 2, 2, NOW(), FALSE);

INSERT INTO NotifySale (user, item, store, notification_type, last_notified) VALUES
(1, 1, 1, 'Price Drop', NOW()),
(2, 2, 2, 'New Stock', NOW());

INSERT INTO Sale (item, store, start_date, end_date, sale_price) VALUES
(1, 1, '2024-04-01 00:00:00', '2024-04-07 23:59:59', 1.99),
(2, 2, '2024-05-01 00:00:00', '2024-05-07 23:59:59', 3.99);

