#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Sentiment Prediction Script - Sesuai Google Colab
Menggunakan TF-IDF dan SVM Model yang sudah ditraining
"""

import sys
import os
import json
import argparse
import pickle
from datetime import datetime
import re

# Force UTF-8 encoding on Windows
if sys.platform == 'win32':
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

# Add current directory to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

try:
    import numpy as np
    import nltk
    from nltk.tokenize import word_tokenize
    from nltk.corpus import stopwords
    from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
except ImportError as e:
    print(json.dumps({
        'success': False,
        'error': f'Required packages not installed: {str(e)}'
    }))
    sys.exit(1)

# Download required NLTK data
try:
    nltk.data.find('tokenizers/punkt')
except LookupError:
    nltk.download('punkt', quiet=True)

try:
    nltk.data.find('corpora/stopwords')
except LookupError:
    nltk.download('stopwords', quiet=True)


def load_model_and_vectorizer():
    """Load trained SVM model, TF-IDF vectorizer, and kamus"""
    model_dir = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private')
    
    model_path = os.path.join(model_dir, 'svm_model.pkl')
    vectorizer_path = os.path.join(model_dir, 'tfidf_vectorizer.pkl')
    kamus_path = os.path.join(model_dir, 'kamus_normalisasi.pkl')
    
    if not os.path.exists(model_path) or not os.path.exists(vectorizer_path):
        return None, None, {}
    
    try:
        with open(model_path, 'rb') as f:
            model = pickle.load(f)
        
        with open(vectorizer_path, 'rb') as f:
            vectorizer = pickle.load(f)
        
        kamus = {}
        if os.path.exists(kamus_path):
            with open(kamus_path, 'rb') as f:
                kamus = pickle.load(f)
        
        return model, vectorizer, kamus
    except Exception as e:
        return None, None, {}


def case_folding(text):
    """Step 1: Case Folding"""
    if not isinstance(text, str):
        return ""
    return text.lower()


def clean_text(text):
    """Step 2: Cleaning"""
    if not isinstance(text, str):
        return ""
    
    text = re.sub(r'http\S+|www\S+|https\S+', '', text)
    text = re.sub(r'@\w+|#\w+', '', text)
    text = re.sub(r'\d+', '', text)
    text = re.sub(r'[^a-zA-Z\s]', '', text)
    text = re.sub(r'\s+', ' ', text).strip()
    
    return text


def tokenize_text(text):
    """Step 3: Tokenization"""
    if not isinstance(text, str) or text == "":
        return []
    return word_tokenize(text)


def normalisasi_kata(tokens, kamus):
    """Step 4: Normalisasi Kata"""
    normalized_tokens = []
    for token in tokens:
        token_lower = token.lower()
        if token_lower in kamus:
            normalized_tokens.extend(kamus[token_lower].split())
        else:
            normalized_tokens.append(token_lower)
    return normalized_tokens


def remove_stopwords(tokens):
    """Step 5: Stopword Removal"""
    try:
        stopwords_indonesia = set(stopwords.words('indonesian'))
    except:
        stopwords_indonesia = {'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'pada', 'adalah'}
    
    return [token for token in tokens if token not in stopwords_indonesia]


def stemming_tokens(tokens):
    """Step 6: Stemming"""
    factory = StemmerFactory()
    stemmer = factory.create_stemmer()
    
    stemmed = [stemmer.stem(token) for token in tokens]
    return " ".join(stemmed)


def preprocess_input(text, kamus):
    """
    Preprocess input text - SESUAI COLAB
    Mengikuti pipeline yang sama dengan training
    """
    # Step 1: Case Folding
    text = case_folding(text)
    
    # Step 2: Cleaning
    text = clean_text(text)
    if not text.strip():
        return ""
    
    # Step 3: Tokenization
    tokens = tokenize_text(text)
    if not tokens:
        return ""
    
    # Step 4: Normalisasi Kata
    tokens = normalisasi_kata(tokens, kamus)
    
    # Step 5: Stopword Removal
    tokens = remove_stopwords(tokens)
    if not tokens:
        return ""
    
    # Step 6: Stemming
    text_stemmed = stemming_tokens(tokens)
    
    return text_stemmed


def predict_sentiment(text):
    """
    Predict sentiment using trained SVM model with TF-IDF
    Implementasi sesuai Colab: preprocess_input + transform + predict
    """
    try:
        # Load model dan vectorizer
        model, vectorizer, kamus = load_model_and_vectorizer()
        
        if model is None or vectorizer is None:
            return {
                'success': False,
                'error': 'Model tidak ditemukan. Silakan train model terlebih dahulu.'
            }
        
        # Preprocess text dengan pipeline yang sama seperti training
        processed_text = preprocess_input(text, kamus)
        
        if not processed_text:
            return {
                'success': False,
                'error': 'Text kosong setelah preprocessing'
            }
        
        # Transform menggunakan TF-IDF vectorizer
        X = vectorizer.transform([processed_text])
        
        # Make prediction
        prediction = model.predict(X)[0]
        
        # Get probabilities untuk confidence
        probabilities = model.predict_proba(X)[0]
        confidence = float(np.max(probabilities))
        
        # Get class labels
        classes = sorted(model.classes_.tolist())
        
        return {
            'success': True,
            'text': text,
            'processed_text': processed_text,
            'sentiment': str(prediction),
            'confidence': float(confidence),
            'probabilities': {str(cls): float(prob) for cls, prob in zip(classes, probabilities)},
            'timestamp': datetime.now().isoformat()
        }
        
    except Exception as e:
        import traceback
        return {
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc()
        }


def predict_interactive():
    """
    Interactive mode - Masukkan teks dan dapatkan prediksi
    SESUAI COLAB: new_text = input("Masukkan teks baru: ")
    """
    model, vectorizer, kamus = load_model_and_vectorizer()
    
    if model is None or vectorizer is None:
        print("❌ Error: Model tidak ditemukan!")
        print("Silakan jalankan train_model.py terlebih dahulu.")
        return
    
    print("\n" + "=" * 70)
    print("SENTIMENT ANALYSIS PREDICTION SYSTEM")
    print("=" * 70)
    print("\nModel siap digunakan!")
    print(f"Classes: {sorted(model.classes_.tolist())}")
    print("\nKirim 'exit' atau 'quit' untuk keluar\n")
    
    while True:
        user_input = input("\n[INPUT] Masukkan teks: ").strip()
        
        if user_input.lower() in ['exit', 'quit', 'keluar']:
            print("\n✓ Terima kasih! Program selesai.")
            break
        
        if not user_input:
            print("⚠ Teks tidak boleh kosong. Coba lagi!")
            continue
        
        # Preprocessing
        processed_text = preprocess_input(user_input, kamus)
        
        if not processed_text:
            print("⚠ Text kosong setelah preprocessing. Coba teks yang lain!")
            continue
        
        # Transform dan predict
        X = vectorizer.transform([processed_text])
        predicted_sentiment = model.predict(X)[0]
        probabilities = model.predict_proba(X)[0]
        confidence = np.max(probabilities)
        
        # Get class probabilities
        classes = sorted(model.classes_.tolist())
        prob_dict = {cls: prob for cls, prob in zip(classes, probabilities)}
        
        # Output
        print("\n" + "-" * 70)
        print(f"[HASIL] Sentiment: {predicted_sentiment}")
        print(f"[CONFIDENCE] {confidence * 100:.2f}%")
        print(f"\n[PROBABILITIES]")
        for cls, prob in prob_dict.items():
            bar_length = int(prob * 30)
            bar = "█" * bar_length + "░" * (30 - bar_length)
            print(f"  {cls:15} [{bar}] {prob * 100:.2f}%")
        print("-" * 70)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Predict sentiment using trained SVM model')
    parser.add_argument('--text', type=str, default=None,
                       help='Text to predict sentiment')
    parser.add_argument('--interactive', action='store_true',
                       help='Run in interactive mode (input dari terminal)')
    parser.add_argument('--file', type=str, default=None,
                       help='Path to file containing text to predict')
    
    args = parser.parse_args()
    
    # Interactive mode (default)
    if args.interactive or (not args.text and not args.file):
        predict_interactive()
    # File mode
    elif args.file and os.path.exists(args.file):
        with open(args.file, 'r', encoding='utf-8') as f:
            text = f.read().strip()
        result = predict_sentiment(text)
        print(json.dumps(result, ensure_ascii=False, indent=2))
    # Text argument mode
    elif args.text:
        result = predict_sentiment(args.text)
        print(json.dumps(result, ensure_ascii=False, indent=2))
    else:
        print("❌ Error: Tidak ada input text atau file!")
        print("Gunakan flags: --text, --file, atau --interactive")
