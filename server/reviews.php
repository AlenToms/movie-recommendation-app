<?php
session_start();
include 'db.php';

$imdb_id = $_GET['imdb_id'] ?? '';
$email = $_SESSION['email'];

$response = ["avg" => null, "user" => null, "reviews" => []];

// Average rating
$res = $conn->prepare("SELECT AVG(rating) as avg FROM user_reviews WHERE imdb_id = ?");
$res->bind_param("s", $imdb_id);
$res->execute();
$res->bind_result($avg);
$res->fetch();
$response['avg'] = $avg;
$res->close();

// User's rating
$res = $conn->prepare("SELECT rating, comment FROM user_reviews WHERE imdb_id = ? AND email = ?");
$res->bind_param("ss", $imdb_id, $email);
$res->execute();
$res->bind_result($rating, $comment);
if ($res->fetch()) {
  $response['user'] = ["rating" => $rating, "comment" => $comment];
}
$res->close();

// All reviews
$res = $conn->prepare("SELECT email, rating, comment FROM user_reviews WHERE imdb_id = ?");
$res->bind_param("s", $imdb_id);
$res->execute();
$res->bind_result($uemail, $urating, $ucomment);
while ($res->fetch()) {
  $response['reviews'][] = ["email" => $uemail, "rating" => $urating, "comment" => $ucomment];
}
echo json_encode($response);
