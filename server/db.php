<?php
$host = "localhost"; // Change if needed
$user = "root";      // Your MySQL username
$pass = "";          // Your MySQL password
$dbname = "movie_app"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
