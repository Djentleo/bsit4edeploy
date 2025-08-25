import joblib

# Load the trained model
model = joblib.load('incident_classifier.joblib')

# Test the model with sample data
sample_incidents = [
    "Fire alarm triggered in the basement",
    "Flooding reported in the parking lot",
    "Car crash on the highway",
    "Loud music disturbing the neighborhood",
    "Medical emergency at the mall"
]

# Predict the types of the sample incidents
predictions = model.predict(sample_incidents)

for incident, prediction in zip(sample_incidents, predictions):
    print(f"Incident: {incident} => Predicted Type: {prediction}")
