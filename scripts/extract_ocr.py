import sys
import json
import cv2
import pytesseract
import re

def process_ocr(image_path):
    try:
        # 1. Cargar la imagen
        img = cv2.imread(image_path)
        if img is None:
            return {"success": False, "error": "No se pudo leer la imagen"}


        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        

        alpha = 1.5 # Contraste
        beta = 0    # Brillo
        adjusted = cv2.convertScaleAbs(gray, alpha=alpha, beta=beta)
        

        bfilter = cv2.bilateralFilter(adjusted, 11, 17, 17)
        

        thresh = cv2.adaptiveThreshold(bfilter, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2)


        custom_config = r'--oem 3 --psm 11'
        text = pytesseract.image_to_string(thresh, config=custom_config)


        raw_matches = re.findall(r'\b\d{1,5}\b', text)
        

        valid_numbers = []
        for num in raw_matches:

            if num not in ['0', '2025', '2026']:
                valid_numbers.append(num)


        unique_numbers = list(set(valid_numbers))

        return {
            "success": True, 
            "numbers": unique_numbers
        }

    except Exception as e:
        return {"success": False, "error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "Falta la ruta de la imagen"}))
        sys.exit(1)
        
    print(json.dumps(process_ocr(sys.argv[1])))