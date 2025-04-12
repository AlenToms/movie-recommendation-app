<?php
ob_clean(); // Clean any prior output (remove invisible spaces)
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $email = $_POST["email"];
  $password = $_POST["password"];
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  $activation_code = bin2hex(random_bytes(16));

  // ✅ Step 1: Check if user exists
  $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo "exists";
    exit();
  }

  // ✅ Step 2: Insert inactive user with activation code
  $insert = $conn->prepare("INSERT INTO users (email, password, activation_code, is_active) VALUES (?, ?, ?, 0)");
  $insert->bind_param("sss", $email, $hashed_password, $activation_code);

  if (!$insert->execute()) {
    echo "insert_failed: " . $insert->error;
    exit();
  }

  // ✅ Step 3: Setup PHPMailer
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'alentomsie1@gmail.com';         // ⚠️ your Gmail
    $mail->Password   = 'xtyx vzrr lace wlba';           // ⚠️ your Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('yourgmail@gmail.com', 'MoodFlix
');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Activate Your MoodFlix
 Account';
    $activation_link = "http://localhost/movie-recommendation-app/server/activate.php?code=$activation_code";
    $mail->Body = "
      <h3>Welcome to MoodFlix
 🎬</h3>
      <p>Click below to activate your account:</p>
      <a href='$activation_link' style='padding:10px 20px;background:#007bff;color:#fff;'>Activate Now</a>
    ";

    if ($mail->send()) {
      echo "success";
    } else {
      echo "mail_failed: " . $mail->ErrorInfo;
    }
  } catch (Exception $e) {
    echo "mailer_exception: " . $e->getMessage();
  }

  $check->close();
  $insert->close();
  $conn->close();
}
?>
