<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = $_POST["email"];

  // Look up user
  $stmt = $conn->prepare("SELECT is_active FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows == 0) {
    echo "No account found with that email.";
    exit();
  }

  $stmt->bind_result($is_active);
  $stmt->fetch();

  if ($is_active) {
    echo "Your account is already activated. You can log in.";
    exit();
  }

  // Generate a new code and update
  $code = bin2hex(random_bytes(16));
  $update = $conn->prepare("UPDATE users SET activation_code = ? WHERE email = ?");
  $update->bind_param("ss", $code, $email);
  $update->execute();

  // Resend email
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'yourgmail@gmail.com';        // your Gmail
    $mail->Password = 'your_app_password';          // Gmail app password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('yourgmail@gmail.com', 'MovieLand');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Resend Activation - MovieLand';

    $activation_link = "http://localhost/movie-recommendation-app/server/activate.php?code=$code";
    $mail->Body = "
      <h3>Welcome back to MovieLand 🎬</h3>
      <p>Click the button below to activate your account:</p>
      <a href='$activation_link' style='display:inline-block;padding:10px 20px;background:#007bff;color:#fff;text-decoration:none;border-radius:5px;'>Activate Account</a>
      <p>If you didn't request this, you can ignore it.</p>
    ";

    $mail->send();
    echo "A new activation link has been sent to your email.";
  } catch (Exception $e) {
    echo "Failed to send email: " . $mail->ErrorInfo;
  }

  $stmt->close();
  $update->close();
  $conn->close();
}
?>
