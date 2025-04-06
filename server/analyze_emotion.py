import sys
import cv2
from fer import FER

image_path = sys.argv[1]
img = cv2.imread(image_path)

if img is None:
    print("no_face")
    sys.exit()

# Use mtcnn=False to avoid moviepy dependency
detector = FER(mtcnn=False)
emotion, score = detector.top_emotion(img)

if emotion is None:
    print("no_face")
else:
    print(emotion)
