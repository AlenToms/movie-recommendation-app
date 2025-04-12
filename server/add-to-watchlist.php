<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
  echo "Not logged in";
  exit();
}

$email = $_SESSION['email'];
$imdb = $_POST['imdb_id'];
$title = $_POST['title'];
$poster = $_POST['poster'];

// Check if already in watchlist
$check = $conn->query("SELECT * FROM watchlist WHERE email='$email' AND imdb_id='$imdb'");
if ($check->num_rows > 0) {
  echo "already";
  exit();
}

// Insert into watchlist
$stmt = $conn->prepare("INSERT INTO watchlist (email, imdb_id, title, poster) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $imdb, $title, $poster);
$stmt->execute();
echo "success";
?>
