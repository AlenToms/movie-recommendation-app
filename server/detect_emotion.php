<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["error" => "Invalid request method"]);
  exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['image'])) {
  echo json_encode(["error" => "No image provided"]);
  exit();
}

// Create uploads folder if not exists
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

// Save image
$imgData = $data['image'];
$imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
$imgData = str_replace(' ', '+', $imgData);
$imageName = 'temp_' . uniqid() . '.jpg';
$imagePath = $uploadDir . $imageName;
file_put_contents($imagePath, base64_decode($imgData));

// Run Python script
$venvPython = __DIR__ . '/../venv310/Scripts/python.exe';
$scriptPath = __DIR__ . '/analyze_emotion.py';
$cmd = "$venvPython $scriptPath $imagePath";
exec($cmd, $output, $status);

// Debug logging
@file_put_contents(__DIR__ . '/../logs/emotion_log.txt', "CMD: $cmd\nOUTPUT: " . implode("\n", $output) . "\n", FILE_APPEND);

$emotion = strtolower(trim($output[0] ?? ''));

if (!$emotion || $emotion === 'no_face') {
  unlink($imagePath);
  echo json_encode(["error" => "No face detected or emotion unclear."]);
  exit();
}

// Emotion-to-genre map
$genreMap = [
  "happy"    => ["comedy", "romance", "animation"],
  "sad"      => ["drama", "biography", "musical"],
  "angry"    => ["thriller", "crime", "action"],
  "surprise" => ["fantasy", "adventure", "sci-fi"],
  "neutral"  => ["documentary", "drama", "family"],
  "fear"     => ["horror", "mystery", "psychological"]
];

$genres = $genreMap[$emotion] ?? ["drama"];
$movies = [];
$apiKey = "3e7ca915";

foreach ($genres as $genre) {
  $query = urlencode($genre);
  
  for ($page = 1; $page <= 4; $page++) {
    foreach (["movie", "series"] as $type) {
      $url = "https://www.omdbapi.com/?apikey=$apiKey&s=$query&type=$type&page=$page";
      $res = @file_get_contents($url);
      $data = json_decode($res, true);

      if (!empty($data['Search'])) {
        foreach ($data['Search'] as $item) {
          $detailsUrl = "https://www.omdbapi.com/?apikey=$apiKey&i={$item['imdbID']}&plot=short";
          $details = @json_decode(file_get_contents($detailsUrl), true);

          if (
            $details &&
            $details['Response'] === 'True' &&
            $details['Poster'] !== 'N/A' &&
            $details['imdbRating'] !== 'N/A' &&
            (int)$details['Year'] >= 2020 &&
            floatval($details['imdbRating']) >= 6.0
          ) {
            $movies[] = [
              "imdbID"     => $details["imdbID"],
              "Title"      => $details["Title"],
              "Poster"     => $details["Poster"],
              "Year"       => $details["Year"],
              "Plot"       => $details["Plot"],
              "imdbRating" => $details["imdbRating"],
              "Type"       => $details["Type"]
            ];
          }
        }
      }
    }
  }
}

// Shuffle and limit results
shuffle($movies);
$movies = array_slice($movies, 0, 20); // Return up to 20 items

// Clean up temp image
unlink($imagePath);

// Final output
echo json_encode([
  "emotion" => $emotion,
  "movies" => $movies
]);
