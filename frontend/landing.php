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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

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
    .rating-star {
      font-size: 2rem;
      margin-right: 5px;
      transition: transform 0.2s, color 0.2s;
      cursor: pointer;
    }

    .rating-star:hover {
      transform: scale(1.2);
      color: #ffc107 !important;
    }
    .trending-movies, .popular-movies {
  scroll-snap-type: x mandatory;
}

.movie-card {
  scroll-snap-align: start;
  flex: 0 0 auto;
}


  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold text-warning" href="#">MovieLand</a>
<div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
    <li class="nav-item">
  <a class="nav-link" href="watchlist.php">Watchlist</a>
</li>
<li class="nav-item">
  <a class="nav-link" href="watched.php">Watched</a>
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
<!-- 🔥 Trending Movies -->
<h4 class="mt-4 mb-3">🔥 Trending Movies</h4>
<div class="trending-movies d-flex overflow-auto gap-3 mb-5 px-1" id="trendingContainer"></div>

<!-- ⭐ Popular Movies -->
<h4 class="mt-4 mb-3">⭐ Popular Movies</h4>
<div class="popular-movies d-flex overflow-auto gap-3 px-1" id="popularContainer"></div>
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

          <hr>
          <div class="mt-3" id="reviewSection">
            <h6>Your Review</h6>
            <div id="userStars" class="mb-2"></div>
            <textarea class="form-control mb-2" id="userComment" placeholder="Write a comment..."></textarea>
            <button class="btn btn-sm btn-primary" onclick="submitReview()">Submit Review</button>

            <hr class="mt-4">
            <h6>Average Rating: <span id="avgRating">Loading...</span></h6>
            <div id="allReviews" class="small text-muted"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
<hr>
<div class="mt-3" id="reviewSection">
  <h6>Your Review</h6>
  <div id="userStars" class="mb-2">
    <!-- Stars will be injected here -->
  </div>
  <textarea class="form-control mb-2" id="userComment" placeholder="Write a comment..."></textarea>
  <button class="btn btn-sm btn-primary" onclick="submitReview()">Submit Review</button>

  <hr class="mt-4">
  <h6>Average Rating: <span id="avgRating">Loading...</span></h6>
  <div id="allReviews" class="small text-muted"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

