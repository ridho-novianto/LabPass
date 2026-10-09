import os
import json
import cv2
from ultralytics import YOLO

# Pemetaan class names dari dataset Roboflow ke checklist APD Laboratorium
CLASS_MAPPING = {
    "lab_coat": "Jas Lab",
    "latex_gloves": "Sarung Tangan",
    "work_gloves": "Sarung Tangan",
    "safety_glasses": "Kacamata Pelindung",
    "glasses": "Kacamata Pelindung",
    "mask": "Masker"
}

REQUIRED_ITEMS = [
    "Jas Lab",
    "Sarung Tangan",
    "Kacamata Pelindung",
    "Masker"
]

class PPEEvaluator:
    def __init__(self, model_path=None):
        base_dir = os.path.dirname(os.path.abspath(__file__))
        
        if model_path is None:
            # Candidate paths
            candidates = [
                os.path.join(base_dir, "weights", "best.pt"),
                os.path.join(base_dir, "best.pt"),
                os.path.join(base_dir, "runs", "detect", "apd_model", "weights", "best.pt"),
            ]
            for c in candidates:
                if os.path.exists(c):
                    model_path = c
                    break
            if model_path is None:
                model_path = candidates[0]
        elif not os.path.isabs(model_path):
            model_path = os.path.join(base_dir, model_path)
            
        if not os.path.exists(model_path):
            raise FileNotFoundError(f"Model weight tidak ditemukan pada: {model_path}. Harap periksa lokasi file best.pt.")
        
        self.model = YOLO(model_path)
        self.model_class_names = self.model.names

    def evaluate_ppe(self, image_input, output_path=None, conf=0.25):
        """
        image_input: path file gambar (str) atau array gambar OpenCV (numpy.ndarray)
        output_path: jika diisi, menyimpan gambar hasil deteksi (annotated frame)
        conf: threshold confidence deteksi (default 0.25)
        
        Mengembalikan dict hasil evaluasi kepatuhan APD
        """
        results = self.model(image_input, conf=conf)[0]
        detected_items = set()

        # Iterasi setiap bounding box deteksi
        for box in results.boxes:
            class_id = int(box.cls[0])
            raw_class_name = self.model_class_names[class_id].lower().replace(" ", "_")
            
            # Map ke kategori checklist
            if raw_class_name in CLASS_MAPPING:
                detected_items.add(CLASS_MAPPING[raw_class_name])

        checklist = []
        is_compliant = True

        for item in REQUIRED_ITEMS:
            detected = item in detected_items
            checklist.append({
                "item": item,
                "detected": detected
            })
            if not detected:
                is_compliant = False

        # Visualisasi jika output_path diberikan
        annotated_frame = None
        if output_path is not None:
            annotated_frame = results.plot()
            cv2.imwrite(output_path, annotated_frame)

        result_payload = {
            "is_compliant": is_compliant,
            "checklist": checklist
        }

        return result_payload

    def evaluate_ppe_json(self, image_input, output_path=None, conf=0.25):
        """Versi helper yang langsung mengembalikan format JSON string berindentasi"""
        result = self.evaluate_ppe(image_input, output_path=output_path, conf=conf)
        return json.dumps(result, indent=2, ensure_ascii=False)


if __name__ == "__main__":
    import sys
    if len(sys.argv) > 1:
        img_path = sys.argv[1]
        out_path = sys.argv[2] if len(sys.argv) > 2 else "output_annotated.jpg"
        evaluator = PPEEvaluator()
        json_output = evaluator.evaluate_ppe_json(img_path, output_path=out_path)
        print("--- Hasil Evaluasi Kepatuhan APD ---")
        print(json_output)
        if out_path:
            print(f"Gambar dengan anotasi disimpan ke: {out_path}")
    else:
        print("Penggunaan: python inference_checklist.py <path_gambar> [path_output_gambar]")
