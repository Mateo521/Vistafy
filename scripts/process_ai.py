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
            return {"success": False, "error": "No se decodificó la imagen"}

        img_rgb = cv2.cvtColor(img_cv, cv2.COLOR_BGR2RGB)

        if do_faces:
            encodings = face_recognition.face_encodings(img_rgb)
            result["faces"]["count"] = len(encodings)
            result["faces"]["descriptors"] = [enc.tolist() for enc in encodings]

        if do_ocr:
            
            b, g, r_channel = cv2.split(img_cv)
            
        
            enlarged = cv2.resize(r_channel, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
            
     
            blur = cv2.GaussianBlur(enlarged, (5, 5), 0)
            _, thresh = cv2.threshold(blur, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)


            custom_config = r'--oem 3 --psm 11 -c tessedit_char_whitelist=0123456789'
            data = pytesseract.image_to_data(thresh, output_type=pytesseract.Output.DICT, config=custom_config)

            valid_numbers = []
            

            for i in range(len(data['text'])):
                confianza = int(data['conf'][i])
                texto = data['text'][i].strip()
                

                if confianza > 70 and re.fullmatch(r'\d{2,5}', texto):

                    if texto not in ['2025', '2026', '2027', '00']:
                        valid_numbers.append(texto)

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