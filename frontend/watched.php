<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.html");
  exit();
}
include '../server/db.php';

$email = $_SESSION['email'];
$result = $conn->query("SELECT * FROM watched WHERE email='$email'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Watched | RecomX

</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #141e30, #243b55);
      color: white;
    }
    .card {
      background-color: #1f1f1f;
      border-radius: 12px;
      overflow: hidden;
    }
    .card-img-top {
      height: 350px;
      object-fit: cover;
    }
  </style>
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4">Watched Movies</h2>
  <div class="row" id="watchedContainer">
    <?php if ($result->num_rows === 0): ?>
      <div class="text-muted text-center">You haven't watched any movies yet.</div>
    <?php else: ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-3 mb-4 movie-card" data-imdb-id="<?= $row['imdb_id'] ?>">
          <div class="card">
            <img src="<?= $row['poster'] ?>" class="card-img-top" alt="<?= $row['title'] ?>">
            <div class="card-body">
              <h6 class="card-title"><?= $row['title'] ?></h6>
              <div class="d-grid gap-2">
                <button class="btn btn-danger btn-sm" onclick="removeFromWatched('<?= $row['imdb_id'] ?>', this)">
                  <i class="bi bi-x-circle"></i> Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function removeFromWatched(imdbID, btn) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);

  fetch("../server/remove-from-watched.php", {
    method: "POST",
    body: formData
  })
  .then(r => r.text())
  .then(() => {
    btn.closest('.movie-card').remove();
    history.replaceState(null, "", location.href); // ✅ Flatten history
  });
}

</script>
</body>
</html>