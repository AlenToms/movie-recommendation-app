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
  <title>Watched Movies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #141e30, #243b55);
      color: white;
    }
    .card {
      background-color: #1c1c1e;
      border-radius: 12px;
      color: white;
    }
    .card-img-top {
      height: 350px;
      object-fit: cover;
    }
  </style>
</head>
<body>
  <div class="container my-4">
    <h2 class="text-center mb-4">✅ Watched Movies</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="col">
          <div class="card">
            <img src="<?= $row['poster'] ?>" class="card-img-top" alt="<?= $row['title'] ?>">
            <div class="card-body text-center">
            <h5 class="card-title"><?= $row['title'] ?></h5>
            <form method="POST" action="../server/remove-from-watched.php" class="d-grid gap-2 mt-2">
                <input type="hidden" name="imdb_id" value="<?= $row['imdb_id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-x-circle-fill"></i> Remove
                </button>
            </form>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</body>
</html>
