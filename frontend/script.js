const video = document.getElementById("webcam");
let emotionCaptured = false;

Promise.all([
  faceapi.nets.tinyFaceDetector.loadFromUri('models'),
  faceapi.nets.faceLandmark68Net.loadFromUri('models'),
  faceapi.nets.faceExpressionNet.loadFromUri('models')
]).then(startVideo);

function startVideo() {
  navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => video.srcObject = stream)
    .catch(err => alert("Camera access denied"));
}

video.addEventListener("play", () => {
  const canvas = faceapi.createCanvasFromMedia(video);
  document.body.append(canvas);

  const displaySize = { width: video.width, height: video.height };
  faceapi.matchDimensions(canvas, displaySize);

  const interval = setInterval(async () => {
    if (emotionCaptured) return;

    const detections = await faceapi
      .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceExpressions();

    const resized = faceapi.resizeResults(detections, displaySize);
    canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);
    faceapi.draw.drawDetections(canvas, resized);
    faceapi.draw.drawFaceLandmarks(canvas, resized);
    faceapi.draw.drawFaceExpressions(canvas, resized);

    if (detections.length > 0) {
      const expressions = detections[0].expressions;
      const sorted = Object.entries(expressions).sort((a, b) => b[1] - a[1]);
      const topEmotion = sorted[0][0];

      emotionCaptured = true;
      document.getElementById("emotionHeader").innerText = `You seem ${topEmotion}! Fetching suggestions...`;

      fetchRecommendations(topEmotion);
      clearInterval(interval);
    }
  }, 200);
});

function fetchRecommendations(emotion) {
  const genreMap = {
    happy: ["comedy", "romance", "animation"],
    sad: ["drama", "biography"],
    angry: ["thriller", "crime", "action"],
    surprise: ["fantasy", "sci-fi", "adventure"],
    neutral: ["documentary", "family", "drama"],
    fear: ["horror", "mystery", "psychological"]
  };

  const genres = genreMap[emotion] || ["drama"];
  const container = document.getElementById("recommendations");
  container.innerHTML = "";

  const apiKey = "3e7ca915";
  const fetchPromises = genres.map(g =>
    fetch(`https://www.omdbapi.com/?apikey=${apiKey}&s=${g}&type=movie`)
      .then(r => r.json())
      .then(data => data.Search || [])
  );

  Promise.all(fetchPromises).then(results => {
    const all = results.flat();
    const fetchDetails = all.map(m =>
      fetch(`https://www.omdbapi.com/?apikey=${apiKey}&i=${m.imdbID}&plot=short`).then(r => r.json())
    );

    Promise.all(fetchDetails).then(movies => {
      const filtered = movies
        .filter(m => m.Poster !== "N/A" && m.imdbRating !== "N/A")
        .sort((a, b) => parseFloat(b.imdbRating) - parseFloat(a.imdbRating));

      filtered.slice(0, 10).forEach(movie => {
        const card = document.createElement("div");
        card.className = "col";
        card.innerHTML = `
          <div class="card h-100 bg-dark text-light shadow-sm">
            <img src="${movie.Poster}" class="card-img-top" alt="${movie.Title}">
            <div class="card-body">
              <h5 class="card-title">${movie.Title}</h5>
              <p class="small text-muted mb-1">${movie.Year} · 
                <span class="badge bg-warning text-dark">⭐ ${movie.imdbRating}</span>
              </p>
              <p class="card-text">${movie.Plot?.substring(0, 100) || "No plot."}...</p>
              <button class="btn btn-outline-warning btn-sm mb-2" onclick='addToWatchlist("${movie.imdbID}", "${movie.Title}", "${movie.Poster}")'>
                <i class="bi bi-bookmark-plus-fill"></i> Add to Watchlist
              </button>
              <button class="btn btn-outline-success btn-sm" onclick='markAsWatched("${movie.imdbID}", "${movie.Title}", "${movie.Poster}")'>
                <i class="bi bi-eye-fill"></i> Mark as Watched
              </button>
            </div>
          </div>`;
        container.appendChild(card);
      });
    });
  });
}
