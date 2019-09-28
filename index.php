<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 09/27/19
 * Time: 09:05 PM
 */


$hostname="localhost";
$username="freder26_php";
$password="don't post passwords in discord";
$dbname="freder26_drup354";
$usertable="test_table";

// Create connection
$conn = mysqli_connect($hostname, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql = "SELECT * FROM Commodities_Amount";

foreach(mysqli_query($conn, $sql) as $row) {
    foreach ($row as $name => $data) {
        echo $name . " " . $data . PHP_EOL;
    }
}

mysqli_close($conn);
