# hugging_chat.py
import sys
from transformers import pipeline

query = sys.argv[1]
pipe = pipeline("text2text-generation", model="bluenguyen/movie_chatbot_large_v1")

response = pipe(query, max_new_tokens=150)[0]['generated_text']
print(response)
