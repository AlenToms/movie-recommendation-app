<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
  echo "Unauthorized access.";
  exit();
}

$email = $_SESSION['email'];
$imdb_id = $_POST['imdb_id'];
$title = $_POST['title'];
$poster = $_POST['poster'];

// Check for duplicates
$check = $conn->prepare("SELECT * FROM watchlist WHERE email=? AND imdb_id=?");
$check->bind_param("ss", $email, $imdb_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
  echo "Already in your watchlist!";
} else {
  $stmt = $conn->prepare("INSERT INTO watchlist (email, imdb_id, title, poster) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $email, $imdb_id, $title, $poster);
  if ($stmt->execute()) {
    echo "✅ Added to watchlist!";
  } else {
    echo "Error: Could not add to watchlist.";
  }
}
?>
