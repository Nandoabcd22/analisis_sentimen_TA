#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
EXACT Colab Training Script
This is 100% copy of Colab notebook training logic
No modifications, no optimizations - EXACT match
"""

import sys
import os
import json
import pickle
import pandas as pd
import numpy as np
import re
from datetime import datetime
import warnings
warnings.filterwarnings('ignore')

# LOCK RANDOMNESS
RANDOM_SEED = 42
np.random.seed(RANDOM_SEED)
import random
random.seed(RANDOM_SEED)

# Force UTF-8 encoding on Windows
if sys.platform == 'win32':
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Import required packages
try:
    from sklearn.svm import SVC
    from sklearn.feature_extraction.text import TfidfVectorizer
    from sklearn.model_selection import train_test_split
    from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, confusion_matrix, classification_report
    from imblearn.over_sampling import SMOTE
    import nltk
    from nltk.tokenize import word_tokenize
    from nltk.corpus import stopwords
    from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
    import matplotlib
    matplotlib.use('Agg')  # Non-interactive backend
    import matplotlib.pyplot as plt
    import seaborn as sns
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

# Global stemmer
try:
    _STEMMER = StemmerFactory().create_stemmer()
except:
    _STEMMER = None


def generate_confusion_matrix_image(cm, classes, title="Confusion Matrix"):
    """Generate confusion matrix heatmap as base64 image"""
    try:
        import io
        import base64
        
        # Create figure
        fig, ax = plt.subplots(figsize=(8, 6), dpi=100)
        
        # Create heatmap
        sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', 
                    xticklabels=classes, yticklabels=classes,
                    cbar_kws={'label': 'Count'},
                    ax=ax, square=True, linewidths=0.5, linecolor='white')
        
        ax.set_xlabel('Predicted', fontsize=12, fontweight='bold')
        ax.set_ylabel('Actual', fontsize=12, fontweight='bold')
        ax.set_title(title, fontsize=14, fontweight='bold', pad=20)
        
        # Convert to base64
        buffer = io.BytesIO()
        plt.savefig(buffer, format='png', bbox_inches='tight', dpi=100)
        buffer.seek(0)
        image_base64 = base64.b64encode(buffer.read()).decode('utf-8')
        buffer.close()
        plt.close(fig)
        
        return {
            'success': True,
            'image': image_base64,
            'format': 'png'
        }
    except Exception as e:
        return {
            'success': False,
            'error': str(e)
        }


def load_kamus_normalisasi():
    """Load normalization dictionary"""
    kamus = {}
    current_dir = os.path.dirname(os.path.abspath(__file__))
    kamus_path = os.path.join(current_dir, '..', 'resources', 'data', 'kamus_normalisasi.txt')
    
    try:
        with open(kamus_path, 'r', encoding='utf-8') as file:
            for line in file:
                parts = line.strip().split('\t')
                if len(parts) == 2:
                    kamus[parts[0].lower()] = parts[1]
        print(f"✓ Kamus loaded: {len(kamus)} entries", file=sys.stderr, flush=True)
    except:
        print("⚠ Kamus file not found, using default", file=sys.stderr, flush=True)
    
    return kamus


def case_folding(text):
    """Case folding"""
    return text.lower() if isinstance(text, str) else ""


def clean_text(text):
    """Step 2: Remove special characters"""
    if not isinstance(text, str):
        return ""
    text = re.sub(r'http\S+|www\S+', '', text)
    text = re.sub(r'@\w+|#\w+', '', text)
    text = re.sub(r'\d+', '', text)
    text = re.sub(r'[^a-zA-Z\s]', '', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text


def tokenize_text(text):
    """Step 3: Tokenization"""
    if not isinstance(text, str) or text == "":
        return []
    try:
        return word_tokenize(text)
    except:
        return text.split()


def normalize_tokens(tokens, kamus):
    """Step 4: Normalisasi kata"""
    normalized_tokens = []
    for token in tokens:
        if token.lower() in kamus:
            normalized_tokens.extend(kamus[token.lower()].split())
        else:
            normalized_tokens.append(token)
    return normalized_tokens


def remove_stopwords(tokens, stop_words):
    """Step 5: Stopword removal"""
    return [w.lower() for w in tokens if w.lower() not in stop_words]


def stem_tokens(tokens, stemmer):
    """Step 6: Stemming"""
    return " ".join([stemmer.stem(w) for w in tokens])


def preprocess_exact(text, kamus, stop_words, stemmer):
    """EXACT Colab preprocessing pipeline"""
    # Case folding
    text = case_folding(text)
    
    # Cleaning
    text = clean_text(text)
    
    # Tokenization
    tokens = tokenize_text(text)
    
    # Normalisasi
    tokens = normalize_tokens(tokens, kamus)
    
    # Stopword removal
    tokens = remove_stopwords(tokens, stop_words)
    
    # Stemming
    return stem_tokens(tokens, stemmer)


def connect_database():
    """Load data from database"""
    import mysql.connector
    from mysql.connector import Error
    
    config = {
        'user': 'root',
        'password': '',
        'host': '127.0.0.1',
        'database': 'analisis_sentimen_ta',
        'port': 3306,
        'raise_on_warnings': False
    }
    
    try:
        connection = mysql.connector.connect(**config)
        if connection.is_connected():
            query = "SELECT id, review as text, label FROM reviews WHERE label IN ('Negatif', 'Netral', 'Positif')"
            df = pd.read_sql(query, connection)
            connection.close()
            return df
    except Error as e:
        print(f"Database error: {e}", file=sys.stderr, flush=True)
    
    return None


def train_svm_exact(kernel='rbf', test_size=10):
    """EXACT Colab training - no modifications"""
    
    print("=" * 70, file=sys.stderr, flush=True)
    print(f"[EXACT COLAB TRAINING] Kernel: {kernel.upper()}, Test Size: {test_size}%", file=sys.stderr, flush=True)
    print("=" * 70, file=sys.stderr, flush=True)
    
    # STEP 1: Load data
    print("\n[1] LOADING DATA FROM DATABASE...", file=sys.stderr, flush=True)
    df = connect_database()
    
    if df is None or len(df) == 0:
        result = {'success': False, 'error': 'Cannot load data from database'}
        print(json.dumps(result), flush=True)
        return
    
    print(f"✓ Loaded {len(df)} reviews", file=sys.stderr, flush=True)
    print(f"Distribution: {df['label'].value_counts().to_dict()}", file=sys.stderr, flush=True)
    
    # STEP 2: Exact Colab preprocessing
    print("\n[2] PREPROCESSING (EXACT COLAB)...", file=sys.stderr, flush=True)
    kamus = load_kamus_normalisasi()
    stop_ind = set(nltk.corpus.stopwords.words('indonesian'))
    stemmer = _STEMMER
    
    # Process all texts
    df['text_processed'] = df['text'].apply(
        lambda x: preprocess_exact(x, kamus, stop_ind, stemmer)
    )
    
    print(f"✓ Preprocessing completed: {len(df)} texts processed", file=sys.stderr, flush=True)
    
    # STEP 3: Split data (EXACT 90:10)
    print("\n[3] SPLITTING DATA (90:10 - EXACT COLAB)...", file=sys.stderr, flush=True)
    X = df['text_processed']
    y = df['label']
    
    test_size_ratio = test_size / 100.0
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=test_size_ratio, random_state=42, stratify=y
    )
    
    print(f"Train: {len(X_train)}, Test: {len(X_test)}", file=sys.stderr, flush=True)
    
    # STEP 4: TF-IDF (EXACT COLAB - max_features=5000)
    print("\n[4] TF-IDF VECTORIZATION (EXACT COLAB)...", file=sys.stderr, flush=True)
    tfidf = TfidfVectorizer(max_features=5000)
    X_train_tfidf = tfidf.fit_transform(X_train)
    X_test_tfidf = tfidf.transform(X_test)
    
    print(f"✓ Features: {X_train_tfidf.shape[1]}", file=sys.stderr, flush=True)
    
    # STEP 5: SMOTE (EXACT COLAB - random_state=42, k_neighbors=3)
    print("\n[5] APPLYING SMOTE (EXACT COLAB)...", file=sys.stderr, flush=True)
    smote = SMOTE(random_state=42, k_neighbors=3)
    X_train_balanced, y_train_balanced = smote.fit_resample(X_train_tfidf, y_train)
    
    print(f"After SMOTE: {len(y_train_balanced)} samples", file=sys.stderr, flush=True)
    print(f"Distribution: {pd.Series(y_train_balanced).value_counts().to_dict()}", file=sys.stderr, flush=True)
    
    # STEP 6: Train SVM (EXACT COLAB - kernel, C=1, gamma='scale', random_state=42, probability=True for predict_proba)
    print(f"\n[6] TRAINING SVM (kernel='{kernel}', C=1, gamma='scale', probability=True)...", file=sys.stderr, flush=True)
    svm = SVC(kernel=kernel, C=1, gamma='scale', random_state=42, probability=True)
    svm.fit(X_train_balanced, y_train_balanced)
    
    print("✓ SVM training completed", file=sys.stderr, flush=True)
    
    # STEP 7: Evaluation (EXACT COLAB)
    print("\n[7] EVALUATING MODEL (EXACT COLAB)...", file=sys.stderr, flush=True)
    y_pred = svm.predict(X_test_tfidf)
    
    accuracy = accuracy_score(y_test, y_pred)
    precision = precision_score(y_test, y_pred, average='weighted', zero_division=0)
    recall = recall_score(y_test, y_pred, average='weighted', zero_division=0)
    f1 = f1_score(y_test, y_pred, average='weighted', zero_division=0)
    cm = confusion_matrix(y_test, y_pred, labels=sorted(svm.classes_))
    
    print(f"Accuracy : {accuracy:.4f}", file=sys.stderr, flush=True)
    print(f"Precision: {precision:.4f}", file=sys.stderr, flush=True)
    print(f"Recall   : {recall:.4f}", file=sys.stderr, flush=True)
    print(f"F1 Score : {f1:.4f}", file=sys.stderr, flush=True)
    
    # Per-class metrics
    report = classification_report(y_test, y_pred, output_dict=True)
    per_class = {}
    for label in sorted(svm.classes_):
        if label in report:
            per_class[label] = {
                'precision': float(report[label]['precision']),
                'recall': float(report[label]['recall']),
                'f1-score': float(report[label]['f1-score']),
                'support': int(report[label]['support'])
            }
    
    print("\nPer-class metrics:", file=sys.stderr, flush=True)
    print(classification_report(y_test, y_pred), file=sys.stderr, flush=True)
    
    # STEP 8: Save artifacts
    print("\n[8] SAVING MODEL ARTIFACTS...", file=sys.stderr, flush=True)
    model_dir = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private')
    os.makedirs(model_dir, exist_ok=True)
    
    with open(os.path.join(model_dir, 'svm_model.pkl'), 'wb') as f:
        pickle.dump(svm, f)
    
    with open(os.path.join(model_dir, 'tfidf_vectorizer.pkl'), 'wb') as f:
        pickle.dump(tfidf, f)
    
    with open(os.path.join(model_dir, 'kamus_normalisasi.pkl'), 'wb') as f:
        pickle.dump(kamus, f)
    
    with open(os.path.join(model_dir, 'stopwords.pkl'), 'wb') as f:
        pickle.dump(stop_ind, f)
    
    print("✓ Model artifacts saved", file=sys.stderr, flush=True)
    
    # Generate Wordcloud
    print("\n[9] GENERATING WORDCLOUD...", file=sys.stderr, flush=True)
    try:
        from wordcloud_generator import generate_wordcloud_image
        from ast import literal_eval
        
        # Combine all stemmed text
        all_text = ' '.join(df['stemming'].fillna('').astype(str).tolist())
        
        if all_text.strip():
            wordcloud_result = generate_wordcloud_image(all_text, width=1200, height=600)
            
            if wordcloud_result['success']:
                # Save base64 image to file
                with open(os.path.join(model_dir, 'wordcloud.b64'), 'w') as f:
                    f.write(wordcloud_result['image'])
                print("✓ Wordcloud generated successfully", file=sys.stderr, flush=True)
            else:
                print(f"⚠ Wordcloud generation failed: {wordcloud_result['error']}", file=sys.stderr, flush=True)
        else:
            print("⚠ No text data for wordcloud", file=sys.stderr, flush=True)
            
    except Exception as e:
        print(f"⚠ Error in wordcloud generation: {str(e)}", file=sys.stderr, flush=True)
    
    # Generate Confusion Matrix Heatmap
    print("\n[10] GENERATING CONFUSION MATRIX HEATMAP...", file=sys.stderr, flush=True)
    cm_image = None
    try:
        cm_result = generate_confusion_matrix_image(
            cm, 
            sorted(svm.classes_),
            title=f"Confusion Matrix - SVM ({kernel} kernel)"
        )
        
        if cm_result['success']:
            # Save base64 image to file
            with open(os.path.join(model_dir, 'confusion_matrix.b64'), 'w') as f:
                f.write(cm_result['image'])
            cm_image = cm_result['image']
            print("✓ Confusion matrix heatmap generated successfully", file=sys.stderr, flush=True)
        else:
            print(f"⚠ Confusion matrix generation failed: {cm_result['error']}", file=sys.stderr, flush=True)
            
    except Exception as e:
        print(f"⚠ Error in confusion matrix generation: {str(e)}", file=sys.stderr, flush=True)
    
    # Save metrics to model_metrics.json
    print("\n[11] SAVING MODEL METRICS...", file=sys.stderr, flush=True)
    try:
        metrics_data = {
            'kernel': kernel,
            'timestamp': datetime.now().isoformat(),
            'evaluation': {
                'accuracy': float(accuracy),
                'precision_weighted': float(precision),
                'recall_weighted': float(recall),
                'f1_weighted': float(f1),
                'confusion_matrix': cm.tolist(),
                'confusion_matrix_image': cm_image,
                'per_class_metrics': per_class,
                'classes': sorted(svm.classes_.tolist())
            },
            'data': {
                'total_samples': len(df),
                'train_samples': len(X_train),
                'test_samples': len(X_test),
                'features': X_train_tfidf.shape[1]
            }
        }
        
        metrics_path = os.path.join(model_dir, 'model_metrics.json')
        with open(metrics_path, 'w', encoding='utf-8') as f:
            json.dump(metrics_data, f, indent=2, ensure_ascii=False)
        
        print("✓ Model metrics saved to model_metrics.json", file=sys.stderr, flush=True)
    except Exception as e:
        print(f"⚠ Error saving model metrics: {str(e)}", file=sys.stderr, flush=True)
    
    print("\n" + "=" * 70, file=sys.stderr, flush=True)
    print("TRAINING COMPLETED (EXACT COLAB)!", file=sys.stderr, flush=True)
    print("=" * 70, file=sys.stderr, flush=True)
    
    result = {
        'success': True,
        'message': f'Training completed (kernel={kernel}) - EXACT COLAB',
        'data': {
            'total_samples': len(df),
            'train_samples': len(X_train),
            'test_samples': len(X_test),
            'features': X_train_tfidf.shape[1],
            'classes': sorted(svm.classes_.tolist()),
            'timestamp': datetime.now().isoformat()
        },
        'evaluation_result': {
            'accuracy': float(accuracy),
            'precision_weighted': float(precision),
            'recall_weighted': float(recall),
            'f1_weighted': float(f1),
            'confusion_matrix': cm.tolist(),
            'confusion_matrix_image': cm_image,
            'per_class_metrics': per_class,
            'classes': sorted(svm.classes_.tolist())
        },
        'model_config': {
            'kernel': kernel,
            'test_size': float(test_size),
            'C': 1,
            'gamma': 'scale',
            'tfidf_max_features': 5000,
            'smote_k_neighbors': 3,
            'note': 'EXACT COLAB REPLICATION - NO MODIFICATIONS'
        }
    }
    
    print(json.dumps(result, indent=2, ensure_ascii=False), flush=True)


if __name__ == '__main__':
    import argparse
    parser = argparse.ArgumentParser(description='EXACT Colab Training')
    parser.add_argument('--kernel', type=str, default='rbf',
                       help='SVM kernel: linear, rbf, polynomial, sigmoid',
                       choices=['linear', 'rbf', 'polynomial', 'sigmoid'])
    parser.add_argument('--test_size', type=float, default=10,
                       help='Test set size as percentage (10-50)')
    
    args = parser.parse_args()
    
    test_size = args.test_size
    if test_size < 10 or test_size > 50:
        test_size = 10
    
    train_svm_exact(kernel=args.kernel, test_size=test_size)
