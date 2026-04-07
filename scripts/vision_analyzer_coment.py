import sys
import json
import warnings
import os
import numpy as np
from PIL import Image
from ultralytics import YOLO
from scipy.ndimage import laplace
import nltk

warnings.filterwarnings("ignore")
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

# Configuración de ruta para Windows
BASE_DIR = r'C:\Users\PC-4154\Desktop\mundo_yacus-main'
MODEL_PATH = os.path.join(BASE_DIR, 'database', 'ai', 'best.pt')

try:
    nltk.data.find('tokenizers/punkt')
    nltk.data.find('tokenizers/punkt_tab')
except LookupError:
    nltk.download('punkt', quiet=True)
    nltk.download('punkt_tab', quiet=True)

def analyze_cuy_full(image_path, user_comment=""):
    try:
        # FASE 1: Nitidez con SciPy
        img = Image.open(image_path).convert('L')
        img_array = np.array(img)
        nitidez = laplace(img_array).var()
        
        if nitidez < 50:
            return json.dumps({
                "status": "error", 
                "message": f"La foto está muy borrosa (Nitidez: {nitidez:.2f}).",
                "nitidez": nitidez
            })

        # FASE 2: Detección YOLOv8
        if not os.path.exists(MODEL_PATH):
            return json.dumps({"status": "error", "message": f"Modelo no encontrado en: {MODEL_PATH}"})

        model = YOLO(MODEL_PATH)
        results = model(image_path, verbose=False)
        
        found_cuy = False
        best_box = None
        conf_value = 0
        raza_detectada = "Desconocida"

        for r in results:
            if len(r.boxes) > 0:
                found_cuy = True
                top_box = r.boxes[0]
                conf_value = float(top_box.conf[0]) * 100
                best_box = top_box.xyxy[0].tolist()
                raza_detectada = r.names[int(top_box.cls[0])]
                break

        # FASE 3: Síntomas con NLTK
        analisis_salud = "No se proporcionaron comentarios."
        if user_comment:
            tokens = nltk.word_tokenize(user_comment.lower())
            diccionario_sintomas = {
                'diarrea': 'URGENTE: Problema digestivo.',
                'estornudo': 'ALERTA: Posible infección respiratoria.',
                'decaido': 'AVISO: Baja energía.',
                'herida': 'AVISO: Lesión física.',
                'hinchado': 'AVISO: Posible timpanismo.',
                'moquillo': 'ALERTA: Infección respiratoria.',
            }
            hallazgos = [diccionario_sintomas[t] for t in tokens if t in diccionario_sintomas]
            analisis_salud = " ".join(hallazgos) if hallazgos else "Sin síntomas evidentes."

        if found_cuy and conf_value >= 45.0:
            return json.dumps({
                "status": "success",
                "raza_detectada": raza_detectada,
                "confianza": f"{conf_value:.2f}%",
                "coordenadas": best_box,
                "nitidez": f"{nitidez:.2f}",
                "analisis_salud": analisis_salud,
                "sugerencia": f"¡Cuy {raza_detectada} detectado!"
            })
        else:
            return json.dumps({"status": "error", "raza_detectada": "No detectado"})

    except Exception as e:
        return json.dumps({"status": "error", "message": str(e)})

if __name__ == "__main__":
    img_param = sys.argv[1] if len(sys.argv) > 1 else ""
    comment_param = sys.argv[2] if len(sys.argv) > 2 else ""
    print(analyze_cuy_full(img_param, comment_param))