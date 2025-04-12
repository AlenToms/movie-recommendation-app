<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.html");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>For Me 🎭 | MoodFlix
</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #141e30, #243b55);
      color: white;
    }
    .card {
      background-color: #1c1c1e;
      border: none;
      border-radius: 12px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: scale(1.04);
      box-shadow: 0 12px 25px rgba(255, 255, 255, 0.1);
    }
    .card-img-top {
      height: 350px;
      object-fit: cover;
      border-radius: 12px 12px 0 0;
    }
    #webcam {
      border-radius: 12px;
      margin: auto;
      display: block;
    }
  </style>
</head>
<body>
  <!-- ✅ NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold text-warning" href="landing.php">🎬 MoodFlix
</a>

  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="landing.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="watchlist.php">Watchlist</a></li>
      <li class="nav-item"><a class="nav-link" href="watched.php">Watched</a></li>
      <li class="nav-item"><a class="nav-link" href="forme.php">ForMe</a></li>
    </ul>

    <!-- ✅ Profile Icon -->
    <div class="dropdown">
      <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li class="dropdown-item disabled">Logged in as <strong><?= $_SESSION['email']; ?></strong></li>
        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

  <!-- ✅ Webcam + Capture Button -->
  <div class="container text-center mb-4">
    <video id="webcam" autoplay playsinline width="320" height="240" class="shadow-sm"></video>
    <button id="captureBtn" class="btn btn-warning mt-3">
      <i class="bi bi-camera-fill me-2"></i>Capture & Get Recommendations
    </button>
  </div>

  <!-- ✅ Loading -->
  <div class="text-center" id="loading" style="display: none;">
    <div class="spinner-border text-warning" role="status"></div>
    <p class="mt-2">Analyzing your mood...</p>
  </div>

  <!-- ✅ Emotion + Recommendations -->
  <div class="container">
    <h4 id="emotionHeader" class="text-center mb-4"></h4>
    <div id="recommendations" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4"></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="capture.js"></script> <!-- make sure this path is correct -->
</body>
</html>
