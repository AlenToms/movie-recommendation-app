import numpy as np
import joblib

# We'll train a basic model separately or load a pre-trained one.
# For now, use this dummy version for proof of concept.

class SimpleEmotionClassifier:
    def predict(self, X):
        return ["happy"]  # Always return "happy" as test

# Replace this with joblib.load('model.pkl') if you train your own model.
model = SimpleEmotionClassifier()

def predict_emotion(landmarks):
    flat = np.array(landmarks).flatten().reshape(1, -1)
    return model.predict(flat)[0]
