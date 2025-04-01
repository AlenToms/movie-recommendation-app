<?php
session_start();
if (!isset($_SESSION["email"])) {
    echo "<script>alert('You must be logged in! Please register if you don\\'t have an account.'); window.location.href = 'login.html';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to MovieLand</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS & AOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .movie-card {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .movie-card:hover {
            transform: scale(1.05);
        }
        .movie-poster {
            height: 320px;
            object-fit: cover;
            width: 100%;
        }
        .shuffle-btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">🎬 MovieLand</a>
    <div class="d-flex">
      <span class="navbar-text text-white me-3">
        Logged in as: <?php echo $_SESSION["email"]; ?>
      </span>
      <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
  </div>
</nav>

<!-- Genre Filter + Shuffle -->
<div class="container text-center mt-4">
    <h2>🎥 Discover Movies by Genre</h2>
    <div class="row justify-content-center mb-3">
        <div class="col-md-4">
            <select id="genreSelect" class="form-select">
                <option value="">All Genres</option>
                <option value="action">Action</option>
                <option value="drama">Drama</option>
                <option value="horror">Horror</option>
                <option value="romance">Romance</option>
                <option value="comedy">Comedy</option>
                <option value="sci-fi">Sci-Fi</option>
                <option value="thriller">Thriller</option>
            </select>
        </div>
    </div>
    <button onclick="fetchMovies()" class="btn btn-primary shuffle-btn">🎲 Shuffle Movies</button>
</div>

<!-- Movie Grid -->
<div class="container mt-4">
    <div class="row" id="movies"></div>
</div>

<!-- Movie Modal -->
<div class="modal fade" id="movieModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Movie Title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-column flex-md-row">
            <img id="modalPoster" src="" class="img-fluid me-md-4 mb-3" style="max-width: 200px;">
            <div>
                <p><strong>Year:</strong> <span id="modalYear"></span></p>
                <p><strong>Genre:</strong> <span id="modalGenre"></span></p>
                <p><strong>Plot:</strong> <span id="modalPlot"></span></p>
                <button id="addToWatchlist" class="btn btn-outline-success mt-2">➕ Add to Watchlist</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS + AOS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init();

const apiKey = 'd371a630'; // ✅ Your working OMDB API key

async function fetchMovies() {
    const selectedGenre = document.getElementById("genreSelect").value;
    const keywords = selectedGenre
        ? [selectedGenre]
        : ["action", "drama", "thriller", "romance", "war", "alien", "magic", "spy", "zombie", "superhero", "horror", "crime", "comedy"];

    const randomKeyword = keywords[Math.floor(Math.random() * keywords.length)];
    const url = `https://www.omdbapi.com/?apikey=${apiKey}&s=${randomKeyword}`;

    try {
        const response = await fetch(url);
        const data = await response.json();
        const container = document.getElementById("movies");
        container.innerHTML = "";

        if (data.Response === "True") {
    // Sort movies by year (descending)
    const sortedMovies = data.Search.sort((a, b) => {
        return parseInt(b.Year) - parseInt(a.Year);
    });

    sortedMovies.forEach(movie => {
        const col = document.createElement("div");
        col.className = "col-md-3 mb-4";
        col.setAttribute("data-aos", "fade-up");
        col.innerHTML = `
            <div class="card movie-card h-100" onclick="openMovieModal('${movie.imdbID}')">
                <img src="${movie.Poster !== "N/A" ? movie.Poster : "https://via.placeholder.com/300x450"}" class="movie-poster card-img-top">
                <div class="card-body">
                    <h6 class="card-title">${movie.Title}</h6>
                    <p class="card-text"><small>${movie.Year}</small></p>
                </div>
            </div>
        `;
        container.appendChild(col);
    });
} else {
            container.innerHTML = `<div class="col text-center"><p class="text-danger">No movies found. Try again!</p></div>`;
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
}

async function openMovieModal(imdbID) {
    const url = `https://www.omdbapi.com/?apikey=${apiKey}&i=${imdbID}&plot=full`;
    const response = await fetch(url);
    const movie = await response.json();

    document.getElementById("modalTitle").innerText = movie.Title;
    document.getElementById("modalYear").innerText = movie.Year;
    document.getElementById("modalGenre").innerText = movie.Genre;
    document.getElementById("modalPlot").innerText = movie.Plot;
    document.getElementById("modalPoster").src = movie.Poster !== "N/A" ? movie.Poster : "https://via.placeholder.com/300x450";

    const watchlistBtn = document.getElementById("addToWatchlist");
    watchlistBtn.onclick = () => addToWatchlist(movie);

    const myModal = new bootstrap.Modal(document.getElementById('movieModal'));
    myModal.show();
}

function addToWatchlist(movie) {
    const formData = new FormData();
    formData.append("imdbID", movie.imdbID);
    formData.append("title", movie.Title);
    formData.append("year", movie.Year);
    formData.append("poster", movie.Poster);

    fetch("/movie-recommendation-app/server/add_to_watchlist.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        alert(data.includes("Added") ? "🎉 Movie added to your watchlist!" : data);
    });
}

window.onload = fetchMovies;
document.getElementById("genreSelect").addEventListener("change", fetchMovies);
</script>

</body>
</html>
