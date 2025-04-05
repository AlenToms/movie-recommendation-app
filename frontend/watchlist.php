<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.html");
  exit();
}
include '../server/db.php';

$email = $_SESSION['email'];
$movies = $conn->query("SELECT * FROM watchlist WHERE email='$email'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Watchlist</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      color: white;
    }
    .card {
      background-color: #2c2f33;
      border: none;
      border-radius: 12px;
    }
    .card-img-top {
      height: 300px;
      object-fit: cover;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }
  </style>
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4 text-center text-warning">Your Watchlist</h2>
  <div class="row" id="watchlistContainer">
    <?php if ($movies->num_rows > 0): ?>
      <?php while ($row = $movies->fetch_assoc()): ?>
        <div class="col-md-3 mb-4">
          <div class="card h-100 text-white">
            <img src="<?= $row['poster'] ?>" class="card-img-top" alt="<?= $row['title'] ?>">
            <div class="card-body">
              <h5 class="card-title"><?= $row['title'] ?></h5>
            </div>
            <div class="card-footer bg-transparent border-0">
              <div class="d-grid gap-2">
                <button class="btn btn-success" onclick="markAsWatched(
                  '<?= $row['imdb_id'] ?>',
                  '<?= addslashes($row['title']) ?>',
                  '<?= $row['poster'] ?>',
                  this
                )">
                  <i class="bi bi-check-circle-fill"></i> Mark as Watched
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="text-center text-light mt-5">
        <h4>No movies in your watchlist yet.</h4>
        <p>Start adding some from the homepage!</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="watchSuccessModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-white bg-success">
      <div class="modal-header">
        <h5 class="modal-title">Success</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Movie marked as watched and removed from your watchlist.
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function markAsWatched(imdbID, title, poster, btn) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", title);
  formData.append("poster", poster);

  fetch("../server/mark-as-watched.php", {
    method: "POST",
    body: formData
  })
  .then(r => r.text())
  .then(res => {
    const clean = res.trim();
    console.log("WATCHLIST AJAX RESPONSE:", clean);
    if (clean === "success" || clean.includes("Already watched")) {
      const card = btn.closest(".col-md-3");
      if (card) card.remove();
      new bootstrap.Modal(document.getElementById("watchSuccessModal")).show();

      const remaining = document.querySelectorAll("#watchlistContainer .col-md-3").length;
      if (remaining === 0) {
        document.getElementById("watchlistContainer").innerHTML = `
          <div class='text-center text-light mt-5'>
            <h4>No movies in your watchlist yet.</h4>
            <p>Start adding some from the homepage!</p>
          </div>`;
      }
    } else {
      alert("Something went wrong: " + clean);
    }
  });
}
</script>
</body>
</html>