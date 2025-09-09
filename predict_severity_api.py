from flask import Flask, request, jsonify
import joblib

app = Flask(__name__)

# Load your trained severity model
model = joblib.load('severity_classifier.joblib')

@app.route('/predict-severity', methods=['POST'])
def predict_severity():
    data = request.get_json()
    description = data.get('description', '')
    # Predict severity using the trained model
    severity = model.predict([description])[0]
    return jsonify({'severity': severity})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
