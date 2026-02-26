#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Sentiment Prediction Script
Predicts sentiment using trained SVM model
"""

import sys
import os
import json
import pickle
import pandas as pd
import numpy as np
import re
import argparse
import warnings
warnings.filterwarnings('ignore')

# Force UTF-8 encoding on Windows
if sys.platform == 'win32':
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Import required packages
try:
    from sklearn.feature_extraction.text import TfidfVectorizer
    import nltk
    from nltk.tokenize import word_tokenize
    from nltk.corpus import stopwords
    from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
except ImportError as e:
    print(json.dumps({'success': False, 'error': f'Required packages not installed: {str(e)}'}), flush=True)
    sys.exit(1)

# Download NLTK data
try:
    nltk.data.find('tokenizers/punkt')
except LookupError:
    nltk.download('punkt', quiet=True)

try:
    nltk.data.find('corpora/stopwords')
except LookupError:
    nltk.download('stopwords', quiet=True)

# Initialize Indonesian stemmer
stemmer = StemmerFactory().create_stemmer()

def preprocess_text(text):
    """Preprocess text: lowercase, remove special chars, tokenize, remove stopwords, stem"""
    # Lowercase
    text = text.lower()
    
    # Remove URLs
    text = re.sub(r'http\S+|www\S+', '', text)
    
    # Remove email
    text = re.sub(r'\S+@\S+', '', text)
    
    # Remove special characters (keep only alphanumeric and spaces)
    text = re.sub(r'[^a-zA-Z0-9\s]', '', text)
    
    # Tokenize
    tokens = word_tokenize(text)
    
    # Remove stopwords
    stop_words = set(stopwords.words('indonesian'))
    tokens = [t for t in tokens if t not in stop_words]
    
    # Stem
    tokens = [stemmer.stem(t) for t in tokens]
    
    # Join back
    processed_text = ' '.join(tokens)
    
    return processed_text

def predict_sentiment(text, model_path='storage/app/private/svm_model.pkl', tfidf_path='storage/app/private/tfidf_vectorizer.pkl'):
    """
    Predict sentiment for given text
    """
    try:
        # Check if model files exist
        if not os.path.exists(model_path):
            return {'success': False, 'error': f'Model file not found: {model_path}'}
        
        if not os.path.exists(tfidf_path):
            return {'success': False, 'error': f'TF-IDF vectorizer not found: {tfidf_path}'}
        
        # Load model and vectorizer
        with open(model_path, 'rb') as f:
            model = pickle.load(f)
        
        with open(tfidf_path, 'rb') as f:
            tfidf_vectorizer = pickle.load(f)
        
        # Preprocess input text
        processed_text = preprocess_text(text)
        
        if not processed_text.strip():
            return {'success': False, 'error': 'Text terlalu pendek atau hanya berisi stopwords'}
        
        # Vectorize
        text_tfidf = tfidf_vectorizer.transform([processed_text])
        
        # Predict
        prediction = model.predict(text_tfidf)[0]
        
        # Get probability/confidence
        if hasattr(model, 'decision_function'):
            decision = model.decision_function(text_tfidf)[0]
            # Normalize decision function to probability-like score
            probabilities = np.abs(decision) / np.sum(np.abs(decision))
        else:
            # Fallback to probability estimates
            probabilities = [1.0 / len(model.classes_)] * len(model.classes_)
        
        # Get class labels
        classes = model.classes_.tolist()
        
        # Confidence is the max probability
        confidence = float(np.max(probabilities))
        
        # Convert prediction to string label if it's an integer
        prediction_label = str(prediction) if isinstance(prediction, (int, np.integer)) else prediction
        
        result = {
            'success': True,
            'prediction': prediction_label,
            'confidence': confidence,
            'probabilities': [float(p) for p in probabilities],
            'classes': classes,
            'text': text
        }
        
        return result
        
    except Exception as e:
        return {'success': False, 'error': str(e)}

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Predict sentiment using trained SVM model')
    parser.add_argument('--text', type=str, required=True, help='Text to predict')
    
    args = parser.parse_args()
    
    result = predict_sentiment(args.text)
    print(json.dumps(result, ensure_ascii=False, indent=2), flush=True)
