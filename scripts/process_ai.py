import sys
import json
import urllib.request
import numpy as np
import cv2
import face_recognition
import pytesseract
import re

def process_image(image_url, do_faces, do_ocr):
    result = {
        "success": True,
        "faces": {"count": 0, "descriptors": []},
        "bibs": []
    }

    try:

        req = urllib.request.urlopen(image_url)
        arr = np.asarray(bytearray(req.read()), dtype=np.uint8)
        img_cv = cv2.imdecode(arr, -1)  
        
        if img_cv is None:
            return {"success": False, "error": "No se pudo decodificar la imagen de la URL"}


        img_rgb = cv2.cvtColor(img_cv, cv2.COLOR_BGR2RGB)


        if do_faces:
            encodings = face_recognition.face_encodings(img_rgb)
            result["faces"]["count"] = len(encodings)
            result["faces"]["descriptors"] = [enc.tolist() for enc in encodings]


        if do_ocr:

            gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
            adjusted = cv2.convertScaleAbs(gray, alpha=1.5, beta=0)
            bfilter = cv2.bilateralFilter(adjusted, 11, 17, 17)
            thresh = cv2.adaptiveThreshold(bfilter, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2)


            text = pytesseract.image_to_string(thresh, config=r'--oem 3 --psm 11')


            raw_matches = re.findall(r'\b\d{1,5}\b', text)
            valid_numbers = [num for num in raw_matches if num not in ['0', '2025', '2026', '2027']]
            

            result["bibs"] = list(set(valid_numbers))

        return result

    except Exception as e:
        return {"success": False, "error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print(json.dumps({"success": False, "error": "Faltan argumentos: URL, flag_faces, flag_ocr"}))
        sys.exit(1)
        
    image_url = sys.argv[1]
    do_faces = sys.argv[2] == '1'
    do_ocr = sys.argv[3] == '1'
    
    print(json.dumps(process_image(image_url, do_faces, do_ocr)))