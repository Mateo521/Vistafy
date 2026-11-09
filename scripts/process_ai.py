import sys
import json
import urllib.request
import numpy as np
import cv2
import face_recognition
import easyocr
import re
import warnings
import os


warnings.filterwarnings("ignore")
os.environ["W_DISABLE_NNPACK"] = "1"


os.environ["OMP_NUM_THREADS"] = "1"
os.environ["OPENBLAS_NUM_THREADS"] = "1"
os.environ["MKL_NUM_THREADS"] = "1"
os.environ["VECLIB_MAXIMUM_THREADS"] = "1"
os.environ["NUMEXPR_NUM_THREADS"] = "1"
import torch
torch.set_num_threads(1)


reader = easyocr.Reader(['en'], gpu=False, verbose=False)


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
            return {"success": False, "error": "No se decodificó la imagen"}


        max_width = 1200
        h, w = img_cv.shape[:2]
        if w > max_width:
            factor = max_width / float(w)
            img_cv = cv2.resize(img_cv, (max_width, int(h * factor)), interpolation=cv2.INTER_AREA)

        img_rgb = cv2.cvtColor(img_cv, cv2.COLOR_BGR2RGB)

        if do_faces:
            encodings = face_recognition.face_encodings(img_rgb)
            result["faces"]["count"] = len(encodings)
            result["faces"]["descriptors"] = [enc.tolist() for enc in encodings]

        if do_ocr:

            ocr_results = reader.readtext(img_rgb)
            
            valid_numbers = []
            
            for (bbox, text, prob) in ocr_results:

                cleaned_text = re.sub(r'\D', '', text)
                

                if prob > 0.45 and 2 <= len(cleaned_text) <= 5:
                    if cleaned_text not in ['2025', '2026', '2027', '00', '50']:
                        valid_numbers.append(cleaned_text)

            result["bibs"] = list(set(valid_numbers))

        return result

    except Exception as e:
        return {"success": False, "error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print(json.dumps({"success": False, "error": "Faltan argumentos"}))
        sys.exit(1)
        
    image_url = sys.argv[1]
    do_faces = sys.argv[2] == '1'
    do_ocr = sys.argv[3] == '1'
    
    print(json.dumps(process_image(image_url, do_faces, do_ocr)))