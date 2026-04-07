#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
EXACT COLAB Training Script - LOAD PREPROCESSED DATA FROM DATABASE
Matches Google Colab results exactly

Key features:
- Load preprocessed text from database's case_folding column
- Skip stemming (already in database)
- Skip SMOTE (no class balancing)
- TF-IDF with 5000 features (EXACT COLAB)
- SVM with C=1, RBF kernel (EXACT COLAB)
- Direct SVM training without extra steps
- Produces identical results to Google Colab
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
            """)
            reviews = cursor.fetchall()
            cursor.close()
            conn.close()
            
            print(f"[1] LOADING DATA FROM DATABASE...", file=sys.stderr)
            print(f"✓ Loaded {len(reviews)} reviews", file=sys.stderr)
            return pd.DataFrame(reviews)
    except Exception as e:
        print(f"⚠ Database error: {str(e)}", file=sys.stderr)
    
    return None


def train_model_optimized(kernel='rbf', test_size=10):
    """
    OPTIMIZED SVM Training - FAST VERSION
    
    Key optimizations:
    1. Load preprocessed text from database cache_folding column (skip stemming)
    2. TF-IDF with 1000 features (reduced from 5000)
    3. NO SMOTE (skip class balancing)
    4. Direct SVM training
    5. NO wordcloud generation
    
    Target: Training < 1 minute, Accuracy >= 85%
    """
    
    print("=" * 70, file=sys.stderr)
    print("🚀 OPTIMIZED SVM TRAINING (DATABASE CACHED PREPROCESSING)", file=sys.stderr)
    print("=" * 70, file=sys.stderr)
    
    start_total = time.time()
    stage_times = {}
    
    try:
        # ========== STEP 1: Load Data ==========
        start_step = time.time()
        
        df = connect_database()
        
        if df is None or len(df) < 10:
            return {
                'success': False,
                'error': 'Tidak ada data. Gunakan CSV atau Database dengan minimal 10 rows'
            }
        
        stage_times['load_data'] = time.time() - start_step
        print(f"✓ Loaded {df.shape[0]} reviews", file=sys.stderr)
        print(f"✓ Label Distribution: {df['label'].value_counts().to_dict()}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['load_data']:.2f}s\n", file=sys.stderr)
        
        # ========== STEP 2: Load Preprocessed Data (from database) ==========
        print(f"[2] LOADING PREPROCESSED DATA FROM DATABASE...", file=sys.stderr)
        start_step = time.time()
        
        # Data already preprocessed from case_folding column
        df['text_preprocessed'] = df['text']
        
        # Remove empty rows
        df = df[df['text_preprocessed'].str.len() > 0].reset_index(drop=True)
        stage_times['preprocess'] = time.time() - start_step
        print(f"✓ Loaded {df.shape[0]} preprocessed texts", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['preprocess']:.2f}s\n", file=sys.stderr)
        
        if len(df) < 10:
            return {
                'success': False,
                'error': 'Not enough data after loading'
            }
        
        # ========== STEP 3: Train/Test Split ==========
        print(f"[3] SPLITTING DATA (90:10)...", file=sys.stderr)
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
        print(f"Train: {len(X_train)}, Test: {len(X_test)}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['split']:.2f}s\n", file=sys.stderr)
        
        # ========== STEP 4: TF-IDF Vectorization (OPTIMIZED - 800 features, parallel) ==========
        print(f"[4] TF-IDF VECTORIZATION (OPTIMIZED)...", file=sys.stderr)
        start_step = time.time()
        
        # EXACT Colab parameters to match results
        tfidf = TfidfVectorizer(
            max_features=5000           # Match Colab exact
        )
        X_train_tfidf = tfidf.fit_transform(X_train)
        X_test_tfidf = tfidf.transform(X_test)
        
        stage_times['tfidf'] = time.time() - start_step
        print(f"✓ Features: {X_train_tfidf.shape[1]}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['tfidf']:.2f}s\n", file=sys.stderr)
        
        # ========== STEP 5: SKIPPING SMOTE (keep as is) ==========
        print(f"[5] SKIPPING SMOTE...", file=sys.stderr)
        X_train_final = X_train_tfidf
        y_train_final = y_train
        print(f"Using {len(y_train_final)} samples without rebalancing\n", file=sys.stderr)
        
        # ========== STEP 6: SVM Training (OPTIMIZED) ==========
        print(f"[6] TRAINING SVM (kernel='{kernel}')...", file=sys.stderr)
        start_step = time.time()
        
        # Optimized SVM parameters:
        # - Use 'linear' kernel for 3-5x faster training (recommended for text)
        # - Reduced C=0.5 for faster convergence (was 1.0)
        # - Linear kernel recommended for text classification tasks
        
        # EXACT Colab parameters to match results
        svm = SVC(
            kernel=kernel,
            C=1,                        # Match Colab exact (was 0.5 in optimize)
            gamma='scale',
            probability=True,
            random_state=42
        )
        
        svm.fit(X_train_final, y_train_final)
        
        stage_times['svm_train'] = time.time() - start_step
        print(f"✓ SVM training completed with {kernel.upper()} kernel", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['svm_train']:.2f}s\n", file=sys.stderr)
        
        # ========== STEP 7: Evaluation ==========
        print(f"[7] EVALUATING MODEL...", file=sys.stderr)
        start_step = time.time()
        
        y_pred = svm.predict(X_test_tfidf)
        
        accuracy = accuracy_score(y_test, y_pred)
        precision = precision_score(y_test, y_pred, average='weighted', zero_division=0)
        recall = recall_score(y_test, y_pred, average='weighted', zero_division=0)
        f1 = f1_score(y_test, y_pred, average='weighted', zero_division=0)
        cm = confusion_matrix(y_test, y_pred, labels=sorted(svm.classes_))
        
        stage_times['evaluation'] = time.time() - start_step
        print(f"Accuracy: {accuracy:.4f}", file=sys.stderr)
        print(f"Precision: {precision:.4f}", file=sys.stderr)
        print(f"Recall: {recall:.4f}", file=sys.stderr)
        print(f"F1 Score: {f1:.4f}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['evaluation']:.2f}s\n", file=sys.stderr)
        
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
        
        # ========== STEP 8: Save Model Artifacts ==========
        print(f"[8] SAVING MODEL ARTIFACTS...", file=sys.stderr)
        start_step = time.time()
        
        model_dir = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private')
        os.makedirs(model_dir, exist_ok=True)
        
        with open(os.path.join(model_dir, 'svm_model.pkl'), 'wb') as f:
            pickle.dump(svm, f)
        
        with open(os.path.join(model_dir, 'tfidf_vectorizer.pkl'), 'wb') as f:
            pickle.dump(tfidf, f)
        
        stage_times['save_artifacts'] = time.time() - start_step
        print(f"✓ Model saved to: {model_dir}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['save_artifacts']:.2f}s\n", file=sys.stderr)
        
        # ========== TIMING SUMMARY ==========
        total_time = time.time() - start_total
        print("=" * 70, file=sys.stderr)
        print("⏱️  TIMING SUMMARY", file=sys.stderr)
        print("=" * 70, file=sys.stderr)
        for stage, elapsed in sorted(stage_times.items()):
            percentage = (elapsed / total_time * 100) if total_time > 0 else 0
            print(f"  {stage.upper():25} {elapsed:7.2f}s  ({percentage:5.1f}%)", file=sys.stderr)
        print(f"  {'TOTAL':25} {total_time:7.2f}s  (100.0%)", file=sys.stderr)
        print("=" * 70, file=sys.stderr)
        
        # ========== Return Results ==========
        result = {
            'success': True,
            'message': f'Model training completed in {total_time:.2f}s',
            'data': {
                'total_samples': len(df),
                'train_samples': len(X_train),
                'test_samples': len(X_test),
                'features': X_train_tfidf.shape[1],
                'classes': sorted(svm.classes_.tolist()),
                'kernel': kernel,
                'timestamp': datetime.now().isoformat(),
                'total_time': total_time,
                'preprocessing_source': 'database_cached'
            },
            'evaluation': {
                'accuracy': float(accuracy),
                'precision': float(precision),
                'recall': float(recall),
                'f1_score': float(f1),
                'confusion_matrix': cm.tolist(),
                'per_class_metrics': per_class_metrics
            },
            'evaluation_result': {
                'accuracy': float(accuracy),
                'precision': float(precision),
                'recall': float(recall),
                'f1_score': float(f1),
                'confusion_matrix': cm.tolist(),
                'per_class_metrics': per_class_metrics
            },
            'stage_times': stage_times
        }
        
        return result
        
    except Exception as e:
        import traceback
        print(f"❌ ERROR: {str(e)}", file=sys.stderr)
        print(traceback.format_exc(), file=sys.stderr)
        
        return {
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc()
        }


if __name__ == '__main__':
    import argparse
    parser = argparse.ArgumentParser(description='Train SVM Sentiment Analysis Model (EXACT COLAB)')
    parser.add_argument('--kernel', type=str, default='rbf',
                       help='SVM kernel type (default: rbf for accuracy match with Colab)',
                       choices=['linear', 'rbf', 'polynomial', 'sigmoid'])
    parser.add_argument('--test_size', type=int, default=10,
                       help='Test size percentage (default: 10 for 90:10 split)')
    
    args = parser.parse_args()
    
    result = train_model_optimized(kernel=args.kernel, test_size=args.test_size)
    
    # Print result as JSON for controller to parse
    print(json.dumps(result, ensure_ascii=False, indent=2))
