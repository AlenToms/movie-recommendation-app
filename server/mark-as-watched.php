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

// Check if already watched
$check = $conn->prepare("SELECT * FROM watched WHERE email=? AND imdb_id=?");
$check->bind_param("ss", $email, $imdb_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
  echo "Already marked as watched!";
  exit();
} else {
  // Insert into watched
  $stmt = $conn->prepare("INSERT INTO watched (email, imdb_id, title, poster) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $email, $imdb_id, $title, $poster);
  $stmt->execute();

  // Optionally remove from watchlist
  $del = $conn->prepare("DELETE FROM watchlist WHERE email=? AND imdb_id=?");
  $del->bind_param("ss", $email, $imdb_id);
  $del->execute();

  echo "✅ Marked as watched!";
}
?>
