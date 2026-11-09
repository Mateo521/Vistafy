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
            return {"success": False, "error": "No se pudo decodificar la imagen"}

        img_rgb = cv2.cvtColor(img_cv, cv2.COLOR_BGR2RGB)

        if do_faces:
            encodings = face_recognition.face_encodings(img_rgb)
            result["faces"]["count"] = len(encodings)
            result["faces"]["descriptors"] = [enc.tolist() for enc in encodings]

        if do_ocr:
            # 1. Escala de grises y AUMENTO de tamaño
            gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
            gray = cv2.resize(gray, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
            
            # 2. Suavizado
            blur = cv2.GaussianBlur(gray, (5, 5), 0)
            
            # 3. Umbral de Otsu
            _, thresh = cv2.threshold(blur, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)

            # 4. CONFIGURACIÓN ESTRICTA: PSM 6 (Bloque uniforme) + Whitelist
            custom_config = r'--oem 3 --psm 6 -c tessedit_char_whitelist=0123456789'
            text = pytesseract.image_to_string(thresh, config=custom_config)

            # 5. Regex: Permitimos de 1 a 5 dígitos, pero PSM 6 evitará que se rompan los largos
            raw_matches = re.findall(r'\b\d{1,5}\b', text)
            
            # Filtro de basura
            ignore_list = ['0', '2025', '2026', '2027', '00']
            valid_numbers = [num for num in raw_matches if num not in ignore_list]
            
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