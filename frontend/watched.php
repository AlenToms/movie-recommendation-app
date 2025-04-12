<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.html");
  exit();
}
include '../server/db.php';

$email = $_SESSION['email'];
$res = $conn->query("SELECT * FROM watched WHERE email='$email'");
$movies = [];
while ($row = $res->fetch_assoc()) {
  $movies[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Watched | RecomX</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #141e30, #243b55);
      color: white;
    }
    .card {
      background-color: #1c1c1e;
      border-radius: 12px;
      transition: transform 0.3s ease-in-out;
    }
    .card:hover {
      transform: scale(1.03);
    }
    .card-img-top {
      height: 350px;
      object-fit: cover;
    }
    .rating-star {
      color: gold;
      font-size: 1.2rem;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold text-warning" href="landing.php">RecomX</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="movies.php">Movies</a></li>
      <li class="nav-item"><a class="nav-link" href="series.php">Series</a></li>
      <li class="nav-item"><a class="nav-link" href="watchlist.php">Watchlist</a></li>
      <li class="nav-item"><a class="nav-link active" href="#">Watched</a></li>
      <li class="nav-item"><a class="nav-link" href="forme.php">For Me</a></li>
    </ul>
    <div class="text-white">Logged in as <?= $_SESSION['email'] ?></div>
  </div>
</nav>

<div class="container py-4">
  <h3 class="mb-4 text-warning">👁 Already Watched</h3>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php foreach ($movies as $movie): ?>
      <div class="col">
        <div class="card h-100">
          <img src="<?= $movie['poster'] ?>" class="card-img-top" alt="<?= $movie['title'] ?>">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title text-white"><?= $movie['title'] ?></h5>
            <div class="text-success fw-bold mt-auto">✅ Watched</div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>
