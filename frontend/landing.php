<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.html");
  exit();
}
include '../server/db.php';

$watchedIds = [];
$watchlistIds = [];
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];

  $res1 = $conn->query("SELECT imdb_id FROM watched WHERE email='$email'");
  while ($row = $res1->fetch_assoc()) {
    $watchedIds[] = $row['imdb_id'];
  }

  $res2 = $conn->query("SELECT imdb_id FROM watchlist WHERE email='$email'");
  while ($row = $res2->fetch_assoc()) {
    $watchlistIds[] = $row['imdb_id'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>MovieLand | Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #141e30, #243b55);
      color: white;
    }
    .card {
      background-color: #1c1c1e;
      border-radius: 12px;
      transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
      cursor: pointer;
      overflow: hidden;
    }
    .card:hover {
      transform: scale(1.05);
      box-shadow: 0 15px 30px rgba(255, 255, 255, 0.1);
    }
    .card-img-top {
      height: 350px;
      object-fit: cover;
      border-radius: 12px 12px 0 0;
    }
    .modal-content {
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
    }
    .dropdown-menu {
      max-height: 400px;
      overflow-y: auto;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold text-warning" href="#">MovieLand</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link active" href="landing.php">Home</a></li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="watchlistDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Watchlist</a>
        <ul class="dropdown-menu" aria-labelledby="watchlistDropdown" id="watchlistMenu"></ul>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="watchedDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Watched</a>
        <ul class="dropdown-menu" aria-labelledby="watchedDropdown" id="watchedMenu"></ul>
      </li>
    </ul>
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

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Now Streaming 🎬</h4>
    <button class="btn btn-sm btn-outline-light" onclick="fetchMovies()">🔀 Shuffle</button>
  </div>
  <div id="movies" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4"></div>
</div>

<!-- Movie Modal -->
<div class="modal fade" id="movieModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content text-dark p-4">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="movieTitle"></h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body d-md-flex gap-4">
        <img id="moviePoster" class="rounded shadow" style="max-width: 250px;" />
        <div>
          <p><strong>Year:</strong> <span id="movieYear"></span></p>
          <p><strong>Rating:</strong> <span id="movieRating"></span></p>
          <p class="small"><strong>Plot:</strong> <span id="moviePlot"></span></p>
          <div class="mt-3" id="actionButtons"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const watchedIds = <?= json_encode($watchedIds); ?>;
const watchlistIds = <?= json_encode($watchlistIds); ?>;
let currentMovie = {};
const movieContainer = document.getElementById("movies");

function fetchMovies() {
  const queries = ["action", "drama", "comedy", "thriller", "sci-fi", "adventure"];
  const query = queries[Math.floor(Math.random() * queries.length)];

  fetch(`https://www.omdbapi.com/?apikey=d371a630&s=${query}&type=movie`)
    .then(res => res.json())
    .then(data => {
      movieContainer.innerHTML = "";

      if (data.Search) {
        const promises = data.Search.map(movie =>
          fetch(`https://www.omdbapi.com/?apikey=d371a630&i=${movie.imdbID}&plot=short`).then(r => r.json())
        );

        Promise.all(promises).then(results => {
          results.forEach(movie => {
            if (!watchedIds.includes(movie.imdbID)) {
              const col = document.createElement("div");
              col.className = "col movie-card";
              col.dataset.imdbId = movie.imdbID;
              col.innerHTML = `
                <div class="card animate__animated animate__fadeIn" onclick="showModal(this)" data-movie='${JSON.stringify(movie).replace(/'/g, "&apos;")}' >
                  <img src="${movie.Poster !== "N/A" ? movie.Poster : 'https://via.placeholder.com/300x450'}" class="card-img-top" alt="${movie.Title}" />
                </div>`;
              movieContainer.appendChild(col);
            }
          });
        });
      }
    });
}

function showModal(cardElement) {
  const raw = cardElement.dataset.movie.replace(/&apos;/g, "'");
  const movie = JSON.parse(raw);
  currentMovie = movie;

  document.getElementById("movieTitle").textContent = movie.Title;
  document.getElementById("movieYear").textContent = movie.Year;
  document.getElementById("movieRating").textContent = movie.imdbRating;
  document.getElementById("moviePlot").textContent = movie.Plot;
  document.getElementById("moviePoster").src = movie.Poster;

  let html = "";
  if (watchedIds.includes(movie.imdbID)) {
    html += `<div class="text-success fw-bold">✅ Already Watched</div>`;
  } else {
    html += `<button class="btn btn-outline-success w-100 mb-2" onclick="markAsWatched('${movie.imdbID}')">👁 Mark as Watched</button>`;
  }

  if (watchlistIds.includes(movie.imdbID)) {
    html += `<div class="text-warning fw-bold">⭐ Already in Watchlist</div>`;
  } else {
    html += `<button class="btn btn-outline-warning w-100" onclick="addToWatchlist('${movie.imdbID}')">🔖 Add to Watchlist</button>`;
  }

  document.getElementById("actionButtons").innerHTML = html;
  new bootstrap.Modal(document.getElementById("movieModal")).show();
}

function markAsWatched(imdbID) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", currentMovie.Title);
  formData.append("poster", currentMovie.Poster);

  fetch("../server/mark-as-watched.php", {
    method: "POST",
    body: formData
  })
  .then(r => r.text())
  .then(() => {
    bootstrap.Modal.getInstance(document.getElementById("movieModal")).hide();
    document.querySelector(`[data-imdb-id='${imdbID}']`)?.remove();
    watchedIds.push(imdbID);
  });
}

function addToWatchlist(imdbID) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", currentMovie.Title);
  formData.append("poster", currentMovie.Poster);

  fetch("../server/add-to-watchlist.php", {
    method: "POST",
    body: formData
  })
  .then(r => r.text())
  .then(() => {
    bootstrap.Modal.getInstance(document.getElementById("movieModal")).hide();
    watchlistIds.push(imdbID);
  });
}

window.onload = fetchMovies;
</script>
</body>
</html>
