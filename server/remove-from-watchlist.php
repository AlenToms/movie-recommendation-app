<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
  echo "Unauthorized access.";
  exit();
}

$email = $_SESSION['email'];
$imdb_id = $_POST['imdb_id'];

$stmt = $conn->prepare("DELETE FROM watchlist WHERE email=? AND imdb_id=?");
$stmt->bind_param("ss", $email, $imdb_id);

if ($stmt->execute()) {
  header("Location: ../frontend/watchlist.php");
  exit();
} else {
  echo "Failed to remove.";
}
?>
