<?php
header('Content-Type: application/json');
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $image_path = 'uploads/' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    
    $emotion = shell_exec("python3 ../backend/analyze_face.py " . escapeshellarg($image_path));
    $movies = shell_exec("python3 ../backend/recommend.py " . escapeshellarg(trim($emotion)));
    
    echo json_encode(["movies" => json_decode($movies)]);
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
