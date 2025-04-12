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
while ($row = $res->fetch_assoc()) $watched[] = $row['imdb_id'];

$res = $conn->query("SELECT imdb_id FROM watchlist WHERE email='$email'");
while ($row = $res->fetch_assoc()) $watchlist[] = $row['imdb_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>For Me | RecomX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <script src="face-api.min.js"></script>
  <style>
    body {
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      color: white;
    }
    .card {
      background: #1c1c1c;
      color: white;
      border: none;
      border-radius: 12px;
      transition: transform 0.3s ease;
      cursor: pointer;
    }
    .card:hover {
      transform: scale(1.05);
    }
    .movie-row {
      display: flex;
      overflow-x: auto;
      gap: 1rem;
      padding-bottom: 1rem;
    }
    .movie-row::-webkit-scrollbar {
      height: 8px;
    }
    .movie-row::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }
    .modal-content {
      background: #1f1f2e;
      color: #f1f1f1;
    }
    #video {
      border-radius: 10px;
      margin-bottom: 20px;
    }
    /* Custom horizontal scrollbar like Netflix */
.movie-row, .popular-movies, .trending-movies, .recommended-movies, .overflow-auto {
  scrollbar-width: thin;
  scrollbar-color: #888 transparent;
  overflow-x: auto;
  padding-bottom: 8px;
  transition: scrollbar-color 0.3s ease;
}

.movie-row::-webkit-scrollbar,
.popular-movies::-webkit-scrollbar,
.trending-movies::-webkit-scrollbar,
.recommended-movies::-webkit-scrollbar,
.overflow-auto::-webkit-scrollbar {
  height: 8px;
}

.movie-row::-webkit-scrollbar-track,
.popular-movies::-webkit-scrollbar-track,
.trending-movies::-webkit-scrollbar-track,
.recommended-movies::-webkit-scrollbar-track {
  background: transparent;
}

.movie-row::-webkit-scrollbar-thumb,
.popular-movies::-webkit-scrollbar-thumb,
.trending-movies::-webkit-scrollbar-thumb,
.recommended-movies::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.3);
  border-radius: 10px;
  transition: background-color 0.3s ease;
}

.movie-row:hover::-webkit-scrollbar-thumb,
.popular-movies:hover::-webkit-scrollbar-thumb,
.trending-movies:hover::-webkit-scrollbar-thumb,
.recommended-movies:hover::-webkit-scrollbar-thumb {
  background-color: #ffc107;
}

/* Make .row-main horizontally scrollable */
.row-main {
  display: flex;
  overflow-x: auto;
  padding: 10px 0;
  scroll-behavior: smooth;
  gap: 1rem;
  scrollbar-width: thin;
  scrollbar-color: #ffc107 transparent; /* golden color thumb */
}

/* For WebKit browsers like Chrome */
.row-main::-webkit-scrollbar {
  height: 8px;
}
.row-main::-webkit-scrollbar-track {
  background: transparent;
}
.row-main::-webkit-scrollbar-thumb {
  background-color: rgba(255, 193, 7, 0.6); /* Bootstrap warning yellow */
  border-radius: 10px;
}
.row-main:hover::-webkit-scrollbar-thumb {
  background-color: #ffc107;
}
.movie-row .card {
  min-width: 180px;
  max-width: 180px;
  height: 320px;
  flex: 0 0 auto;
  background-color: #1c1c1c;
  color: white;
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s ease-in-out;
  cursor: pointer;
}

.movie-row .card:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 20px rgba(255, 255, 255, 0.1);
}

.movie-row .card img {
  width: 100%;
  height: 240px;
  object-fit: cover;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}

