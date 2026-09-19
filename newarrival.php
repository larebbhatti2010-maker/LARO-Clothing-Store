<?php


$servername = "localhost";
$username = "root";      // default XAMPP username
$password = "";          // default XAMPP password (empty)
$dbname   = "LARO-Clothing-Store";   // your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    echo("Connection failed: " . mysqli_connect_error());
}

// Optional: set character set
mysqli_set_charset($conn, "utf8mb4");
?>

