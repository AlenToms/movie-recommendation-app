const video = document.getElementById("webcam");
const captureBtn = document.getElementById("captureBtn");

navigator.mediaDevices.getUserMedia({ video: true })
  .then(stream => {
    video.srcObject = stream;
  })
  .catch(err => {
    alert("Camera access denied. Cannot analyze mood.");
  });

captureBtn.onclick = () => {
  const canvas = document.createElement("canvas");
  canvas.width = 640;
  canvas.height = 480;
  const ctx = canvas.getContext("2d");
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
  const imageBlob = canvas.toDataURL("image/jpeg");

  document.getElementById("loading").style.display = "block";
  document.getElementById("recommendations").innerHTML = "";
  document.getElementById("emotionHeader").innerText = "";

  fetch("../server/detect_emotion.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"  // <-- Important!
    },
    body: JSON.stringify({ image: imageBlob })
  })
  .then(async r => {
    const raw = await r.text();
    console.log("🔍 RAW RESPONSE FROM SERVER:", raw);

    try {
      const data = JSON.parse(raw);
      document.getElementById("loading").style.display = "none";

      if (data.error) {
        document.getElementById("emotionHeader").innerText = "😐 Couldn’t detect a face. Try again!";
        return;
      }

      document.getElementById("emotionHeader").innerText =
        `😄 You seem ${data.emotion.charAt(0).toUpperCase() + data.emotion.slice(1)}! We recommend:`;

      data.movies.forEach(movie => {
        const card = document.createElement("div");
        card.className = "col";
        card.innerHTML = `
          <div class="card h-100 bg-dark text-light shadow-sm">
            <img src="${movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/300x450?text=No+Image'}"
                class="card-img-top" alt="${movie.Title}">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">${movie.Title}</h5>
              <p class="small text-muted mb-1">
                ${movie.Year} · 
                <span class="badge bg-warning text-dark">⭐ ${movie.imdbRating || 'N/A'}</span>
              </p>
              <p class="card-text flex-grow-1">${movie.Plot ? movie.Plot.substring(0, 100) + "..." : "No plot available."}</p>
              <button class="btn btn-outline-warning btn-sm mb-2" onclick='addToWatchlist("${movie.imdbID}", "${movie.Title}", "${movie.Poster}")'>
                <i class="bi bi-bookmark-plus-fill"></i> Add to Watchlist
              </button>
              <button class="btn btn-outline-success btn-sm" onclick='markAsWatched("${movie.imdbID}", "${movie.Title}", "${movie.Poster}")'>
                <i class="bi bi-eye-fill"></i> Mark as Watched
              </button>
            </div>
          </div>`;
        document.getElementById("recommendations").appendChild(card);
      });

    } catch (err) {
      console.error("❌ JSON Parse Error:", err);
      document.getElementById("loading").style.display = "none";
      document.getElementById("emotionHeader").innerText = "⚠️ Server error. Invalid JSON. Check console.";
    }
  })
  .catch(err => {
    console.error("❌ Fetch error:", err);
    document.getElementById("loading").style.display = "none";
    document.getElementById("emotionHeader").innerText = "⚠️ Could not contact server.";
  });
};

function addToWatchlist(imdbID, title, poster) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", title);
  formData.append("poster", poster);

  fetch("../server/add-to-watchlist.php", {
    method: "POST",
    body: formData
  }).then(r => r.text()).then(res => {
    alert(res.includes("success") ? "✅ Added to Watchlist!" : res);
  });
}

function markAsWatched(imdbID, title, poster) {
  const formData = new FormData();
  formData.append("imdb_id", imdbID);
  formData.append("title", title);
  formData.append("poster", poster);

  fetch("../server/mark-as-watched.php", {
    method: "POST",
    body: formData
  }).then(r => r.text()).then(res => {
    alert(res.includes("success") ? "✅ Marked as Watched!" : res);
  });
}
