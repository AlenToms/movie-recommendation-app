<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST["email"];
  $token = bin2hex(random_bytes(16));

  // Check user exists
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows == 0) {
    echo "Email not found.";
    exit();
  }

  // Update token
  $update = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
  $update->bind_param("ss", $token, $email);
  $update->execute();

  // Send email
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'alentomsie1@gmail.com';        // your Gmail
    $mail->Password = 'xtyx vzrr lace wlba';          // Gmail app password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('yourgmail@gmail.com', 'MovieLand');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Reset Your MovieLand Password';

    $link = "http://localhost/movie-recommendation-app/frontend/reset-password.html?token=$token";
    $mail->Body = "
      <h3>Reset Your Password</h3>
      <p>Click the link below to reset your password:</p>
      <a href='$link'>Reset Password</a>
    ";

    $mail->send();
    echo "Password reset link sent to your email.";
  } catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
  }
  
  $stmt->close();
  $update->close();
  $conn->close();
}
?>
