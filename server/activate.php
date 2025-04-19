<?php
include 'db.php';

if (isset($_GET["code"])) {
  $code = $_GET["code"];

  $stmt = $conn->prepare("SELECT * FROM users WHERE activation_code = ? AND is_active = 0");
  $stmt->bind_param("s", $code);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $update = $conn->prepare("UPDATE users SET is_active = 1, activation_code = NULL WHERE activation_code = ?");
    $update->bind_param("s", $code);
    if ($update->execute()) {
      echo "<h2 style='font-family:sans-serif;color:green;text-align:center;'>✅ Account activated! You can now <a href='../frontend/index.html'>login</a>.</h2>";
    } else {
      echo "<h2 style='color:red;'>Activation failed. Try again later.</h2>";
    }
    $update->close();
  } else {
    echo "<h2 style='color:red;'>Invalid or already activated link.</h2>";
  }

  $stmt->close();
} else {
  echo "<h2 style='color:red;'>Invalid activation request.</h2>";
}

$conn->close();
?>
