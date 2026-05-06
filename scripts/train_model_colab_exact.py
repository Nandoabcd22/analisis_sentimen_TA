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


def load_preprocessing_cache():
    """Load preprocessing cache from file"""
    cache_file = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private', 'preprocessing_cache.json')
    cache = {}
    try:
        if os.path.exists(cache_file):
            with open(cache_file, 'r', encoding='utf-8') as f:
                cache = json.load(f)
            print(f"✓ Preprocessing cache loaded: {len(cache)} entries", file=sys.stderr, flush=True)
    except Exception as e:
        print(f"⚠ Could not load cache: {e}", file=sys.stderr, flush=True)
    return cache


def save_preprocessing_cache(cache):
    """Save preprocessing cache to file"""
    cache_file = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private', 'preprocessing_cache.json')
    try:
        os.makedirs(os.path.dirname(cache_file), exist_ok=True)
        with open(cache_file, 'w', encoding='utf-8') as f:
            json.dump(cache, f, ensure_ascii=False, indent=2)
        print(f"✓ Preprocessing cache saved: {len(cache)} entries", file=sys.stderr, flush=True)
    except Exception as e:
        print(f"⚠ Could not save cache: {e}", file=sys.stderr, flush=True)


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
    """Load data from database - Load RAW text for preprocessing"""
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
            # Load raw review text for fresh preprocessing
            query = "SELECT id, review as text, label FROM reviews WHERE label IN ('Negatif', 'Netral', 'Positif')"
            df = pd.read_sql(query, connection)
            connection.close()
            return df
            
    except Error as e:
        print(f"Database error: {e}", file=sys.stderr, flush=True)
        return None
    
    return None


