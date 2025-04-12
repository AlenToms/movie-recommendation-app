<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.html");
  exit();
}
include '../server/db.php';

$email = $_SESSION['email'];
$watched = [];
$watchlist = [];

$res = $conn->query("SELECT imdb_id FROM watched WHERE email='$email'");
while ($row = $res->fetch_assoc()) {
  $watched[] = $row['imdb_id'];
}
$res = $conn->query("SELECT imdb_id FROM watchlist WHERE email='$email'");
while ($row = $res->fetch_assoc()) {
  $watchlist[] = $row['imdb_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Series | MoodFlix
</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      color: white;
    }
    .card {
      background: #1c1c1c;
      border-radius: 10px;
      overflow: hidden;
      transition: transform 0.3s ease;
      cursor: pointer;
    }
    .card:hover {
      transform: scale(1.05);
    }
    .card img {
      height: 270px;
      object-fit: cover;
    }
    .movie-row {
      display: flex;
      overflow-x: auto;
      gap: 1rem;
      padding-bottom: 1rem;
    }
    .movie-row::-webkit-scrollbar {
      height: 6px;
    }
    .movie-row::-webkit-scrollbar-thumb {
      background: #777;
      border-radius: 4px;
    }
    .modal-content {
      background-color: #1f1f2e;
      color: #f1f1f1;
      border-radius: 12px;
    }
    .section-title {
      color: #ffc107;
      font-weight: 600;
      font-size: 1.4rem;
      margin-top: 20px;
    }
    .rating-star {
      font-size: 1.5rem;
      cursor: pointer;
      color: #ccc;
    }
    .rating-star:hover, .rating-star.text-warning {
      color: #ffc107 !important;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold text-warning" href="landing.php">MoodFlix
</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="landing.php">Home</a></li>
      <li class="nav-item"><a class="nav-link active" href="movies.php">Movies</a></li>
      <li class="nav-item"><a class="nav-link " href="#">Series</a></li>
      <li class="nav-item"><a class="nav-link" href="watchlist.php">Watchlist</a></li>
      <li class="nav-item"><a class="nav-link" href="watched.php">Watched</a></li>
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

<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center">
    <h3 class="text-light">📺 Series</h3>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="bi bi-search"></i> Search</button>
  </div>

  <h5 class="section-title">🔥 Trending Series</h5>
  <div class="movie-row" id="trendingSeries"></div>

  <h5 class="section-title">⭐ Top Rated Series</h5>
  <div class="movie-row" id="topRatedSeries"></div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0">
        <h5 class="modal-title">Search Series</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search for series..." oninput="liveSearch()" />
      <div id="searchResults" class="movie-row"></div>
    </div>
  </div>
</div>

<!-- Series Modal -->
<div class="modal fade" id="movieModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body d-flex gap-4">
        <img id="modalPoster" class="rounded shadow" style="max-width: 200px;">
        <div>
          <p><strong>Year:</strong> <span id="modalYear"></span></p>
          <p><strong>Rating:</strong> <span id="modalRating"></span></p>
          <p><strong>Genre:</strong> <span id="modalGenre"></span></p>
          <p><strong>Plot:</strong> <span id="modalPlot"></span></p>
          <div id="modalActions" class="mt-3"></div>

          <hr>
          <div class="mt-3" id="reviewSection">
            <h6>Your Review</h6>
            <div id="userStars" class="mb-2"></div>
            <textarea class="form-control mb-2" id="userComment" placeholder="Write a comment..."></textarea>
            <button class="btn btn-sm btn-primary" onclick="submitReview()">Submit Review</button>
            <hr class="mt-4">
            <h6>Average Rating: <span id="avgRating">Loading...</span></h6>
            <div id="allReviews" class="small text-white"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const API_KEY = "3e7ca915";
const watched = <?= json_encode($watched); ?>;
const watchlist = <?= json_encode($watchlist); ?>;
let currentMovie = {};
let userRating = 0;

function loadSection(containerId, sort) {
  const container = document.getElementById(containerId);
  container.innerHTML = "";
  const queries = ["dark", "life", "dream", "man", "star"];
  const fetches = queries.map(q =>
    fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&s=${q}&type=series`)
      .then(r => r.json())
      .then(data => data.Search || [])
  );
  Promise.all(fetches).then(results => {
    const merged = results.flat();
    const detailPromises = merged.map(m =>
      fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&i=${m.imdbID}&plot=short`).then(r => r.json())
    );
    Promise.all(detailPromises).then(series => {
      const filtered = series.filter(m => m.Poster !== "N/A" && !isNaN(m.imdbRating));
      if (sort === "rating") filtered.sort((a, b) => parseFloat(b.imdbRating) - parseFloat(a.imdbRating));
      else filtered.sort((a, b) => parseInt(b.Year) - parseInt(a.Year));
      filtered.slice(0, 12).forEach(m => container.appendChild(createCard(m)));
    });
  });
}

