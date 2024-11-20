<?php
// Remember to start the database server (or GCP SQL instance) before trying to connect to it
////////////////////////////////////////////
/** S24, PHP (on Google Standard App Engine) connect to MySQL instance (GCP) **/


// $username = 'root';                       // or your username
// $password = 'QWer1234!';     
// $host = 'smart-grocery-finder:us-east4:smart-grocery-finder';       // e.g., 'cs4750:us-east4:db-demo'; 
// $dbname = 'smart-grocery-finder';           // e.g., 'guestbook';
// $dsn = "mysql:unix_socket=/cloudsql/$host;dbname=$dbname";


//       e.g., "mysql:unix_socket=/cloudsql/cs4750:us-east4:db-demo;dbname=guestbook";

// to get instance connection name, go to GCP SQL overview page
////////////////////////////////////////////

/** S24, PHP (on local XAMPP or CS server) connect to MySQL instance (GCP) **/
// $username = 'root';
// $password = 'QWer1234!';
// $host = 'smart-grocery-finder:us-east4:smart-grocery-finder';       // e.g., 'cs4750:us-east4:db-demo'; 
// $dbname = 'smart-grocery-finder';           // e.g., 'guestbook';
// $dsn = "mysql:host=35.245.35.72;dbname=smart-grocery-finder";   // connect PHP (XAMPP) to DB (GCP)
//       e.g., "mysql:host=99.99.999.99;dbname=$dbname";   

// to get public IP addres of the SQL instance, go to GCP SQL overview page

// To connect from a local PHP to GCP SQL instance, need to add authormized network
// to allow (my)machine to connect to the SQL instance. 
// 1. Get IP of the computer that tries to connect to the SQL instance
//    (use http://ipv4.whatismyv6.com/ to find the IP address)
// 2. On the SQL connections page, add authorized networks, enter the IP address
////////////////////////////////////////////




/** S24, PHP (on GCP, local XAMPP, or CS server) connect to MySQL (on local XAMPP) **/
$username = 'pma';
$password = '';
$host = 'localhost:3306';
$dbname = 'azw2rz';    
$dsn = "mysql:host=$host;dbname=$dbname";  






////////////////////////////////////////////

/** S24, PHP (on GCP, local XAMPP, or CS server) connect to MySQL (on CS server) **/
// $username = 'azw2rz'; 
// $password = 'q1q1Q!Q!';
// $host = 'portal.cs.virginia.edu';
// $dbname = 'azw2rz';
// $dsn = "mysql:host=your-host-name:port-number;dbname=your-database-name";

////////////////////////////////////////////

// To find a hostname, access phpMyAdmin
// - select tob "User accounts"
// - locate the username you created, by default, the Host name is localhost

// To find a port number, access phpMyAdmin
// - use Console (bottom)
// - type     SHOW VARIABLES WHERE Variable_name = 'port';
// - execute the query    press Ctrl+Enter
// (default port to mySQL database in XAMPP is 3306)

// Be sure to use the correct database name (also case-sensitive)
//   Note: Looking in the wrong database and/or wrong table may results in either
//         cannot connect to the database, not find table, or no result set.
//         Thus, specify the correct database name


// DSN (Data Source Name) specifies the host computer for the MySQL datbase 
// and the name of the database. If the MySQL datbase is running on the same server
// as PHP, use the localhost keyword to specify the host computer

// To connect to a MySQL database named db-demo, need three arguments: 
// - specify a DSN, username, and password

// Create an instance of PDO (PHP Data Objects) which connects to a MySQL database
// (PDO defines an interface for accessing databases)
// Syntax: 
//    new PDO(dsn, username, password);


/** connect to the database **/
try 
{
//  $db = new PDO("mysql:host=$hostname;dbname=db-demo", $username, $password);
   $db = new PDO($dsn, $username, $password);
   
   // dispaly a message to let us know that we are connected to the database 
   // echo "<p>You are connected to the database -- host=$host</p>";
}
catch (PDOException $e)     // handle a PDO exception (errors thrown by the PDO library)
{
   // Call a method from any object, use the object's name followed by -> and then method's name
   // All exception objects provide a getMessage() method that returns the error message 
   $error_message = $e->getMessage();        
   echo "<p>An error occurred while connecting to the database: $error_message </p>";
}
catch (Exception $e)       // handle any type of exception
{
   $error_message = $e->getMessage();
   echo "<p>Error message: $error_message </p>";
}

?>