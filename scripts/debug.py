import sys
import urllib.request
import numpy as np
import cv2
import easyocr

if len(sys.argv) < 2:
    print("Falta la URL de la imagen.")
    sys.exit()

url = sys.argv[1]
print(f"Descargando imagen desde {url}...")


req = urllib.request.urlopen(url)
arr = np.asarray(bytearray(req.read()), dtype=np.uint8)
img = cv2.imdecode(arr, -1)

print("Ejecutando EasyOCR (esto puede tardar unos segundos)...")

reader = easyocr.Reader(['en'], gpu=False, verbose=False)
results = reader.readtext(img)



for (bbox, text, prob) in results:
    print(f"Detectado: '{text}' | Confianza: {prob:.2f}")
    

    (tl, tr, br, bl) = bbox
    tl = (int(tl[0]), int(tl[1]))
    br = (int(br[0]), int(br[1]))
    

    cv2.rectangle(img, tl, br, (0, 0, 255), 2)
    cv2.putText(img, f"{text} ({prob:.2f})", (tl[0], tl[1] - 10), 
                cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 0, 255), 2)


cv2.imwrite("resultado_debug.jpg", img)
print("\n¡Listo! Descarga y abre el archivo 'resultado_debug.jpg' para ver qué vio la IA.")