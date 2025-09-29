from flask import Flask, request, jsonify
import joblib
import pandas as pd
from scipy.sparse import hstack

app = Flask(__name__)

# Load trained model and vectorizer
model = joblib.load("severity_classifier.joblib")
vectorizer = joblib.load("severity_vectorizer.joblib")
cat_columns = joblib.load("severity_cat_columns.joblib")

# Define possible types and departments for one-hot encoding (fallback)
ALL_TYPES = ["fire", "healthcare", "vehicle_crash", "public_disturbance", "flood"]
ALL_DEPARTMENTS = ["fire", "medical", "police", "tanod"]


def encode_features(description, typ, department):
    # Vectorize description
    X_text = vectorizer.transform([description])
    # Build a single-row DataFrame and get_dummies with training-time columns
    df = pd.DataFrame([[typ, department]], columns=["type", "department"])
    X_cat = pd.get_dummies(df)
    # Reindex to match training columns, filling missing with 0
    X_cat = X_cat.reindex(columns=cat_columns, fill_value=0)
    # Convert to float to avoid scipy.sparse dtype error
    X = hstack([X_text, X_cat.values.astype(float)])
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
