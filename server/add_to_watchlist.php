<?php
session_start();
include 'db.php';

if (!isset($_SESSION["email"])) {
    echo "Unauthorized";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION["email"];
    $imdbID = $_POST["imdbID"];
    $title = $_POST["title"];
    $year = $_POST["year"];
    $poster = $_POST["poster"];

    $stmt = $conn->prepare("INSERT INTO watchlist (email, imdbID, title, year, poster) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $imdbID, $title, $year, $poster);

    if ($stmt->execute()) {
        echo "Added to watchlist";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
