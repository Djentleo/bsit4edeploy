from flask import Flask, request, jsonify
import joblib
import pandas as pd
from scipy.sparse import hstack

app = Flask(__name__)

# Load trained model and vectorizer
model = joblib.load("severity_classifier.joblib")
vectorizer = joblib.load("severity_vectorizer.joblib")

# Define possible types and departments for one-hot encoding
ALL_TYPES = ["fire", "healthcare", "vehicle_crash", "public_disturbance", "flood"]
ALL_DEPARTMENTS = ["fire", "medical", "police", "tanod"]


def encode_features(description, typ, department):
    # Vectorize description
    X_text = vectorizer.transform([description])
    # One-hot encode type and department
    type_vec = [1 if typ == t else 0 for t in ALL_TYPES]
    dept_vec = [1 if department == d else 0 for d in ALL_DEPARTMENTS]
    # Combine all features
    import numpy as np

    X_cat = np.array(type_vec + dept_vec).reshape(1, -1)
    X = hstack([X_text, X_cat])
    return X


@app.route("/predict-severity", methods=["POST"])
def predict_severity():
    data = request.get_json()
    description = data.get("description", "")
    typ = data.get("type", "fire")  # default to 'fire' if not provided
    department = data.get("department", "fire")  # default to 'fire' if not provided
    # Prepare features
    X = encode_features(description, typ, department)
    # Predict severity
    severity = model.predict(X)[0]
    return jsonify({"severity": severity})


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
