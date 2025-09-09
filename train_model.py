import pandas as pd
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.model_selection import train_test_split
from sklearn.naive_bayes import MultinomialNB
from sklearn.pipeline import Pipeline
import joblib

# Load the dataset
data = pd.read_csv('incidents.csv')


# Features and labels for severity prediction
X = data['incident_description']
y = data['severity']

# Split the dataset into training and testing sets
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Create a pipeline with a CountVectorizer and a MultinomialNB classifier
pipeline = Pipeline([
    ('vectorizer', CountVectorizer()),
    ('classifier', MultinomialNB())
])

# Train the model
pipeline.fit(X_train, y_train)

# Save the trained model for severity prediction
joblib.dump(pipeline, 'severity_classifier.joblib')

print("Model trained and saved as 'severity_classifier.joblib'")
