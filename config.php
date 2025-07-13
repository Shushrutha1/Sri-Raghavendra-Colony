
<?php

// Database configuration

//$dbhost = '184.168.115.128';
//$dbuser = 'dakshayani';
//$dbpass = 'bday_2024';
$dbhost = 'localhost';
$dbuser = 'dakshayani';
$dbpass = 'bday_2024';

$dbname = 'srcproject';

// Create a connection
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");

?>
