<?php
session_start();
include 'db.php';

$email = $_SESSION['email'];
$imdb_id = $_POST['imdb_id'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];

$stmt = $conn->prepare("INSERT INTO user_reviews (email, imdb_id, rating, comment)
  VALUES (?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)");
$stmt->bind_param("ssis", $email, $imdb_id, $rating, $comment);
$stmt->execute();

echo "saved";
