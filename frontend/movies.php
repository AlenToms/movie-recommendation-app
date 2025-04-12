<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: login.html");
  exit();
}
include '../server/db.php';

$watched = [];
$watchlist = [];
$email = $_SESSION['email'];

$w = $conn->query("SELECT imdb_id FROM watched WHERE email='$email'");
while ($r = $w->fetch_assoc()) $watched[] = $r['imdb_id'];

$wl = $conn->query("SELECT imdb_id FROM watchlist WHERE email='$email'");
while ($r = $wl->fetch_assoc()) $watchlist[] = $r['imdb_id'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Movies | RecomX

</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(to right, #141e30, #243b55);
      color: white;
    }
    .card {
      background: #1c1c1e;
      border: none;
      border-radius: 10px;
      overflow: hidden;
      transition: 0.3s;
      cursor: pointer;
    }
    .card:hover {
      transform: scale(1.03);
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .card-title {
      color: #fff !important;
      font-size: 1rem;
    }
    .section-title {
      color: #ffc107;
      font-size: 1.5rem;
      margin: 30px 0 15px;
    }
    .search-button {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
    }
    .modal-content {
      background-color: #1f1f2e;
      color: #fff;
    }
    #movieModal .modal-title {
      color: #ffc107;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold text-warning" href="landing.php">RecomX

</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="landing.php">Home</a></li>
      <li class="nav-item"><a class="nav-link active" href="#">Movies</a></li>
      <li class="nav-item"><a class="nav-link " href="series.php">Series</a></li>
      <li class="nav-item"><a class="nav-link" href="watchlist.php">Watchlist</a></li>
      <li class="nav-item"><a class="nav-link" href="watched.php">Watched</a></li>
      <li class="nav-item"><a class="nav-link" href="forme.php">For Me</a></li>
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
    <h3 class="text-light">🎞 Movies</h3>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="bi bi-search"></i> Search</button>
  </div>

  <h4 class="section-title">🔥 Trending Movies</h4>
  <div class="row g-3" id="trendingMovies"></div>

  <h4 class="section-title">⭐ Top Rated Movies</h4>
  <div class="row g-3" id="topRatedMovies"></div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-4">
      <div class="modal-header border-0">
        <h5 class="modal-title">🔍 Search Movies</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <input type="text" class="form-control mb-3" placeholder="Search..." oninput="liveSearch(this.value)" />
      <div class="row g-3" id="searchResults"></div>
    </div>
  </div>
</div>

<!-- Movie Modal -->
<div class="modal fade" id="movieModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-4">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body d-flex gap-4">
        <img id="modalPoster" class="rounded shadow" style="width: 200px;" />
        <div>
          <p><strong>Year:</strong> <span id="modalYear"></span></p>
          <p><strong>Rating:</strong> <span id="modalRating"></span></p>
          <p><strong>Genre:</strong> <span id="modalGenre"></span></p>
          <p><strong>Plot:</strong> <span id="modalPlot"></span></p>
          <div id="modalActions" class="mt-3"></div>
        </div>
      </div>
      <!-- Ratings & Reviews -->
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

<script>
const watched = <?= json_encode($watched); ?>;
const watchlist = <?= json_encode($watchlist); ?>;

function createCard(movie) {
  const div = document.createElement("div");
  div.className = "col-6 col-sm-4 col-md-3 col-lg-2";
  div.innerHTML = `
    <div class="card" onclick='viewMovieDetails("${movie.imdbID}")'>
      <img src="${movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/300x450'}" class="card-img-top" style="height:270px; object-fit:cover;">
      <div class="card-body p-2">
        <h6 class="card-title text-center">${movie.Title}</h6>
      </div>
    </div>`;
  return div;
}

function fetchMovies(keyword, containerId, sortByRating = false) {
  fetch(`https://www.omdbapi.com/?apikey=3e7ca915&s=${keyword}&type=movie`)
    .then(res => res.json())
    .then(data => {
      if (!data.Search) return;
      const promises = data.Search.map(m => fetch(`https://www.omdbapi.com/?apikey=3e7ca915&i=${m.imdbID}&plot=short`).then(r => r.json()));
      Promise.all(promises).then(movies => {
        let filtered = movies.filter(m => m.imdbRating !== "N/A" && m.Poster !== "N/A");
        if (sortByRating) filtered.sort((a, b) => parseFloat(b.imdbRating) - parseFloat(a.imdbRating));
        const container = document.getElementById(containerId);
        container.innerHTML = "";
        filtered.slice(0, 12).forEach(m => container.appendChild(createCard(m)));
      });
    });
}

function viewMovieDetails(imdbID) {
  fetch(`https://www.omdbapi.com/?apikey=3e7ca915&i=${imdbID}&plot=full`)
    .then(r => r.json())
    .then(movie => {
      document.getElementById("modalTitle").textContent = movie.Title;
      document.getElementById("modalPoster").src = movie.Poster;
      document.getElementById("modalYear").textContent = movie.Year;
      document.getElementById("modalRating").textContent = movie.imdbRating;
      document.getElementById("modalPlot").textContent = movie.Plot;
      document.getElementById("modalGenre").textContent = movie.Genre;

      let html = "";
      if (watched.includes(movie.imdbID)) {
        html += `<div class="text-success fw-bold">✅ Already Watched</div>`;
      } else {
        html += `<button class="btn btn-outline-success w-100 mb-2" onclick="markAsWatched('${movie.imdbID}', '${movie.Title}', '${movie.Poster}')">👁 Mark as Watched</button>`;
      }
      if (watchlist.includes(movie.imdbID)) {
        html += `<div class="text-warning fw-bold">⭐ Already in Watchlist</div>`;
      } else {
        html += `<button class="btn btn-outline-warning w-100" onclick="addToWatchlist('${movie.imdbID}', '${movie.Title}', '${movie.Poster}')">🔖 Add to Watchlist</button>`;
      }

      document.getElementById("modalActions").innerHTML = html;
      new bootstrap.Modal(document.getElementById("movieModal")).show();
    });
}

function markAsWatched(id, title, poster) {
  const formData = new FormData();
  formData.append("imdb_id", id);
  formData.append("title", title);
  formData.append("poster", poster);
  fetch("../server/mark-as-watched.php", { method: "POST", body: formData })
    .then(r => r.text())
    .then(res => alert("✅ Marked as watched!"));
}

function addToWatchlist(imdbID, title, poster) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", title);
  formData.append("poster", poster);

  fetch("../server/add-to-watchlist.php", {
    method: "POST",
    body: formData
  })
  .then(r => r.text())
  .then(response => {
    if (response.includes("already")) {
      alert("📌 Already in your Watchlist!");
    } else if (response.includes("success")) {
      alert("✅ Added to Watchlist!");
      watchlist.push(imdbID); // update client state
      updateModalButtons(imdbID); // optional: if modal is open
    } else {
      alert("⚠️ Failed to add. Try again.");
    }
  });
}

function liveSearch(q) {
  const container = document.getElementById("searchResults");
  if (q.length < 2) {
    container.innerHTML = "<p class='text-light'>Type at least 2 characters...</p>";
    return;
  }

  fetch(`https://www.omdbapi.com/?apikey=3e7ca915&s=${encodeURIComponent(q)}&type=movie`)
    .then(res => res.json())
    .then(data => {
      container.innerHTML = "";
      if (!data.Search) {
        container.innerHTML = "<p class='text-light'>No results found.</p>";
        return;
      }
      const promises = data.Search.map(m =>
        fetch(`https://www.omdbapi.com/?apikey=3e7ca915&i=${m.imdbID}&plot=short`).then(r => r.json())
      );
      Promise.all(promises).then(movies => {
        movies
          .filter(m => m.Poster !== "N/A" && m.imdbRating !== "N/A")
          .slice(0, 10)
          .forEach(m => container.appendChild(createCard(m)));
      });
    });
}

window.onload = () => {
  fetchMovies("action", "trendingMovies");
  fetchMovies("thriller", "topRatedMovies", true);
};
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
