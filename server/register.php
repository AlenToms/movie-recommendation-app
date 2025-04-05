<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST["email"];
  $password = $_POST["password"];
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo "exists";
  } else {
    $insert = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $insert->bind_param("ss", $email, $hashed_password);
    if ($insert->execute()) {
      echo "success";
    } else {
      echo "error";
    }
    $insert->close();
  }

  $check->close();
  $conn->close();
}
?>
