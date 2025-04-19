<?php
session_start();
if (!isset($_SESSION['email'])) {
  header('Location: login.html');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Movie Chatbot</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #0f0f0f;
      color: white;
      font-family: 'Segoe UI', sans-serif;
    }

    .chat-container {
      max-width: 700px;
      margin: 50px auto;
      background: #1c1c1e;
      border-radius: 20px;
      padding: 20px;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
    }

    #chatBox {
      height: 60vh;
      overflow-y: auto;
      padding-right: 10px;
      margin-bottom: 1rem;
      scrollbar-width: none; /* Firefox */
    }

    #chatBox::-webkit-scrollbar {
      display: none; /* Chrome, Safari */
    }

    .message {
      margin: 8px 0;
    }

    .bubble {
      display: inline-block;
      padding: 10px 16px;
      border-radius: 20px;
      max-width: 80%;
      word-wrap: break-word;
      font-size: 15px;
      line-height: 1.5;
    }

    .user .bubble {
      background-color: #0d6efd;
      color: white;
      margin-left: auto;
      text-align: right;
    }

    .bot .bubble {
      background-color: #343a40;
      color: white;
      margin-right: auto;
    }

    .input-group {
      background: #2c2c2c;
      border-radius: 40px;
      padding: 5px 10px;
      overflow: hidden;
    }

    .input-group input {
      background: transparent;
      border: none;
      color: white;
      padding: 10px 15px;
      width: 100%;
    }

    .input-group input:focus {
      outline: none;
      box-shadow: none;
    }

    .input-group button {
      border: none;
      background: #ffc107;
      color: black;
      font-weight: bold;
      border-radius: 40px;
      padding: 8px 20px;
      transition: 0.3s;
    }

    .input-group button:hover {
      background-color: #ffcd39;
    }

    .title {
      text-align: center;
      margin-bottom: 20px;
      color: #ffc107;
    }
  </style>
</head>
<body>

<div class="chat-container">
  <h3 class="title">🎬 Movie Chatbot</h3>
  <div id="chatBox"></div>
  <div class="input-group">
    <input type="text" id="userInput" placeholder="Ask me anything about movies...">
    <button onclick="sendMessage()">Send</button>
  </div>
</div>

<script>
function appendMessage(role, text) {
  const chatBox = document.getElementById("chatBox");
  const msg = document.createElement("div");
  msg.className = `message ${role}`;
  msg.innerHTML = `<div class="bubble">${text}</div>`;
  chatBox.appendChild(msg);
  chatBox.scrollTop = chatBox.scrollHeight;
}

function sendMessage() {
  const input = document.getElementById("userInput");
  const text = input.value.trim();
  if (!text) return;
  appendMessage("user", text);
  input.value = "";
  appendMessage("bot", "…thinking…");

  fetch("../server/hugging_chat.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ message: text })
  })
  .then(res => res.json())
  .then(data => {
    document.querySelectorAll(".message.bot .bubble")[document.querySelectorAll(".message.bot .bubble").length - 1].remove();
    appendMessage("bot", data.reply || "Sorry, I couldn’t find an answer.");
  })
  .catch(err => {
    console.error(err);
    appendMessage("bot", "❌ Error contacting server.");
  });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
