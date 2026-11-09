import sys
import json
import face_recognition

def extract(image_path):
    try:

        image = face_recognition.load_image_file(image_path)
        

        encodings = face_recognition.face_encodings(image)
        
        if len(encodings) == 0:
            print(json.dumps({"success": False, "error": "No face found"}))
        else:

            print(json.dumps({"success": True, "descriptor": encodings[0].tolist()}))
            
    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "Falta la ruta de la imagen"}))
        sys.exit(1)
        
    extract(sys.argv[1])