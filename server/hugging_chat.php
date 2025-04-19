<?php
header("Content-Type: application/json");

// Get the message from the frontend
$input = json_decode(file_get_contents("php://input"), true);
$message = $input["message"] ?? "";

if (!$message) {
  echo json_encode(["reply" => "Please enter a message."]);
  exit;
}

// ✅ Use your actual Hugging Face API key here directly
$huggingface_token = "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";  // <-- replace this with your real token

// Prepare cURL request
$ch = curl_init("https://api-inference.huggingface.co/models/bluenguyen/movie_chatbot_large_v1");

curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer $huggingface_token",
    "Content-Type: application/json"
  ],
  CURLOPT_POSTFIELDS => json_encode(["inputs" => $message])
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// Handle response
if ($err) {
  echo json_encode(["reply" => "Error contacting Hugging Face API."]);
  exit;
}

$data = json_decode($response, true);

// Handle different response formats
if (isset($data[0]["generated_text"])) {
  echo json_encode(["reply" => $data[0]["generated_text"]]);
} elseif (isset($data["error"])) {
  echo json_encode(["reply" => "API error: " . $data["error"]]);
} else {
  echo json_encode(["reply" => "Sorry, no response."]);
}
