import sys
import json
import face_recognition
import pytesseract
from PIL import Image
import numpy as np
import urllib.request

def process_image(image_url, do_faces, do_bibs):
    result = {
        "success": True,
        "faces": {"count": 0, "descriptors": []},
        "bibs": []
    }

    try:

        req = urllib.request.urlopen(image_url)
        img_array = np.asarray(bytearray(req.read()), dtype=np.uint8)
        
        import cv2
        image_cv = cv2.imdecode(img_array, cv2.IMREAD_COLOR)

        image_rgb = cv2.cvtColor(image_cv, cv2.COLOR_BGR2RGB)


        if do_faces.lower() == 'true' or do_faces == '1':

            face_locations = face_recognition.face_locations(image_rgb)
            if face_locations:

                face_encodings = face_recognition.face_encodings(image_rgb, face_locations)
                result["faces"]["count"] = len(face_encodings)

                result["faces"]["descriptors"] = [enc.tolist() for enc in face_encodings]


        if do_bibs.lower() == 'true' or do_bibs == '1':

            gray = cv2.cvtColor(image_cv, cv2.COLOR_BGR2GRAY)

            custom_config = r'--oem 3 --psm 11 -c tessedit_char_whitelist=0123456789'
            text = pytesseract.image_to_string(gray, config=custom_config)
            

            import re
            numbers = re.findall(r'\d{2,}', text)

            result["bibs"] = list(set(numbers))

    except Exception as e:
        result["success"] = False
        result["error"] = str(e)


    print(json.dumps(result))

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print(json.dumps({"success": False, "error": "Faltan argumentos"}))
        sys.exit(1)

    url = sys.argv[1]
    process_faces = sys.argv[2]
    process_bibs = sys.argv[3]

    process_image(url, process_faces, process_bibs)