from flask import Flask, request, jsonify
from transformers import pipeline

app = Flask(__name__)
pipe = pipeline("text2text-generation", model="bluenguyen/movie_chatbot_large_v1")

@app.route("/chatbot", methods=["POST"])
def chat():
    data = request.get_json()
    user_input = data.get("message", "")
    if not user_input:
        return jsonify({"reply": "Please enter something!"})
    
    result = pipe(user_input, max_length=200, do_sample=True)[0]['generated_text']
    return jsonify({"reply": result})

if __name__ == "__main__":
    app.run(port=5005)