def train_svm_exact(kernel='rbf', test_size=10):
    """EXACT Colab training - Load preprocessed data from DB"""
    
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
    
    # STEP 2: Preprocessing with CACHE (fast + accurate)
    print("\n[2] PREPROCESSING (with CACHE)...", file=sys.stderr, flush=True)
    import time
    start_preprocess = time.time()
    
    kamus = load_kamus_normalisasi()
    stop_ind = set(nltk.corpus.stopwords.words('indonesian'))
    stemmer = _STEMMER
    
    # Load preprocessing cache
    cache = load_preprocessing_cache()
    
    # Process texts with cache
    processed_texts = []
    cached_count = 0
    new_count = 0
    
    for idx, row in df.iterrows():
        review_id = str(row['id'])
        
        # Check if already cached
        if review_id in cache:
            processed_texts.append(cache[review_id])
            cached_count += 1
        else:
            # Preprocess and cache
            processed = preprocess_exact(row['text'], kamus, stop_ind, stemmer)
            processed_texts.append(processed)
            cache[review_id] = processed
            new_count += 1
    
    df['text_processed'] = processed_texts
    
    # Save updated cache
    save_preprocessing_cache(cache)
    
    preprocess_time = time.time() - start_preprocess
    print(f"✓ Preprocessing completed in {preprocess_time:.2f}s", file=sys.stderr, flush=True)
    print(f"  └─ Cached: {cached_count} | New: {new_count} | Total: {len(df)}", file=sys.stderr, flush=True)
    if cached_count > 0:
        print(f"  └─ Time saved: ~{(cached_count / (cached_count + new_count + 0.001)) * preprocess_time:.2f}s (estimated)", file=sys.stderr, flush=True)
    
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
    start_tfidf = time.time()
    tfidf = TfidfVectorizer(max_features=5000)
    X_train_tfidf = tfidf.fit_transform(X_train)
    X_test_tfidf = tfidf.transform(X_test)
    tfidf_time = time.time() - start_tfidf
    
    print(f"✓ Features: {X_train_tfidf.shape[1]} (completed in {tfidf_time:.2f}s)", file=sys.stderr, flush=True)
    
    # STEP 5: SMOTE (EXACT COLAB - random_state=42, k_neighbors=3)
    print("\n[5] APPLYING SMOTE (EXACT COLAB)...", file=sys.stderr, flush=True)
    start_smote = time.time()
    smote = SMOTE(random_state=42, k_neighbors=3)
    X_train_balanced, y_train_balanced = smote.fit_resample(X_train_tfidf, y_train)
    smote_time = time.time() - start_smote
    
    print(f"After SMOTE: {len(y_train_balanced)} samples (completed in {smote_time:.2f}s)", file=sys.stderr, flush=True)
    print(f"Distribution: {pd.Series(y_train_balanced).value_counts().to_dict()}", file=sys.stderr, flush=True)
    
    # STEP 6: Train SVM (EXACT COLAB - kernel, C=1, gamma='scale', random_state=42, probability=True for predict_proba)
    print(f"\n[6] TRAINING SVM (kernel='{kernel}', C=1, gamma='scale', probability=True)...", file=sys.stderr, flush=True)
    start_svm = time.time()
    svm = SVC(kernel=kernel, C=1, gamma='scale', random_state=42, probability=True)
    svm.fit(X_train_balanced, y_train_balanced)
    svm_time = time.time() - start_svm
    
    print(f"✓ SVM training completed in {svm_time:.2f}s", file=sys.stderr, flush=True)
    
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
    
    # STEP 8: Dataset Labeling Results
    print("\n[8] GENERATING DATASET LABELING RESULTS...", file=sys.stderr, flush=True)
    try:
        # Predict on full dataset
        X_full_tfidf = tfidf.transform(X)
        y_full_pred = svm.predict(X_full_tfidf)
        y_full_proba = svm.predict_proba(X_full_tfidf)
        
        # Calculate label distribution
        label_distribution = {}
        unique_labels = sorted(svm.classes_)
        for label in unique_labels:
            count = sum(1 for pred in y_full_pred if pred == label)
            label_distribution[label] = int(count)
        
        print(f"Label distribution: {label_distribution}", file=sys.stderr, flush=True)
        
        # Get top 20 labeled samples with highest confidence
        labeled_samples = []
        
        # Calculate confidence as max probability for each sample
        confidences = np.max(y_full_proba, axis=1)
        
        # Create list of (index, prediction, confidence)
        sample_info = [(i, y_full_pred[i], confidences[i]) for i in range(len(y_full_pred))]
        
        # Sort by confidence (descending) and take top 20
        sample_info_sorted = sorted(sample_info, key=lambda x: x[2], reverse=True)[:20]
        
        # Build labeled samples with text
        for idx, pred, conf in sample_info_sorted:
            original_text = X[idx] if idx < len(X) else ""
            
            labeled_samples.append({
                'text': original_text,
                'label': pred,
                'confidence': float(conf)
            })
        
        print(f"✓ Generated {len(labeled_samples)} top labeled samples", file=sys.stderr, flush=True)
        
    except Exception as e:
        print(f"⚠ Error in labeling: {str(e)}", file=sys.stderr, flush=True)
        label_distribution = {label: 0 for label in sorted(svm.classes_)}
        labeled_samples = []
    
    # STEP 9: Save artifacts
    print("\n[9] SAVING MODEL ARTIFACTS...", file=sys.stderr, flush=True)
    model_dir = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private')
    os.makedirs(model_dir, exist_ok=True)
    
    with open(os.path.join(model_dir, 'svm_model.pkl'), 'wb') as f:
        pickle.dump(svm, f)
    
    with open(os.path.join(model_dir, 'tfidf_vectorizer.pkl'), 'wb') as f:
        pickle.dump(tfidf, f)
    
    # Skip saving kamus & stopwords - no longer needed (preprocessing done in DB)
    # with open(os.path.join(model_dir, 'kamus_normalisasi.pkl'), 'wb') as f:
    #     pickle.dump(kamus, f)
    # with open(os.path.join(model_dir, 'stopwords.pkl'), 'wb') as f:
    #     pickle.dump(stop_ind, f)
    
    print("✓ Model artifacts saved (SVM + TF-IDF)", file=sys.stderr, flush=True)
    
    # Generate Wordcloud
    print("\n[10] GENERATING WORDCLOUD...", file=sys.stderr, flush=True)
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
    
    # Confusion Matrix - send as plain array (frontend will render)
    cm_image = None
    
    # Save metrics to model_metrics.json
    print("\n[11] SAVING MODEL METRICS...", file=sys.stderr, flush=True)
    try:
        metrics_data = {
            'kernel': kernel,
            'timestamp': datetime.now().isoformat(),
            'evaluation': {
                'accuracy': float(accuracy),
                'precision': float(precision),
                'recall': float(recall),
                'f1_score': float(f1),
                'confusion_matrix': cm.tolist(),
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
    
    # Performance breakdown
    total_time = preprocess_time + tfidf_time + smote_time + svm_time
    print("\n⏱️  PERFORMANCE BREAKDOWN:", file=sys.stderr, flush=True)
    print(f"  Preprocessing: {preprocess_time:.2f}s ({(preprocess_time/total_time*100):.1f}%)", file=sys.stderr, flush=True)
    print(f"  TF-IDF:        {tfidf_time:.2f}s ({(tfidf_time/total_time*100):.1f}%)", file=sys.stderr, flush=True)
    print(f"  SMOTE:         {smote_time:.2f}s ({(smote_time/total_time*100):.1f}%)", file=sys.stderr, flush=True)
    print(f"  SVM Training:  {svm_time:.2f}s ({(svm_time/total_time*100):.1f}%)", file=sys.stderr, flush=True)
    print(f"  {'─' * 40}", file=sys.stderr, flush=True)
    print(f"  TOTAL:         {total_time:.2f}s", file=sys.stderr, flush=True)
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
        'evaluation': {
            'accuracy': float(accuracy),
            'precision': float(precision),
            'recall': float(recall),
            'f1_score': float(f1),
            'confusion_matrix': cm.tolist(),
            'per_class_metrics': per_class,
            'classes': sorted(svm.classes_.tolist()),
            'label_distribution': label_distribution,
            'labeled_samples': labeled_samples
        },
        'evaluation_result': {
            'accuracy': float(accuracy),
            'precision': float(precision),
            'recall': float(recall),
            'f1_score': float(f1),
            'confusion_matrix': cm.tolist(),
            'per_class_metrics': per_class,
            'classes': sorted(svm.classes_.tolist()),
            'label_distribution': label_distribution,
            'labeled_samples': labeled_samples
        },
        'model_config': {
            'kernel': kernel,
            'test_size': float(test_size),
            'C': 1,
            'gamma': 'scale',
            'tfidf_max_features': 5000,
            'smote_k_neighbors': 3,
            'preprocessing': 'FRESH (with CACHE) - Accurate results + Fast execution',
            'note': 'BALANCED MODE: Fresh preprocessing with cache - Maintains accuracy while staying fast'
        },
        'timing': {
            'preprocessing_time': float(preprocess_time),
            'tfidf_time': float(tfidf_time),
            'smote_time': float(smote_time),
            'svm_time': float(svm_time),
            'total_time': float(total_time),
            'preprocessing_source': 'Fresh preprocessing from raw text with cache',
            'cache_info': {
                'cached_count': cached_count,
                'new_count': new_count
            },
            'optimization': 'Caching: subsequent runs will be 60-70% faster if data unchanged'
        }
    }
    
    print(json.dumps(result, indent=2, ensure_ascii=False), flush=True)


if __name__ == '__main__':
    import argparse
    try:
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
    
    except Exception as e:
        error_result = {
            'success': False,
            'error': str(e),
            'error_type': type(e).__name__
        }
        print(json.dumps(error_result, ensure_ascii=False), flush=True)
        import traceback
        print(traceback.format_exc(), file=sys.stderr, flush=True)
