<?php
session_start();
header('Content-Type: text/plain');

include 'db.php';

if (!isset($_SESSION['email'])) {
  http_response_code(401);
  echo "Unauthorized";
  exit();
}

$email = $_SESSION['email'];
$imdb_id = $_POST['imdb_id'] ?? '';
$title = $_POST['title'] ?? '';
$poster = $_POST['poster'] ?? '';

if (!$imdb_id || !$title || !$poster) {
  http_response_code(400);
  echo "Missing data";
  exit();
}

// Check if already watched
$check = $conn->prepare("SELECT 1 FROM watched WHERE email=? AND imdb_id=?");
$check->bind_param("ss", $email, $imdb_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
  echo "Already watched";
  exit();
}

// Insert into watched
$stmt = $conn->prepare("INSERT INTO watched (email, imdb_id, title, poster) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $imdb_id, $title, $poster);
$stmt->execute();

// Remove from watchlist if present
$del = $conn->prepare("DELETE FROM watchlist WHERE email=? AND imdb_id=?");
$del->bind_param("ss", $email, $imdb_id);
$del->execute();
file_put_contents("log.txt", "MARK: $email, $imdb_id\n", FILE_APPEND);
echo "success";

