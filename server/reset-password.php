<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $token = $_POST["token"];
  $password = $_POST["password"];
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Validate token
  $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ?");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows == 0) {
    echo "This reset link has already been used or is invalid. Please request a new one.";
    exit();
  }

  $stmt->bind_result($email);
  $stmt->fetch();

  // Update password & clear token
  $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE email = ?");
  $update->bind_param("ss", $hashed_password, $email);

  if ($update->execute()) {
    echo "✅ Password updated successfully.";
  } else {
    echo "Something went wrong while updating password.";
  }

  $stmt->close();
  $update->close();
  $conn->close();
}
?>
