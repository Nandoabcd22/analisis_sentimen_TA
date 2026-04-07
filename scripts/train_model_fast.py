#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
FAST SVM Training - Target <30 seconds, accuracy ~85%+
Uses database cached preprocessing + optimized parameters

Strategy:
- Load preprocessed text from database (skip stemming)
- TF-IDF with 1500 features (reduced from 5000)
- SMOTE with k_neighbors=2 for faster resampling
- Linear SVM kernel (3-5x faster than RBF)
- C=0.5 for faster convergence
"""

import sys
import os
import json
import pickle
import pandas as pd
import numpy as np
import time
from datetime import datetime

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
except ImportError as e:
    print(json.dumps({'success': False, 'error': f'Required packages not installed: {str(e)}'}), flush=True)
    sys.exit(1)


def connect_database():
    """Connect to Laravel database and get reviews with preprocessed case_folding"""
    try:
        import mysql.connector
        
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='',
            database='analisis_sentimen_ta'
        )
        
        if conn.is_connected():
            cursor = conn.cursor(dictionary=True)
            # Load preprocessed text from case_folding column, fallback to review if empty
            cursor.execute("""
                SELECT id, 
                       CASE 
                           WHEN case_folding IS NOT NULL AND case_folding != '' THEN case_folding 
                           ELSE review 
                       END as text,
                       label 
                FROM reviews 
                WHERE review IS NOT NULL AND review != ''
                LIMIT 2000
            """)
            reviews = cursor.fetchall()
            cursor.close()
            conn.close()
            
            print(f"[1] LOADING DATA FROM DATABASE...", file=sys.stderr, flush=True)
            print(f"✓ Loaded {len(reviews)} reviews (fast mode - limited to 2000)", file=sys.stderr, flush=True)
            return pd.DataFrame(reviews)
    except Exception as e:
        print(f"⚠ Database error: {str(e)}", file=sys.stderr, flush=True)
    
    return None


def train_model_fast(kernel='linear', test_size=10):
    """
    FAST SVM Training - <1 minute target
    
    Optimizations:
    1. Database cached preprocessing (no stemming)
    2. TF-IDF: 1500 features (from 5000)
    3. SMOTE: k_neighbors=2 (faster)
    4. Linear SVM kernel (3-5x faster)
    5. C=0.5 (faster convergence)
    6. Limited to 2000 samples max
    """
    
    print("=" * 70, file=sys.stderr, flush=True)
    print("🚀 FAST SVM TRAINING (Database Cached)", file=sys.stderr, flush=True)
    print("=" * 70, file=sys.stderr, flush=True)
    
    start_total = time.time()
    stage_times = {}
    
    try:
        # STEP 1: Load Data
        print("\n[1] LOADING DATA FROM DATABASE...", file=sys.stderr, flush=True)
        start_step = time.time()
        
        df = connect_database()
        
        if df is None or len(df) < 10:
            return {
                'success': False,
                'error': 'Tidak ada data yang cukup'
            }
        
        stage_times['load_data'] = time.time() - start_step
        print(f"✓ Loaded {df.shape[0]} reviews", file=sys.stderr, flush=True)
        print(f"✓ Label Distribution: {df['label'].value_counts().to_dict()}", file=sys.stderr, flush=True)
        print(f"⏱️  Time: {stage_times['load_data']:.2f}s\n", file=sys.stderr, flush=True)
        
        # STEP 2: Data already preprocessed from database
        print(f"[2] USING DATABASE CACHED PREPROCESSING...", file=sys.stderr, flush=True)
        start_step = time.time()
        
        df['text_preprocessed'] = df['text']
        df = df[df['text_preprocessed'].str.len() > 0].reset_index(drop=True)
        
        stage_times['preprocess'] = time.time() - start_step
        print(f"✓ Using {df.shape[0]} preprocessed texts from database", file=sys.stderr, flush=True)
        print(f"⏱️  Time: {stage_times['preprocess']:.2f}s\n", file=sys.stderr, flush=True)
        
        # STEP 3: Train/Test Split
        print(f"[3] SPLITTING DATA...", file=sys.stderr, flush=True)
        start_step = time.time()
        
        X = df['text_preprocessed']
        y = df['label']
        
        X_train, X_test, y_train, y_test = train_test_split(
            X, y,
            test_size=test_size / 100,
            random_state=42,
            stratify=y
        )
        
        stage_times['split'] = time.time() - start_step
        print(f"Train: {len(X_train)}, Test: {len(X_test)}", file=sys.stderr, flush=True)
        print(f"⏱️  Time: {stage_times['split']:.2f}s\n", file=sys.stderr, flush=True)
        
        # STEP 4: TF-IDF (OPTIMIZED - 1500 features)
        print(f"[4] TF-IDF VECTORIZATION (1500 features)...", file=sys.stderr, flush=True)
        start_step = time.time()
        
        tfidf = TfidfVectorizer(max_features=1500, ngram_range=(1, 1), sublinear_tf=True)
        X_train_tfidf = tfidf.fit_transform(X_train)
        X_test_tfidf = tfidf.transform(X_test)
        
        stage_times['tfidf'] = time.time() - start_step
        print(f"✓ Features: {X_train_tfidf.shape[1]}", file=sys.stderr, flush=True)
        print(f"⏱️  Time: {stage_times['tfidf']:.2f}s\n", file=sys.stderr, flush=True)
        
        # STEP 5: SMOTE (FAST - k_neighbors=2)
        print(f"[5] SMOTE BALANCING (k_neighbors=2)...", file=sys.stderr, flush=True)
        start_step = time.time()
        
        smote = SMOTE(random_state=42, k_neighbors=2)
        X_train_balanced, y_train_balanced = smote.fit_resample(X_train_tfidf, y_train)
        
        stage_times['smote'] = time.time() - start_step
        print(f"✓ After SMOTE: {len(y_train_balanced)} samples", file=sys.stderr, flush=True)
        print(f"⏱️  Time: {stage_times['smote']:.2f}s\n", file=sys.stderr, flush=True)
        
        # STEP 6: SVM Training (OPTIMIZED)
        print(f"[6] SVM TRAINING ({kernel.upper()} kernel)...", file=sys.stderr, flush=True)
        start_step = time.time()
        
        # Linear kernel by default (3-5x faster)
        svm = SVC(
            kernel=kernel,
            C=0.5,              # Reduced for faster convergence
            gamma='scale',
            probability=True,
            random_state=42
        )
        svm.fit(X_train_balanced, y_train_balanced)
        
        stage_times['svm_train'] = time.time() - start_step
        print(f"✓ SVM training completed", file=sys.stderr, flush=True)
        print(f"⏱️  Time: {stage_times['svm_train']:.2f}s\n", file=sys.stderr, flush=True)
        
        # STEP 7: Evaluation
        print(f"[7] EVALUATING MODEL...", file=sys.stderr, flush=True)
        start_step = time.time()
        
        y_pred = svm.predict(X_test_tfidf)
        
        accuracy = accuracy_score(y_test, y_pred)
        precision = precision_score(y_test, y_pred, average='weighted', zero_division=0)
        recall = recall_score(y_test, y_pred, average='weighted', zero_division=0)
        f1 = f1_score(y_test, y_pred, average='weighted', zero_division=0)
        cm = confusion_matrix(y_test, y_pred, labels=sorted(svm.classes_))
        
        stage_times['evaluation'] = time.time() - start_step
        print(f"Accuracy: {accuracy:.4f}", file=sys.stderr, flush=True)
        print(f"Precision: {precision:.4f}", file=sys.stderr, flush=True)
        print(f"Recall: {recall:.4f}", file=sys.stderr, flush=True)
        print(f"F1 Score: {f1:.4f}", file=sys.stderr, flush=True)
        print(f"⏱️  Time: {stage_times['evaluation']:.2f}s\n", file=sys.stderr, flush=True)
        
        # Per-class metrics
        report = classification_report(y_test, y_pred, output_dict=True)
        per_class_metrics = {}
        for label in sorted(svm.classes_):
            if label in report:
                per_class_metrics[label] = {
                    'precision': float(report[label]['precision']),
                    'recall': float(report[label]['recall']),
                    'f1-score': float(report[label]['f1-score']),
                    'support': int(report[label]['support'])
                }
        
        # STEP 8: Save Model
        print(f"[8] SAVING MODEL...", file=sys.stderr, flush=True)
        start_step = time.time()
        
        model_dir = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private')
        os.makedirs(model_dir, exist_ok=True)
        
        with open(os.path.join(model_dir, 'svm_model.pkl'), 'wb') as f:
            pickle.dump(svm, f)
        
        with open(os.path.join(model_dir, 'tfidf_vectorizer.pkl'), 'wb') as f:
            pickle.dump(tfidf, f)
        
        stage_times['save'] = time.time() - start_step
        print(f"✓ Model saved", file=sys.stderr, flush=True)
        print(f"⏱️  Time: {stage_times['save']:.2f}s\n", file=sys.stderr, flush=True)
        
        # TIMING SUMMARY
        total_time = time.time() - start_total
        print("=" * 70, file=sys.stderr, flush=True)
        print("⏱️  TIMING SUMMARY", file=sys.stderr, flush=True)
        print("=" * 70, file=sys.stderr, flush=True)
        for stage, elapsed in sorted(stage_times.items()):
            percentage = (elapsed / total_time * 100) if total_time > 0 else 0
            print(f"  {stage.upper():25} {elapsed:7.2f}s  ({percentage:5.1f}%)", file=sys.stderr, flush=True)
        print(f"  {'TOTAL':25} {total_time:7.2f}s  (100.0%)", file=sys.stderr, flush=True)
        print("=" * 70, file=sys.stderr, flush=True)
        
        # Return Results
        result = {
            'success': True,
            'message': f'Model training completed in {total_time:.2f}s (FAST MODE)',
            'data': {
                'total_samples': len(df),
                'train_samples': len(X_train),
                'test_samples': len(X_test),
                'features': X_train_tfidf.shape[1],
                'classes': sorted(svm.classes_.tolist()),
                'kernel': kernel,
                'timestamp': datetime.now().isoformat(),
                'total_time': total_time,
                'mode': 'fast'
            },
            'evaluation': {
                'accuracy': float(accuracy),
                'precision': float(precision),
                'recall': float(recall),
                'f1_score': float(f1),
                'confusion_matrix': cm.tolist(),
                'per_class_metrics': per_class_metrics,
                'classes': sorted(svm.classes_.tolist())
            },
            'evaluation_result': {
                'accuracy': float(accuracy),
                'precision': float(precision),
                'recall': float(recall),
                'f1_score': float(f1),
                'confusion_matrix': cm.tolist(),
                'per_class_metrics': per_class_metrics,
                'classes': sorted(svm.classes_.tolist())
            },
            'stage_times': stage_times
        }
        
        return result
        
    except Exception as e:
        import traceback
        print(f"❌ ERROR: {str(e)}", file=sys.stderr, flush=True)
        print(traceback.format_exc(), file=sys.stderr, flush=True)
        
        return {
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc()
        }


if __name__ == '__main__':
    import argparse
    parser = argparse.ArgumentParser(description='Train Fast SVM Model')
    parser.add_argument('--kernel', type=str, default='linear',
                       help='SVM kernel (default: linear for speed)',
                       choices=['linear', 'rbf', 'polynomial', 'sigmoid'])
    parser.add_argument('--test_size', type=int, default=10,
                       help='Test size percentage (default: 10)')
    
    args = parser.parse_args()
    
    result = train_model_fast(kernel=args.kernel, test_size=args.test_size)
    
    print(json.dumps(result, ensure_ascii=False, indent=2))