function createCard(movie) {
  const card = document.createElement("div");
  card.className = "card";
  card.style.width = "180px";
  card.onclick = () => viewMovieDetails(movie.imdbID);
  card.innerHTML = `
    <img src="${movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/300x450'}" class="card-img-top">
    <div class="card-body p-2 text-center">
      <h6 class="card-title text-light small mb-0">${movie.Title}</h6>
      <small class="text-muted">${movie.Year}</small>
    </div>`;
  return card;
}

function viewMovieDetails(imdbID) {
  fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&i=${imdbID}&plot=short`)
    .then(res => res.json())
    .then(movie => {
      currentMovie = movie;
      document.getElementById("modalTitle").textContent = movie.Title;
      document.getElementById("modalPoster").src = movie.Poster;
      document.getElementById("modalYear").textContent = movie.Year;
      document.getElementById("modalRating").textContent = movie.imdbRating;
      document.getElementById("modalGenre").textContent = movie.Genre;
      document.getElementById("modalPlot").textContent = movie.Plot;

      let html = "";
      if (watched.includes(movie.imdbID)) {
        html += `<div class="text-success fw-bold">✅ Already Watched</div>`;
      } else {
        html += `<button class="btn btn-outline-success mb-2" onclick="markAsWatched('${movie.imdbID}', '${movie.Title}', '${movie.Poster}')">👁 Mark as Watched</button>`;
      }
      if (watchlist.includes(movie.imdbID)) {
        html += `<div class="text-warning fw-bold">⭐ Already in Watchlist</div>`;
      } else {
        html += `<button class="btn btn-outline-warning" onclick="addToWatchlist('${movie.imdbID}', '${movie.Title}', '${movie.Poster}')">🔖 Add to Watchlist</button>`;
      }

      document.getElementById("modalActions").innerHTML = html;
      loadReviews(movie.imdbID);
      new bootstrap.Modal(document.getElementById("movieModal")).show();
    });
}

function liveSearch() {
  const query = document.getElementById("searchInput").value.trim();
  const container = document.getElementById("searchResults");
  container.innerHTML = "";
  if (query.length < 2) return;

  fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&s=${encodeURIComponent(query)}&type=series`)
    .then(res => res.json())
    .then(data => {
      if (!data.Search) return;
      const details = data.Search.map(m =>
        fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&i=${m.imdbID}&plot=short`).then(r => r.json())
      );
      Promise.all(details).then(results => {
        results.forEach(m => {
          if (m.Poster !== "N/A") container.appendChild(createCard(m));
        });
      });
    });
}

function addToWatchlist(imdbID, title, poster) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", title);
  formData.append("poster", poster);
  fetch("../server/add-to-watchlist.php", { method: "POST", body: formData })
    .then(r => r.text()).then(() => alert("✅ Added to Watchlist!"));
}

function markAsWatched(imdbID, title, poster) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", title);
  formData.append("poster", poster);
  fetch("../server/mark-as-watched.php", { method: "POST", body: formData })
    .then(r => r.text()).then(() => alert("✅ Marked as Watched!"));
}

function loadReviews(imdbID) {
  fetch(`/movie-recommendation-app/server/reviews.php?imdb_id=${imdbID}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById("avgRating").textContent = data.avg ? parseFloat(data.avg).toFixed(1) : "No ratings";

      let html = "";
      data.reviews.forEach(r => {
        html += `<div class="mb-2"><strong>${r.email}</strong>: ⭐${r.rating}<br>${r.comment}</div>`;
      });
      document.getElementById("allReviews").innerHTML = html || "No reviews yet.";

      userRating = data.user ? parseInt(data.user.rating) : 0;
      document.getElementById("userComment").value = data.user ? data.user.comment : "";
      showStars();
    });
}

function showStars() {
  const container = document.getElementById("userStars");
  container.innerHTML = "";
  for (let i = 1; i <= 5; i++) {
    const star = document.createElement("i");
    star.className = `bi bi-star${i <= userRating ? '-fill text-warning' : ''} rating-star`;
    star.onclick = () => setRating(i);
    container.appendChild(star);
  }
}

function setRating(val) {
  userRating = val;
  showStars();
}

function submitReview() {
  const formData = new FormData();
  formData.append("imdb_id", currentMovie.imdbID);
  formData.append("rating", userRating);
  formData.append("comment", document.getElementById("userComment").value);

  fetch("/movie-recommendation-app/server/submit-review.php", {
    method: "POST",
    body: formData
  })
  .then(r => r.text())
  .then(msg => {
    alert("✅ Review submitted!");
    loadReviews(currentMovie.imdbID);
  });
}

window.onload = () => {
  loadSection("trendingSeries", "latest");
  loadSection("topRatedSeries", "rating");
};
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
