<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST["email"];
  $password = $_POST["password"];

  $stmt = $conn->prepare("SELECT password, is_active FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashed_password, $is_active);
    $stmt->fetch();

    if (!$is_active) {
      echo "Your account is not activated. Please check your email.";
    } elseif (password_verify($password, $hashed_password)) {
      $_SESSION["email"] = $email;
      echo "Login successful";
    } else {
      echo "Invalid credentials. Please try again.";
    }
  } else {
    echo "User not found. Please register first.";
  }

  $stmt->close();
  $conn->close();
}
?>