.movie-row .card-body {
  padding: 8px;
  text-align: center;
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

<div class="container text-center mt-4">
  <h3>🎭 Let Your Mood Pick Your Show!</h3>
  <video id="video" width="320" height="240" autoplay muted></video>
  <p id="emotionStatus" class="fw-bold mt-3 text-warning"></p>
  <div class="row-main">
  <div class="movie-row" id="recommendations"></div>
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
        <img id="modalPoster" class="rounded shadow" style="max-width: 200px;">
        <div>
          <p><strong>Year:</strong> <span id="modalYear"></span></p>
          <p><strong>Rating:</strong> <span id="modalRating"></span></p>
          <p><strong>Genre:</strong> <span id="modalGenre"></span></p>
          <p><strong>Plot:</strong> <span id="modalPlot"></span></p>
          <div id="modalActions" class="mt-3"></div>
          <hr />
          <div id="reviewSection">
            <h6>Your Review</h6>
            <div id="userStars" class="mb-2"></div>
            <textarea class="form-control mb-2" id="userComment" placeholder="Write a comment..."></textarea>
            <button class="btn btn-sm btn-primary" onclick="submitReview()">Submit Review</button>
            <hr>
            <h6>Average Rating: <span id="avgRating">Loading...</span></h6>
            <div id="allReviews" class="text-white small"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const watched = <?= json_encode($watched); ?>;
const watchlist = <?= json_encode($watchlist); ?>;
let userRating = 0;
let currentMovie = {};

function setRating(val) {
  userRating = val;
  showStars();
}

function showStars() {
  const container = document.getElementById("userStars");
  container.innerHTML = "";
  for (let i = 1; i <= 5; i++) {
    const star = document.createElement("i");
    star.className = `bi bi-star${i <= userRating ? '-fill text-warning' : ''} rating-star`;
    star.style.cursor = "pointer";
    star.onclick = () => setRating(i);
    container.appendChild(star);
  }
}

function submitReview() {
  const formData = new FormData();
  formData.append("imdb_id", currentMovie.imdbID);
  formData.append("rating", userRating);
  formData.append("comment", document.getElementById("userComment").value);
  fetch("../server/submit-review.php", { method: "POST", body: formData })
    .then(r => r.text()).then(() => loadReviews(currentMovie.imdbID));
}

function loadReviews(imdbID) {
  fetch(`/movie-recommendation-app/server/reviews.php?imdb_id=${imdbID}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById("avgRating").textContent =
        (data.avg && !isNaN(data.avg)) ? parseFloat(data.avg).toFixed(1) : "No ratings";

      let html = "";
      data.reviews.forEach(r => {
        html += `<div class="mb-2"><strong>${r.email}</strong>: ⭐${r.rating}<br>${r.comment}</div>`;
      });
      document.getElementById("allReviews").innerHTML = html || "No reviews yet.";

      if (data.user) {
        userRating = parseInt(data.user.rating);
        document.getElementById("userComment").value = data.user.comment;
      } else {
        userRating = 0;
        document.getElementById("userComment").value = "";
      }

      showStars();
    });
}

function viewMovieDetails(movie) {
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
}

function markAsWatched(id, title, poster) {
  const fd = new FormData();
  fd.append("imdb_id", id);
  fd.append("title", title);
  fd.append("poster", poster);
  fetch("../server/mark-as-watched.php", { method: "POST", body: fd })
    .then(r => r.text()).then(alert);
}

function addToWatchlist(id, title, poster) {
  const fd = new FormData();
  fd.append("imdb_id", id);
  fd.append("title", title);
  fd.append("poster", poster);
  fetch("../server/add-to-watchlist.php", { method: "POST", body: fd })
    .then(r => r.text()).then(alert);
}

// Load face-api and detect emotion once
Promise.all([
  faceapi.nets.tinyFaceDetector.loadFromUri("models"),
  faceapi.nets.faceExpressionNet.loadFromUri("models")
]).then(startVideo);

function startVideo() {
  navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => {
      const video = document.getElementById("video");
      video.srcObject = stream;
      detectFirstEmotion(video);
    });
}

function detectFirstEmotion(video) {
  video.addEventListener("play", () => {
    const canvas = faceapi.createCanvasFromMedia(video);
    document.body.append(canvas);
    const dim = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, dim);

    const interval = setInterval(async () => {
      const detections = await faceapi
        .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceExpressions();
      if (detections.length > 0) {
        clearInterval(interval);
        const emotions = detections[0].expressions;
        const top = Object.entries(emotions).sort((a, b) => b[1] - a[1])[0][0];
        document.getElementById("emotionStatus").innerText = `😄 You seem ${top}! Recommended:`;
        fetchRecommendations(top);
      }
    }, 300);
  });
}

function fetchRecommendations(emotion) {
  const genreMap = {
    happy: ["comedy", "romance", "animation"],
    sad: ["drama", "biography", "musical"],
    angry: ["thriller", "crime", "action"],
    surprise: ["fantasy", "adventure", "sci-fi"],
    neutral: ["documentary", "drama", "family"],
    fear: ["horror", "mystery", "psychological"]
  };

  const genres = genreMap[emotion] || ["drama"];
  const container = document.getElementById("recommendations");

  genres.forEach(g => {
    fetch(`https://www.omdbapi.com/?apikey=3e7ca915&s=${g}&type=movie`)
      .then(res => res.json())
      .then(data => {
        if (data.Search) {
          data.Search.forEach(m => {
            fetch(`https://www.omdbapi.com/?apikey=3e7ca915&i=${m.imdbID}&plot=short`)
              .then(res => res.json())
              .then(movie => {
                if (movie.imdbRating !== "N/A" && parseFloat(movie.imdbRating) >= 6.5) {
                  const card = document.createElement("div");
                  card.className = "card";
                  card.style.width = "180px";
                  card.onclick = () => viewMovieDetails(movie);
                  card.innerHTML = `
                    <img src="${movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/300x450'}" class="card-img-top">
                    <div class="card-body text-center">
                      <h6 class="mb-0">${movie.Title}</h6>
                      <small>${movie.Year} ⭐ ${movie.imdbRating}</small>
                    </div>`;
                  container.appendChild(card);
                }
              });
          });
        }
      });
  });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