function showStars() {
  const container = document.getElementById("userStars");
  container.innerHTML = "";
  for (let i = 1; i <= 5; i++) {
    container.innerHTML += `<i class="bi bi-star${i <= userRating ? '-fill text-warning' : ''}" style="font-size: 1.5rem; cursor:pointer;" onclick="setRating(${i})"></i>`;
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
    alert("Review saved!");
    loadReviews(currentMovie.imdbID); // refresh
  });
}

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
  loadReviews(movie.imdbID);
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
  loadReviews(movie.imdbID);

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
  .then(response => response.text())
  .then(response => {
  const clean = response.trim();
  console.log("RAW RESPONSE:", clean);
  if (clean === "success" || clean.includes("Already watched")) {
    bootstrap.Modal.getInstance(document.getElementById("movieModal")).hide();
    document.querySelector(`[data-imdb-id='${imdbID}']`)?.remove();
    watchedIds.push(imdbID);
    new bootstrap.Modal(document.getElementById("successModal")).show();
  } else {
    alert("Something went wrong: " + clean);
  }
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
// Replacing live search dropdown with movie card rendering on the page
function handleSearch() {
  const query = document.getElementById("searchBox").value.trim();
  const movieContainer = document.getElementById("movies");
  const emptyMessage = document.getElementById("emptyMessage");

  if (query.length < 2) {
    fetchMovies(); // revert back to shuffle results if search box is cleared
    return;
  }

  fetch(`https://www.omdbapi.com/?apikey=d371a630&s=${encodeURIComponent(query)}&type=movie`)
    .then(res => res.json())
    .then(data => {
      movieContainer.innerHTML = "";
      if (data.Response === "True") {
        const promises = data.Search.map(movie =>
          fetch(`https://www.omdbapi.com/?apikey=d371a630&i=${movie.imdbID}&plot=short`).then(r => r.json())
        );

        Promise.all(promises).then(results => {
          let shown = 0;
          results.forEach(movie => {
            if (!watchedIds.includes(movie.imdbID)) {
              shown++;
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
          emptyMessage.style.display = shown === 0 ? "block" : "none";
        });
      } else {
        emptyMessage.style.display = "block";
      }
    });
}


function fetchAndShow(imdbID) {
  fetch(`https://www.omdbapi.com/?apikey=d371a630&i=${imdbID}&plot=short`)
    .then(res => res.json())
    .then(movie => {
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
      document.getElementById("suggestions").innerHTML = "";
      new bootstrap.Modal(document.getElementById("movieModal")).show();
    });
}


let userRating = 0;

function loadReviews(imdbID) {
  fetch(`/movie-recommendation-app/server/get-reviews.php?imdb_id=${imdbID}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById("avgRating").textContent = data.avg?.toFixed(1) || "No ratings";

      // Show all comments
      let html = "";
      data.reviews.forEach(r => {
        html += `<div class="mb-2"><strong>${r.email}</strong>: ⭐${r.rating}<br>${r.comment}</div>`;
      });
      document.getElementById("allReviews").innerHTML = html || "No reviews yet.";

      // Show user's previous rating
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

function showStars() {
  const container = document.getElementById("userStars");
  container.innerHTML = "";

  for (let i = 1; i <= 5; i++) {
    const star = document.createElement("i");
    star.className = `bi bi-star${i <= userRating ? '-fill text-warning' : ''} rating-star`;
    star.dataset.value = i;
    star.addEventListener("click", () => setRating(i));
    star.addEventListener("mouseover", () => highlightStars(i));
    star.addEventListener("mouseleave", () => highlightStars(userRating));
    container.appendChild(star);
  }
}

function highlightStars(level) {
  document.querySelectorAll("#userStars i").forEach((star, index) => {
    star.classList.toggle("text-warning", index < level);
  });
}
function loadTrendingMovies() {
  const trendingContainer = document.getElementById("trendingContainer");
  trendingContainer.innerHTML = "";

  // Use recent years as search terms
  const queries = ["2025", "2024", "2023"];

  const promises = queries.map(q =>
    fetch(`https://www.omdbapi.com/?apikey=d371a630&s=${q}&type=movie`)
      .then(res => res.json())
      .then(data => data.Search || [])
  );

  Promise.all(promises).then(allResults => {
    const flat = allResults.flat();
    const imdbFetches = flat.map(movie =>
      fetch(`https://www.omdbapi.com/?apikey=d371a630&i=${movie.imdbID}`).then(r => r.json())
    );

    Promise.all(imdbFetches).then(results => {
      const filtered = results
        .filter(m => m.imdbRating !== "N/A" && parseFloat(m.imdbRating) >= 6.5)
        .sort((a, b) => parseFloat(b.imdbRating) - parseFloat(a.imdbRating));

      trendingContainer.innerHTML = "";
      filtered.forEach(movie => {
        const card = createMovieCard(movie);
        trendingContainer.appendChild(card);
      });
    });
  });
}


function loadPopularMovies() {
  const keywords = ["action", "drama", "thriller", "sci-fi", "fantasy"];
  const query = keywords[Math.floor(Math.random() * keywords.length)];
  const popularContainer = document.getElementById("popularContainer");
  popularContainer.innerHTML = "";

  fetch(`https://www.omdbapi.com/?apikey=d371a630&s=${query}&type=movie`)
    .then(res => res.json())
    .then(data => {
      if (!data.Search) return;

      const detailsPromises = data.Search.map(m =>
        fetch(`https://www.omdbapi.com/?apikey=d371a630&i=${m.imdbID}&plot=short`).then(r => r.json())
      );

      Promise.all(detailsPromises).then(results => {
        const filtered = results
          .filter(m => m.imdbRating !== "N/A" && parseFloat(m.imdbRating) >= 6.5)
          .sort((a, b) => parseFloat(b.imdbRating) - parseFloat(a.imdbRating));

        popularContainer.innerHTML = "";
        filtered.forEach(movie => {
          const card = createMovieCard(movie);
          popularContainer.appendChild(card);
        });
      });
    });
}
window.onload = () => {
  loadTrendingMovies();
  loadPopularMovies();
};


function createMovieCard(movie) {
  const div = document.createElement("div");
  div.style.width = "180px";
  div.className = "movie-card";
  div.innerHTML = `
    <div class="card h-100 animate__animated animate__fadeIn" onclick="showModal(this)" data-movie='${JSON.stringify(movie).replace(/'/g, "&apos;")}'>
      <img src="${movie.Poster !== "N/A" ? movie.Poster : "https://via.placeholder.com/300x450"}" class="card-img-top" alt="${movie.Title}" style="height: 270px; object-fit: cover;">
      <div class="card-body p-2 text-center">
        <h6 class="card-title small mb-0">${movie.Title}</h6>
        <small class="text-muted">${movie.Year}</small>
      </div>
    </div>`;
  return div;
}


</script>
</body>
</html>
