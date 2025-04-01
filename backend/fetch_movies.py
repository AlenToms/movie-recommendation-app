import requests
import os

def fetch_movies(title):
    api_key = os.getenv('OMDB_API_KEY')  # Load API key from environment variable
    url = f"http://www.omdbapi.com/?s={title}&apikey={api_key}"
    response = requests.get(url)
    
    if response.status_code == 200:
        data = response.json()
        return data.get("Search", [])
    else:
        return []

if __name__ == "__main__":
    movie_title = input("Enter movie title: ")
    movies = fetch_movies(movie_title)
    for movie in movies:
        print(f"{movie['Title']} ({movie['Year']})")
