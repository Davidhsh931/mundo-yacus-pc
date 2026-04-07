import sys
import json
import os
from ultralytics import YOLO

def analyze():
    # 1. Obtener la ruta de la imagen
    img_path = sys.argv[1] if len(sys.argv) > 1 else "cerdo_claro.jpg"
    
    # --- CONFIGURACIÓN DE RUTAS ABSOLUTAS ---
    # Esto busca la carpeta raíz del proyecto (mundo_yacus-pc)
    BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    
    # Ruta absoluta para el modelo general (yolov8n.pt debe estar en la raíz)
    MODELO_GRANJA = os.path.join(BASE_DIR, 'yolov8n.pt')
    
    # Ruta absoluta para el modelo de cuyes
    MODELO_CUY = os.path.join(BASE_DIR, 'database', 'ai', 'best.pt')
    
    try:
        # --- PASO 1: CARGAR MODELO GENERAL ---
        if not os.path.exists(MODELO_GRANJA):
            return json.dumps({"status": "error", "message": f"No se encuentra el modelo: {MODELO_GRANJA}"})
            
        model_g = YOLO(MODELO_GRANJA)
        results_g = model_g(img_path, conf=0.20, verbose=False)
        
        granja_dict = {
            'pig': 'Cerdo',
            'dog': 'Cerdo',    # El truco para el modelo Nano
            'chicken': 'Gallina',
            'sheep': 'Oveja'
        }

        for r in results_g:
            if len(r.boxes) > 0:
                boxes = sorted(r.boxes, key=lambda x: x.conf[0], reverse=True)
                for box in boxes:
                    label = r.names[int(box.cls[0])].lower()
                    conf = float(box.conf[0]) * 100
                    
                    if label in granja_dict:
                        return json.dumps({
                            "status": "success",
                            "raza_detectada": granja_dict[label],
                            "confianza": f"{conf:.2f}%",
                            "tipo": "Granja"
                        })

        # --- PASO 2: SI NO HAY GRANJA, BUSCAR CUY ---
        if os.path.exists(MODELO_CUY):
            model_c = YOLO(MODELO_CUY)
            res_c = model_c(img_path, conf=0.30, verbose=False)
            for r in res_c:
                if len(r.boxes) > 0:
                    raza = r.names[int(r.boxes[0].cls[0])]
                    conf = float(r.boxes[0].conf[0]) * 100
                    return json.dumps({
                        "status": "success",
                        "raza_detectada": f"Cuy {raza}",
                        "confianza": f"{conf:.2f}%",
                        "tipo": "Cuy"
                    })

        return json.dumps({"status": "error", "message": "Animal no identificado"})

    except Exception as e:
        return json.dumps({"status": "error", "message": str(e)})

if __name__ == "__main__":
    # Importante: Solo imprimimos el resultado final
    print(analyze())