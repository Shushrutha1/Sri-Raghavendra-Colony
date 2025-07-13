<?php
// Database configuration
$dbhost = '184.168.115.128'; // Remove 'http://'
//$host = 'localhost';
$username = 'dakshayani'; // Replace with your database username
$password = 'bday_2024';     // Replace with your database password
$database = 'srcproject'; // Replace with your database name

// Set headers for downloading the SQL file
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $database . '_backup_' . date('Y-m-d_H-i-s') . '.sql"');

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$tables = $connection->query("SHOW TABLES");
if (!$tables) {
    die("Error fetching tables: " . $connection->error);
}

while ($row = $tables->fetch_row()) {
    $table = $row[0];

    // Generate CREATE TABLE statement
    $createTable = $connection->query("SHOW CREATE TABLE `$table`")->fetch_row();
    echo $createTable[1] . ";\n\n";

    // Fetch table data
    $result = $connection->query("SELECT * FROM `$table`");
    while ($data = $result->fetch_assoc()) {
        $columns = array_keys($data);
        $values = array_map([$connection, 'real_escape_string'], array_values($data));
        echo "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES ('" . implode("', '", $values) . "');\n";
    }
    echo "\n\n";
}


$connection->close();

?>
