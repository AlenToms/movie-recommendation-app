<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.html");
  exit();
}
include '../server/db.php';

$email = $_SESSION['email'];

// Get all watched IDs
$watched = [];
$res1 = $conn->query("SELECT imdb_id FROM watched WHERE email='$email'");
while ($row = $res1->fetch_assoc()) {
  $watched[] = $row['imdb_id'];
}

// Get watchlist excluding watched
$movies = [];
$res2 = $conn->query("SELECT * FROM watchlist WHERE email='$email'");
while ($row = $res2->fetch_assoc()) {
  if (!in_array($row['imdb_id'], $watched)) {
    $movies[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Watchlist | RecomX</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
      <li class="nav-item"><a class="nav-link active" href="#">Watchlist</a></li>
      <li class="nav-item"><a class="nav-link" href="watched.php">Watched</a></li>
      <li class="nav-item"><a class="nav-link" href="forme.php">For Me</a></li>
    </ul>
    <div class="text-white">Logged in as <?= $_SESSION['email'] ?></div>
  </div>
</nav>

<div class="container py-4">
  <h3 class="mb-4 text-warning">⭐ Your Watchlist</h3>

  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php foreach ($movies as $movie): ?>
      <div class="col" data-imdb-id="<?= $movie['imdb_id'] ?>">
        <div class="card h-100">
          <img src="<?= $movie['poster'] ?>" class="card-img-top" alt="<?= $movie['title'] ?>">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title text-white"><?= $movie['title'] ?></h5>
            <div class="mb-2 text-warning">
              <i class="bi bi-star-fill rating-star"></i>
              <i class="bi bi-star-fill rating-star"></i>
              <i class="bi bi-star-fill rating-star"></i>
              <i class="bi bi-star-half rating-star"></i>
              <i class="bi bi-star rating-star"></i>
            </div>
            <button class="btn btn-outline-success mt-auto" onclick="markAsWatched('<?= $movie['imdb_id'] ?>', '<?= addslashes($movie['title']) ?>', '<?= $movie['poster'] ?>')">
              👁 Mark as Watched
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ✅ Modal for success -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light p-4">
      <div class="modal-body text-center">
        <h5>✅ Added to Watched!</h5>
        <button class="btn btn-warning mt-3" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
function markAsWatched(imdbID, title, poster) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", title);
  formData.append("poster", poster);

  fetch("../server/mark-as-watched.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === "success" || res.includes("Already watched")) {
      const card = document.querySelector(`[data-imdb-id="${imdbID}"]`);
      if (card) card.remove(); // ✅ remove it from DOM
      new bootstrap.Modal(document.getElementById("successModal")).show();
    } else {
      alert("❌ Something went wrong: " + res);
    }
  });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
