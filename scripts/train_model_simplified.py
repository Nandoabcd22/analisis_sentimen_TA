#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
SVM Sentiment Analysis Training - Simplified Version
Workflow: Load → Preprocess → Split 9:1 → TF-IDF → SMOTE → Train → Evaluate
"""

import sys
import os
import json
import argparse
import pickle
import pandas as pd
import numpy as np
import re
from datetime import datetime
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
    from sklearn.svm import SVC
    from sklearn.feature_extraction.text import TfidfVectorizer
    from sklearn.model_selection import train_test_split
    from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, confusion_matrix, classification_report
    from imblearn.over_sampling import SMOTE
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

# Global stemmer instance for performance
try:
    _STEMMER = StemmerFactory().create_stemmer()
except:
    _STEMMER = None


def load_kamus_normalisasi():
    """Load normalization dictionary from file"""
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
    """Step 1: Convert to lowercase"""
    return text.lower() if isinstance(text, str) else ""


def clean_text(text):
    """Step 2: Remove special characters"""
    text = re.sub(r'http\S+', '', text)  # Remove URLs
    text = re.sub(r'@\w+', '', text)     # Remove mentions
    text = re.sub(r'#\w+', '', text)     # Remove hashtags
    text = re.sub(r'[^\w\s]', '', text)  # Remove special chars
    text = re.sub(r'\d+', '', text)      # Remove numbers
    text = re.sub(r'\s+', ' ', text)     # Remove extra spaces
    return text.strip()


def tokenize_text(text):
    """Step 3: Tokenization"""
    if not isinstance(text, str) or text == "":
        return []
    try:
        return word_tokenize(text)
    except:
        return text.split()


def normalisasi_kata(tokens, kamus):
    """Step 4: Normalize words using dictionary"""
    normalized = []
    for token in tokens:
        token_lower = token.lower()
        if token_lower in kamus:
            normalized.extend(kamus[token_lower].split())
        else:
            normalized.append(token_lower)
    return normalized


def remove_stopwords(tokens):
    """Step 5: Remove stopwords"""
    try:
        stopwords_set = set(stopwords.words('indonesian'))
    except:
        stopwords_set = {'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'pada', 'adalah', 'ini', 'itu', 'ada'}
    return [t for t in tokens if t not in stopwords_set and len(t) > 2]


def stemming_tokens(tokens):
    """Step 6: Stemming"""
    if _STEMMER is None or not tokens:
        return " ".join(tokens)
    try:
        return " ".join([_STEMMER.stem(t) for t in tokens])
    except:
        return " ".join(tokens)


def preprocess_text(text, kamus):
    """Complete 6-step preprocessing pipeline"""
    text = case_folding(text)
    text = clean_text(text)
    if not text.strip():
        return ""
    
    tokens = tokenize_text(text)
    if not tokens:
        return ""
    
    tokens = normalisasi_kata(tokens, kamus)
    tokens = remove_stopwords(tokens)
    if not tokens:
        return ""
    
    return stemming_tokens(tokens)


def connect_database():
    """Load reviews from database"""
    try:
        import mysql.connector
        conn = mysql.connector.connect(
            host='127.0.0.1',
            user='root',
            password='',
            database='analisis_sentimen_ta'
        )
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, review as text, label FROM reviews WHERE review IS NOT NULL AND review != ''")
        reviews = cursor.fetchall()
        cursor.close()
        conn.close()
        print(f"✓ Loaded {len(reviews)} reviews from database", file=sys.stderr, flush=True)
        return pd.DataFrame(reviews)
    except Exception as e:
        print(f"✗ Database error: {str(e)}", file=sys.stderr, flush=True)
        return None


def train_svm(kernel='rbf', test_size=0.1):
    """Train SVM model - simplified 8-step workflow"""
    
    # Convert test_size to float
    test_size = float(test_size)
    if test_size > 1:
        test_size = test_size / 100
    
    print("\n" + "=" * 70, file=sys.stderr, flush=True)
    print(f"[STARTING SVM TRAINING] Kernel: {kernel.upper()}, Test Size: {test_size*100:.0f}%", file=sys.stderr, flush=True)
    print("=" * 70, file=sys.stderr, flush=True)
    
    try:
        # STEP 1: Load data
        print("\n[1] LOADING DATA FROM DATABASE...", file=sys.stderr, flush=True)
        df = connect_database()
        if df is None or len(df) < 10:
            result = {'success': False, 'error': 'No data or insufficient data'}
            print(json.dumps(result), flush=True)
            return
        
        print(f"Total: {len(df)}", file=sys.stderr, flush=True)
        print(f"Distribution: {df['label'].value_counts().to_dict()}", file=sys.stderr, flush=True)
        
        # STEP 2: Preprocessing
        print("\n[2] PREPROCESSING TEXTS...", file=sys.stderr, flush=True)
        kamus = load_kamus_normalisasi()
        
        processed = []
        for idx, text in enumerate(df['text']):
            if (idx + 1) % 300 == 0:
                print(f"   Processed {idx + 1}/{len(df)}...", file=sys.stderr, flush=True)
            try:
                processed.append(preprocess_text(text, kamus))
            except Exception as e:
                print(f"   ⚠ Error processing row {idx}: {str(e)}", file=sys.stderr, flush=True)
                processed.append("")
        
        df['text_lemmatized'] = processed
        df = df[df['text_lemmatized'].str.len() > 0].reset_index(drop=True)
        
        if len(df) < 10:
            result = {'success': False, 'error': 'Insufficient data after preprocessing'}
            print(json.dumps(result), flush=True)
            return
        
        print(f"✓ After preprocessing: {len(df)} reviews", file=sys.stderr, flush=True)
        
        # STEP 3: Split data 9:1 (90% train, 10% test)
        print("\n[3] SPLITTING DATA (90:10)...", file=sys.stderr, flush=True)
        X = df['text_lemmatized']
        y = df['label']
        
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=test_size, random_state=42, stratify=y
        )
        
        print(f"Train: {len(X_train)}, Test: {len(X_test)}", file=sys.stderr, flush=True)
        
        # STEP 4: TF-IDF Vectorization
        print("\n[4] TF-IDF VECTORIZATION...", file=sys.stderr, flush=True)
        tfidf = TfidfVectorizer(max_features=5000, min_df=2, max_df=0.8)
        X_train_tfidf = tfidf.fit_transform(X_train)
        X_test_tfidf = tfidf.transform(X_test)
        
        print(f"✓ Features created: {X_train_tfidf.shape[1]}", file=sys.stderr, flush=True)
        
        # STEP 5: SMOTE - Balance training data
        print("\n[5] APPLYING SMOTE (BALANCING DATA)...", file=sys.stderr, flush=True)
        smote = SMOTE(random_state=42, k_neighbors=5)
        X_train_balanced, y_train_balanced = smote.fit_resample(X_train_tfidf, y_train)
        
        print(f"After SMOTE: {len(y_train_balanced)} samples", file=sys.stderr, flush=True)
        print(f"Distribution: {pd.Series(y_train_balanced).value_counts().to_dict()}", file=sys.stderr, flush=True)
        
        # STEP 6: Train SVM
        print(f"\n[6] TRAINING SVM (kernel='{kernel}', C=1, gamma='scale')...", file=sys.stderr, flush=True)
        svm = SVC(kernel=kernel, C=1, gamma='scale', random_state=42, probability=False)
        svm.fit(X_train_balanced, y_train_balanced)
        print("✓ SVM training completed", file=sys.stderr, flush=True)
        
        # STEP 7: Evaluation
        print("\n[7] EVALUATING MODEL...", file=sys.stderr, flush=True)
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
        
        # Get per-class metrics
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
        
        # STEP 8: Save model and artifacts
        print("\n[8] SAVING MODEL ARTIFACTS...", file=sys.stderr, flush=True)
        model_dir = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private')
        os.makedirs(model_dir, exist_ok=True)
        
        # Save model
        with open(os.path.join(model_dir, 'svm_model.pkl'), 'wb') as f:
            pickle.dump(svm, f)
        
        # Save TF-IDF vectorizer
        with open(os.path.join(model_dir, 'tfidf_vectorizer.pkl'), 'wb') as f:
            pickle.dump(tfidf, f)
        
        # Save kamus
        with open(os.path.join(model_dir, 'kamus_normalisasi.pkl'), 'wb') as f:
            pickle.dump(kamus, f)
        
        # Save metrics as JSON
        metrics_data = {
            'kernel': kernel,
            'timestamp': datetime.now().isoformat(),
            'evaluation': {
                'accuracy': float(accuracy),
                'precision_weighted': float(precision),
                'recall_weighted': float(recall),
                'f1_weighted': float(f1),
                'confusion_matrix': cm.tolist(),
                'per_class_metrics': per_class,
                'classes': sorted(svm.classes_.tolist())
            },
            'data_info': {
                'total_samples': len(df),
                'train_samples': len(X_train),
                'test_samples': len(X_test),
                'features': X_train_tfidf.shape[1]
            }
        }
        
        with open(os.path.join(model_dir, 'model_metrics.json'), 'w', encoding='utf-8') as f:
            json.dump(metrics_data, f, indent=2, ensure_ascii=False)
        
        print("✓ Model and artifacts saved", file=sys.stderr, flush=True)
        
        # Return success JSON
        print("\n" + "=" * 70, file=sys.stderr, flush=True)
        print("TRAINING COMPLETED SUCCESSFULLY!", file=sys.stderr, flush=True)
        print("=" * 70, file=sys.stderr, flush=True)
        
        result = {
            'success': True,
            'message': f'Training completed (kernel={kernel})',
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
                'per_class_metrics': per_class,
                'classes': sorted(svm.classes_.tolist())
            },
            'model_config': {
                'kernel': kernel,
                'test_size': float(test_size),
                'C': 1,
                'gamma': 'scale',
                'tfidf_max_features': 5000,
                'smote_applied': True
            }
        }
        
        print(json.dumps(result, indent=2, ensure_ascii=False), flush=True)
        
    except Exception as e:
        import traceback
        print(f"\n✗ ERROR: {str(e)}", file=sys.stderr, flush=True)
        print(traceback.format_exc(), file=sys.stderr, flush=True)
        
        result = {
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc()
        }
        print(json.dumps(result, indent=2, ensure_ascii=False), flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Train SVM Sentiment Analysis Model')
    parser.add_argument('--kernel', type=str, default='rbf',
                       help='SVM kernel: linear, rbf, polynomial, sigmoid',
                       choices=['linear', 'rbf', 'polynomial', 'sigmoid'])
    parser.add_argument('--test_size', type=float, default=10,
                       help='Test set size as percentage (10-50)')
    
    args = parser.parse_args()
    
    # Validate test_size
    test_size = args.test_size
    if test_size < 10 or test_size > 50:
        test_size = 10
    
    train_svm(kernel=args.kernel, test_size=test_size)
