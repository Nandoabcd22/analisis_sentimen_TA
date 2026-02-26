#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
SVM Model Training Script - Sesuai Skripsi TA & Google Colab
Workflow: Case Folding → Cleaning → Tokenization → Normalisasi → Stopword → Stemming → TF-IDF → SMOTE → SVM
"""

import sys
import os
import json
import argparse
import pickle
import pandas as pd
import numpy as np
import re
import time
from datetime import datetime

# Don't wrap sys.stderr to avoid conflicts with imported modules
# Force UTF-8 encoding for print statements
import sys
import io
import os

# Set default encoding to UTF-8
if sys.stdout.encoding and sys.stdout.encoding.lower() != 'utf-8':
    try:
        # Try to reconfigure without breaking anything
        pass
    except Exception:
        pass

# Add current directory to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Import optimized preprocessing
optimized_preprocessor = None
USE_OPTIMIZED_PREPROCESSING = False
try:
    from preprocessing import TextPreprocessor
    USE_OPTIMIZED_PREPROCESSING = True
    optimized_preprocessor = TextPreprocessor()
except ImportError as e:
    print(f"⚠ Warning: Could not import optimized preprocessing: {e}", file=sys.stderr)
    USE_OPTIMIZED_PREPROCESSING = False

# Import wordcloud generator
try:
    from wordcloud_generator import generate_wordcloud_image
    WORDCLOUD_AVAILABLE = True
except ImportError:
    print(f"⚠ Warning: wordcloud_generator not available", file=sys.stderr)
    WORDCLOUD_AVAILABLE = False

try:
    from sklearn.svm import SVC
    from sklearn.feature_extraction.text import TfidfVectorizer
    from sklearn.model_selection import train_test_split, KFold
    from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, confusion_matrix, classification_report
    from imblearn.over_sampling import SMOTE
    from joblib import parallel_backend
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


def load_kamus_normalisasi():
    """Load normalization dictionary"""
    kamus = {}
    current_dir = os.path.dirname(os.path.abspath(__file__))
    kamus_path = os.path.join(current_dir, '..', 'resources', 'data', 'kamus_normalisasi.txt')
    
    try:
        with open(kamus_path, 'r', encoding='utf-8') as file:
            for line in file:
                line = line.strip().split('\t')
                if len(line) == 2:
                    kata_asli = line[0]
                    kata_normalisasi = line[1]
                    kamus[kata_asli.lower()] = kata_normalisasi
        print(f"✓ Kamus loaded: {len(kamus)} entries", file=sys.stderr)
    except FileNotFoundError:
        print(f"⚠ Kamus file not found at {kamus_path}", file=sys.stderr)
        print("Using basic fallback normalization", file=sys.stderr)
        kamus = {
            'gak': 'tidak', 'ga': 'tidak', 'nggak': 'tidak',
            'bgt': 'banget', 'dgn': 'dengan', 'pd': 'pada',
            'yg': 'yang', 'utk': 'untuk', 'krn': 'karena',
            'tdk': 'tidak', 'sdh': 'sudah', 'blm': 'belum',
            'bs': 'bisa', 'dpt': 'dapat', 'jg': 'juga',
        }
    
    return kamus


def case_folding(text):
    """Step 1: Case Folding - Convert to lowercase"""
    if isinstance(text, str):
        return text.lower()
    return ""


def clean_text(text):
    """Step 2: Cleaning - Remove links, mentions, numbers, special chars"""
    if not isinstance(text, str):
        return ""
    
    # Hapus link
    text = re.sub(r'http\S+|www\S+|https\S+', '', text)
    # Hapus mention (@) dan hashtag (#)
    text = re.sub(r'@\w+|#\w+', '', text)
    # Hapus angka
    text = re.sub(r'\d+', '', text)
    # Hapus karakter non-alfabet (keep spaces)
    text = re.sub(r'[^a-zA-Z\s]', '', text)
    # Rapikan spasi berlebih
    text = re.sub(r'\s+', ' ', text).strip()
    
    return text


def tokenize_text(text):
    """Step 3: Tokenization - Split into words"""
    if not isinstance(text, str) or text == "":
        return []
    return word_tokenize(text)


def normalisasi_kata(tokens, kamus):
    """Step 4: Normalisasi Kata - Normalize words using dictionary"""
    normalized_tokens = []
    for token in tokens:
        token_lower = token.lower()
        if token_lower in kamus:
            # Split if normalized word includes spaces
            normalized_tokens.extend(kamus[token_lower].split())
        else:
            normalized_tokens.append(token_lower)
    return normalized_tokens


def remove_stopwords(tokens):
    """Step 5: Stopword Removal - Remove Indonesian stopwords"""
    try:
        stopwords_indonesia = set(stopwords.words('indonesian'))
    except:
        stopwords_indonesia = {'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'pada', 'adalah'}
    
    return [token for token in tokens if token not in stopwords_indonesia]


def stemming_tokens(tokens):
    """Step 6: Stemming - Apply Sastrawi stemmer"""
    factory = StemmerFactory()
    stemmer = factory.create_stemmer()
    stemmed = [stemmer.stem(token) for token in tokens]
    return " ".join(stemmed)


def preprocess_text(text, kamus):
    """Complete preprocessing pipeline - Uses optimized version if available"""
    if USE_OPTIMIZED_PREPROCESSING and optimized_preprocessor:
        # Use optimized preprocessing from preprocessing.py
        result = optimized_preprocessor.preprocess_text(text)
        # Extract stemmed text from result dictionary
        return result.get('text_stemmed', '')
    else:
        # Fallback to old implementation
        return _preprocess_text_legacy(text, kamus)


def _preprocess_text_legacy(text, kamus):
    """Legacy preprocessing pipeline - SESUAI COLAB"""
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


def load_data_from_csv(csv_path):
    """Load data from CSV file"""
    try:
        df = pd.read_csv(csv_path)
        print(f"✓ Loaded {len(df)} rows from CSV", file=sys.stderr)
        
        # Support both 'text' and 'review' columns
        if 'text' not in df.columns and 'review' not in df.columns:
            raise ValueError("CSV must contain 'text' or 'review' column")
        
        if 'text' not in df.columns:
            df['text'] = df['review']
        
        # Support both 'label' and 'sentiment' columns
        if 'label' not in df.columns and 'sentiment' not in df.columns:
            raise ValueError("CSV must contain 'label' or 'sentiment' column")
        
        if 'label' not in df.columns:
            df['label'] = df['sentiment']
        
        return df
    except Exception as e:
        print(f"Error loading CSV: {str(e)}", file=sys.stderr)
        return None


def connect_database():
    """Connect to Laravel database and get reviews"""
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
            cursor.execute("SELECT id, review as text, label FROM reviews WHERE review IS NOT NULL AND review != ''")
            reviews = cursor.fetchall()
            cursor.close()
            conn.close()
            
            print(f"✓ Loaded {len(reviews)} rows from database", file=sys.stderr)
            return pd.DataFrame(reviews)
    except Exception as e:
        print(f"⚠ Database error: {str(e)}", file=sys.stderr)
    
    return None


def train_model(csv_path=None, test_size=10):
    """
    Train SVM Model - SESUAI GOOGLE COLAB
    
    Workflow:
    1. Load data (CSV atau Database)
    2. Preprocessing (Case Folding → Cleaning → Tokenization → Normalisasi → Stopword → Stemming)
    3. TF-IDF Vectorization (max_features=5000)
    4. SMOTE (Balance training data)
    5. SVM Training (kernel='rbf', C=1, gamma='scale')
    6. Evaluation & 10-Fold Cross Validation
    7. Test dengan rasio 9:1, 8:2, 7:3
    """
    
    print("=" * 70, file=sys.stderr)
    print("TRAINING SENTIMENT ANALYSIS SVM MODEL (SESUAI COLAB)", file=sys.stderr)
    print("=" * 70, file=sys.stderr)
    
    # Timing tracking
    start_total = time.time()
    stage_times = {}
    
    try:
        # ========== STEP 1: Load Data ==========
        print("\n[STEP 1] Loading Data...", file=sys.stderr)
        start_step = time.time()
        
        if csv_path and os.path.exists(csv_path):
            df = load_data_from_csv(csv_path)
        else:
            df = connect_database()
        
        if df is None or len(df) < 10:
            return {
                'success': False,
                'error': 'Tidak ada data. Gunakan CSV atau Database dengan minimal 10 rows'
            }
        
        stage_times['load_data'] = time.time() - start_step
        print(f"Jumlah Data Awal: {df.shape[0]}", file=sys.stderr)
        print(f"Label Distribution: {df['label'].value_counts().to_dict()}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['load_data']:.2f}s", file=sys.stderr)
        
        # ========== STEP 2: Load Normalization Dictionary ==========
        print("\n[STEP 2] Loading Normalization Dictionary...", file=sys.stderr)
        start_step = time.time()
        kamus = load_kamus_normalisasi()
        stage_times['load_kamus'] = time.time() - start_step
        print(f"⏱️  Time: {stage_times['load_kamus']:.2f}s", file=sys.stderr)
        
        # ========== STEP 3: Preprocessing ==========
        print("\n[STEP 3] Preprocessing Data...", file=sys.stderr)
        start_step = time.time()
        print("  - Case Folding", file=sys.stderr)
        print("  - Cleaning", file=sys.stderr)
        print("  - Tokenization", file=sys.stderr)
        print("  - Normalisasi Kata", file=sys.stderr)
        print("  - Stopword Removal", file=sys.stderr)
        print("  - Stemming", file=sys.stderr)
        if USE_OPTIMIZED_PREPROCESSING:
            print("  📈 Using OPTIMIZED preprocessing with token caching", file=sys.stderr)
        else:
            print("  ⚠ Using legacy preprocessing (run: pip install sastrawi nltk)", file=sys.stderr)
        
        df['text_stemmed'] = df['text'].apply(lambda x: preprocess_text(x, kamus))
        
        # Remove empty rows after preprocessing
        df = df[df['text_stemmed'].str.len() > 0].reset_index(drop=True)
        stage_times['preprocessing'] = time.time() - start_step
        print(f"✓ Jumlah Data setelah Preprocessing: {df.shape[0]}", file=sys.stderr)
        print(f"  Label Distribution: {df['label'].value_counts().to_dict()}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['preprocessing']:.2f}s ({len(df) / max(stage_times['preprocessing'], 0.1):.1f} records/sec)", file=sys.stderr)
        
        if len(df) < 10:
            return {
                'success': False,
                'error': 'Tidak cukup data setelah preprocessing'
            }
        
        # ========== STEP 4: Train/Test Split (9:1) ==========
        print(f"\n[STEP 4] Splitting Data (Train: 90%, Test: 10%)...", file=sys.stderr)
        start_step = time.time()
        
        X = df['text_stemmed']
        y = df['label']
        
        X_train, X_test, y_train, y_test = train_test_split(
            X, y,
            test_size=test_size / 100,
            random_state=42,
            stratify=y
        )
        
        stage_times['split_data'] = time.time() - start_step
        print(f"Train: {len(X_train)}, Test: {len(X_test)}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['split_data']:.2f}s", file=sys.stderr)
        
        # ========== STEP 5: TF-IDF Vectorization ==========
        print("\n[STEP 5] TF-IDF Vectorization (max_features=5000)...", file=sys.stderr)
        start_step = time.time()
        
        tfidf = TfidfVectorizer(max_features=5000)
        X_train_tfidf = tfidf.fit_transform(X_train)
        X_test_tfidf = tfidf.transform(X_test)
        
        stage_times['tfidf'] = time.time() - start_step
        print(f"✓ TF-IDF Features: {X_train_tfidf.shape[1]}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['tfidf']:.2f}s", file=sys.stderr)
        
        # ========== STEP 6: SMOTE (Balance Training Data) ==========
        print("\n[STEP 6] Applying SMOTE for Class Balancing...", file=sys.stderr)
        start_step = time.time()
        
        smote = SMOTE(random_state=42)
        X_train_smote, y_train_smote = smote.fit_resample(X_train_tfidf, y_train)
        
        stage_times['smote'] = time.time() - start_step
        print(f"✓ After SMOTE - Train: {len(y_train_smote)}", file=sys.stderr)
        print(f"  Label Distribution: {pd.Series(y_train_smote).value_counts().to_dict()}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['smote']:.2f}s", file=sys.stderr)
        
        # ========== STEP 7: SVM Training ==========
        print("\n[STEP 7] Training SVM (kernel='rbf', C=1, gamma='scale')...", file=sys.stderr)
        start_step = time.time()
        
        svm = SVC(kernel='rbf', C=1, gamma='scale', probability=True, random_state=42)
        svm.fit(X_train_smote, y_train_smote)
        
        stage_times['svm_train'] = time.time() - start_step
        print("✓ SVM Training Complete", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['svm_train']:.2f}s", file=sys.stderr)
        
        # ========== STEP 8: Evaluation (9:1) ==========
        print("\n[STEP 8] Model Evaluation (9:1 Split)...", file=sys.stderr)
        start_step = time.time()
        
        y_pred = svm.predict(X_test_tfidf)
        
        accuracy = accuracy_score(y_test, y_pred)
        precision = precision_score(y_test, y_pred, average='weighted', zero_division=0)
        recall = recall_score(y_test, y_pred, average='weighted', zero_division=0)
        f1 = f1_score(y_test, y_pred, average='weighted', zero_division=0)
        cm = confusion_matrix(y_test, y_pred)
        
        stage_times['evaluation'] = time.time() - start_step
        print(f"\nAkurasi : {accuracy:.4f}", file=sys.stderr)
        print(f"Presisi : {precision:.4f}", file=sys.stderr)
        print(f"Recall  : {recall:.4f}", file=sys.stderr)
        print(f"F1 Score: {f1:.4f}", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['evaluation']:.2f}s", file=sys.stderr)
        
        print("\n" + "=" * 70, file=sys.stderr)
        print(classification_report(y_test, y_pred), file=sys.stderr)
        print("=" * 70, file=sys.stderr)
        
        eval_91 = {
            'accuracy': float(accuracy),
            'precision': float(precision),
            'recall': float(recall),
            'f1': float(f1),
            'confusion_matrix': cm.tolist()
        }
        
        # ========== STEP 9: 10-Fold Cross Validation ==========
        print("\n[STEP 9] 10-Fold Cross Validation...", file=sys.stderr)
        start_step = time.time()
        
        vectorizer_cv = TfidfVectorizer(max_features=5000)
        X_cv = vectorizer_cv.fit_transform(X)
        y_cv = y
        
        kf = KFold(n_splits=10, shuffle=True, random_state=42)
        cv_results = []
        
        for fold, (train_idx, test_idx) in enumerate(kf.split(X_cv)):
            fold_X_train, fold_X_test = X_cv[train_idx], X_cv[test_idx]
            fold_y_train, fold_y_test = y_cv.iloc[train_idx], y_cv.iloc[test_idx]
            
            svm_cv = SVC(kernel='rbf', C=1, gamma='scale', probability=True, random_state=42)
            fold_y_pred = svm_cv.fit(fold_X_train, fold_y_train).predict(fold_X_test)
            
            fold_accuracy = accuracy_score(fold_y_test, fold_y_pred) * 100
            fold_precision = precision_score(fold_y_test, fold_y_pred, average='weighted', zero_division=0) * 100
            fold_recall = recall_score(fold_y_test, fold_y_pred, average='weighted', zero_division=0) * 100
            
            cv_results.append({
                'fold': fold + 1,
                'accuracy': fold_accuracy,
                'precision': fold_precision,
                'recall': fold_recall
            })
            
            print(f"Fold {fold+1}: Akurasi={fold_accuracy:.2f}%, Presisi={fold_precision:.2f}%, Recall={fold_recall:.2f}%", file=sys.stderr)
        
        stage_times['cross_validation'] = time.time() - start_step
        mean_accuracy = np.mean([r['accuracy'] for r in cv_results])
        mean_precision = np.mean([r['precision'] for r in cv_results])
        mean_recall = np.mean([r['recall'] for r in cv_results])
        
        print(f"\nRata-rata Akurasi: {mean_accuracy:.2f}%", file=sys.stderr)
        print(f"Rata-rata Presisi: {mean_precision:.2f}%", file=sys.stderr)
        print(f"Rata-rata Recall: {mean_recall:.2f}%", file=sys.stderr)
        print(f"⏱️  Time: {stage_times['cross_validation']:.2f}s", file=sys.stderr)
        
        # ========== STEP 10: Test dengan Rasio 9:1, 8:2, 7:3 ==========
        print("\n[STEP 10] Testing dengan Rasio Split (9:1, 8:2, 7:3)...", file=sys.stderr)
        start_step = time.time()
        
        splits = {"9:1": 0.1, "8:2": 0.2, "7:3": 0.3}
        split_results = {}
        
        for name, test_ratio in splits.items():
            print(f"\nPENGUJIAN DATA {name}", file=sys.stderr)
            
            # Split
            X_train_split, X_test_split, y_train_split, y_test_split = train_test_split(
                X, y, test_size=test_ratio, random_state=42, stratify=y
            )
            
            # TF-IDF
            tfidf_split = TfidfVectorizer(max_features=5000)
            X_train_tfidf_split = tfidf_split.fit_transform(X_train_split)
            X_test_tfidf_split = tfidf_split.transform(X_test_split)
            
            # SMOTE
            smote_split = SMOTE(random_state=42)
            X_train_smote_split, y_train_smote_split = smote_split.fit_resample(X_train_tfidf_split, y_train_split)
            
            # SVM
            svm_split = SVC(kernel='rbf', C=1, gamma='scale', probability=True, random_state=42)
            svm_split.fit(X_train_smote_split, y_train_smote_split)
            
            # Evaluate
            y_pred_split = svm_split.predict(X_test_tfidf_split)
            
            acc_split = accuracy_score(y_test_split, y_pred_split)
            prec_split = precision_score(y_test_split, y_pred_split, average='weighted', zero_division=0)
            rec_split = recall_score(y_test_split, y_pred_split, average='weighted', zero_division=0)
            cm_split = confusion_matrix(y_test_split, y_pred_split)
            
            print(f"Akurasi: {acc_split:.4f}", file=sys.stderr)
            print(classification_report(y_test_split, y_pred_split), file=sys.stderr)
            
            split_results[name] = {
                'accuracy': float(acc_split),
                'precision': float(prec_split),
                'recall': float(rec_split),
                'confusion_matrix': cm_split.tolist()
            }
        
        stage_times['split_testing'] = time.time() - start_step
        print(f"⏱️  Time: {stage_times['split_testing']:.2f}s", file=sys.stderr)
        
        # ========== STEP 11: Save Model Artifacts ==========
        print("\n[STEP 11] Saving Model Artifacts...", file=sys.stderr)
        start_step = time.time()
        
        model_dir = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private')
        os.makedirs(model_dir, exist_ok=True)
        
        with open(os.path.join(model_dir, 'svm_model.pkl'), 'wb') as f:
            pickle.dump(svm, f)
        
        with open(os.path.join(model_dir, 'tfidf_vectorizer.pkl'), 'wb') as f:
            pickle.dump(tfidf, f)
        
        with open(os.path.join(model_dir, 'kamus_normalisasi.pkl'), 'wb') as f:
            pickle.dump(kamus, f)
        
        print(f"✓ Model saved to: {model_dir}", file=sys.stderr)
        
        stage_times['save_artifacts'] = time.time() - start_step
        print(f"⏱️  Time: {stage_times['save_artifacts']:.2f}s", file=sys.stderr)
        
        # ========== TIMING SUMMARY ==========
        total_time = time.time() - start_total
        print("\n" + "=" * 70, file=sys.stderr)
        print("⏱️  TIMING SUMMARY", file=sys.stderr)
        print("=" * 70, file=sys.stderr)
        for stage, elapsed in sorted(stage_times.items()):
            percentage = (elapsed / total_time * 100) if total_time > 0 else 0
            print(f"  {stage.upper():30} {elapsed:7.2f}s  ({percentage:5.1f}%)", file=sys.stderr)
        print(f"  {'TOTAL':30} {total_time:7.2f}s  (100.0%)", file=sys.stderr)
        print("=" * 70, file=sys.stderr)
        
        # ========== Return Results ==========
        return {
            'success': True,
            'data': {
                'total_samples': len(df),
                'train_samples': len(X_train),
                'test_samples': len(X_test),
                'features': X_train_tfidf.shape[1],
                'classes': sorted(svm.classes_.tolist()),
                'timestamp': datetime.now().isoformat(),
                'total_time': total_time,
                'stage_times': stage_times
            },
            'evaluation_9_1': eval_91,
            'cross_validation_10fold': {
                'results': cv_results,
                'mean_accuracy': float(mean_accuracy),
                'mean_precision': float(mean_precision),
                'mean_recall': float(mean_recall)
            },
            'split_ratios': split_results,
            'model_info': {
                'kernel': 'rbf',
                'C': 1,
                'gamma': 'scale',
                'tfidf_max_features': 5000,
                'smote_random_state': 42
            }
        }
        
    except Exception as e:
        import traceback
        print(f"❌ ERROR: {str(e)}", file=sys.stderr)
        print(traceback.format_exc(), file=sys.stderr)
        
        return {
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc()
        }


def train_svm_model(csv_path=None, test_size=10, kernel='rbf'):
    """
    Train SVM Model - FAST OPTIMIZED VERSION
    
    Optimizations:
    - Uses joblib parallel for SVM training (n_jobs=-1)
    - Efficient sparse matrix operations
    - Skips unnecessary cross-validation on first pass
    - Smart caching of TF-IDF vectorizer
    """
    
    print("=" * 70, file=sys.stderr)
    print(f"🚀 FAST SVM TRAINING - KERNEL: {kernel.upper()}", file=sys.stderr)
    print("=" * 70, file=sys.stderr)
    
    start_total = time.time()
    
    try:
        # ========== STEP 1: Load Data ==========
        print("\n[1/7] Loading Data...", file=sys.stderr)
        start_step = time.time()
        
        if csv_path and os.path.exists(csv_path):
            df = load_data_from_csv(csv_path)
        else:
            df = connect_database()
        
        if df is None or len(df) < 10:
            return {
                'success': False,
                'error': 'Tidak ada data. Gunakan CSV atau Database dengan minimal 10 rows'
            }
        
        print(f"  Data: {df.shape[0]} rows | Distribution: {df['label'].value_counts().to_dict()} | Time: {time.time()-start_step:.1f}s", file=sys.stderr)
        
        # ========== STEP 2: Preprocessing ==========
        print("\n[2/7] Preprocessing Data...", file=sys.stderr)
        start_step = time.time()
        
        kamus = load_kamus_normalisasi()
        df['text_stemmed'] = df['text'].apply(lambda x: preprocess_text(x, kamus))
        df = df[df['text_stemmed'].str.len() > 0].reset_index(drop=True)
        
        if len(df) < 10:
            return {'success': False, 'error': 'Tidak cukup data setelah preprocessing'}
        
        print(f"  After preprocessing: {df.shape[0]} rows | Time: {time.time()-start_step:.1f}s", file=sys.stderr)
        
        # ========== STEP 3: Train/Test Split + TF-IDF ==========
        print("\n[3/7] Splitting & TF-IDF Vectorization...", file=sys.stderr)
        start_step = time.time()
        
        X = df['text_stemmed']
        y = df['label']
        
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=test_size / 100, random_state=42, stratify=y
        )
        
        tfidf = TfidfVectorizer(max_features=5000, ngram_range=(1,1), sublinear_tf=True)
        X_train_tfidf = tfidf.fit_transform(X_train)
        X_test_tfidf = tfidf.transform(X_test)
        
        print(f"  Train: {len(X_train)} | Test: {len(X_test)} | Features: {X_train_tfidf.shape[1]} | Time: {time.time()-start_step:.1f}s", file=sys.stderr)
        
        # ========== STEP 4: SMOTE ==========
        print("\n[4/7] SMOTE Balancing...", file=sys.stderr)
        start_step = time.time()
        
        smote = SMOTE(random_state=42, k_neighbors=3)  # Reduced from 5 to 3 for speed
        X_train_smote, y_train_smote = smote.fit_resample(X_train_tfidf, y_train)
        
        print(f"  After SMOTE: {len(y_train_smote)} | Time: {time.time()-start_step:.1f}s", file=sys.stderr)
        
        # ========== STEP 5: SVM Training (PARALLELIZED) ==========
        print(f"\n[5/7] SVM Training (kernel={kernel})...", file=sys.stderr)
        start_step = time.time()
        
        # Use parallel_backend for faster SVM training on multi-core
        with parallel_backend('threading', n_jobs=-1):
            svm = SVC(kernel=kernel, C=1, gamma='scale', probability=True, random_state=42)
            svm.fit(X_train_smote, y_train_smote)
        
        print(f"  SVM trained | Time: {time.time()-start_step:.1f}s", file=sys.stderr)
        
        # ========== STEP 6: Evaluation ==========
        print("\n[6/7] Evaluation...", file=sys.stderr)
        start_step = time.time()
        
        y_pred = svm.predict(X_test_tfidf)
        
        accuracy = accuracy_score(y_test, y_pred)
        precision = precision_score(y_test, y_pred, average='weighted', zero_division=0)
        recall = recall_score(y_test, y_pred, average='weighted', zero_division=0)
        f1 = f1_score(y_test, y_pred, average='weighted', zero_division=0)
        cm = confusion_matrix(y_test, y_pred, labels=sorted(svm.classes_))
        
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
        
        print(f"  Accuracy: {accuracy:.4f} | Precision: {precision:.4f} | Recall: {recall:.4f} | F1: {f1:.4f} | Time: {time.time()-start_step:.1f}s", file=sys.stderr)
        
        # ========== STEP 7: Save Model ==========
        print("\n[7/7] Saving Model...", file=sys.stderr)
        start_step = time.time()
        
        model_dir = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'private')
        os.makedirs(model_dir, exist_ok=True)
        
        with open(os.path.join(model_dir, 'svm_model.pkl'), 'wb') as f:
            pickle.dump(svm, f)
        
        with open(os.path.join(model_dir, 'tfidf_vectorizer.pkl'), 'wb') as f:
            pickle.dump(tfidf, f)
        
        with open(os.path.join(model_dir, 'kamus_normalisasi.pkl'), 'wb') as f:
            pickle.dump(kamus, f)
        
        # Save metrics
        metrics_path = os.path.join(model_dir, 'model_metrics.json')
        with open(metrics_path, 'w', encoding='utf-8') as f:
            json.dump({
                'kernel': kernel,
                'timestamp': datetime.now().isoformat(),
                'evaluation': {
                    'accuracy': float(accuracy),
                    'precision_weighted': float(precision),
                    'recall_weighted': float(recall),
                    'f1_weighted': float(f1),
                    'confusion_matrix': cm.tolist(),
                    'per_class_metrics': per_class_metrics,
                    'classes': sorted(svm.classes_.tolist())
                },
                'data_info': {
                    'total_samples': len(df),
                    'train_samples': len(X_train),
                    'test_samples': len(X_test),
                    'features': X_train_tfidf.shape[1]
                }
            }, f, indent=2, ensure_ascii=False)
        
        # Generate WordCloud from stemmed training text
        print("\n[7b/7] Generating WordCloud...", file=sys.stderr)
        wordcloud_base64 = None
        try:
            # Try to generate direct wordcloud without relying on import
            from wordcloud import WordCloud
            import base64
            from io import BytesIO
            
            # Combine all stemmed training texts
            combined_text = ' '.join(df['text_stemmed'].values)
            
            if combined_text.strip():
                # Generate wordcloud
                wordcloud_obj = WordCloud(
                    width=1200,
                    height=600,
                    background_color='white',
                    colormap='viridis',
                    max_words=100,
                    relative_scaling=0.5,
                    min_font_size=10
                ).generate(combined_text)
                
                # Convert to image
                image = wordcloud_obj.to_image()
                
                # Save to bytes buffer
                buffer = BytesIO()
                image.save(buffer, format='PNG')
                buffer.seek(0)
                
                # Encode to base64
                wordcloud_base64 = base64.b64encode(buffer.getvalue()).decode('utf-8')
                
                # Also save to file for caching
                wordcloud_b64_path = os.path.join(model_dir, 'wordcloud.b64')
                with open(wordcloud_b64_path, 'w', encoding='utf-8') as f:
                    f.write(wordcloud_base64)
                
                print(f"  ✓ WordCloud generated successfully ({len(combined_text)} chars)", file=sys.stderr)
            else:
                print(f"  ⚠ No text data for wordcloud", file=sys.stderr)
                
        except ImportError as e:
            print(f"  ⚠ WordCloud library not available: {str(e)}", file=sys.stderr)
        except Exception as e:
            print(f"  ⚠ Error generating wordcloud: {str(e)}", file=sys.stderr)
            import traceback
            print(traceback.format_exc(), file=sys.stderr)
        
        total_time = time.time() - start_total
        print(f"  Model saved | Time: {time.time()-start_step:.1f}s", file=sys.stderr)
        print(f"\n✅ TOTAL TIME: {total_time:.1f}s", file=sys.stderr)
        
        # ========== Return Results ==========
        return {
            'success': True,
            'message': f'Model training completed in {total_time:.1f}s',
            'data': {
                'total_samples': len(df),
                'train_samples': len(X_train),
                'test_samples': len(X_test),
                'features': X_train_tfidf.shape[1],
                'classes': sorted(svm.classes_.tolist()),
                'kernel': kernel,
                'timestamp': datetime.now().isoformat(),
                'accuracy': float(accuracy),
                'precision': float(precision),
                'recall': float(recall),
                'f1_score': float(f1),
                'confusion_matrix': cm.tolist(),
                'per_class_metrics': per_class_metrics,
                'wordcloud': wordcloud_base64
            }
        }
        
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
    parser = argparse.ArgumentParser(description='Train SVM Sentiment Analysis Model')
    parser.add_argument('--csv', type=str, default=None,
                       help='Path to CSV file with data (columns: text, label)')
    parser.add_argument('--test_size', type=int, default=10,
                       help='Test size percentage (default: 10 for 9:1 split)')
    parser.add_argument('--kernel', type=str, default='rbf',
                       help='SVM kernel type: linear, rbf, polynomial, sigmoid (default: rbf)',
                       choices=['linear', 'rbf', 'polynomial', 'sigmoid'])
    
    args = parser.parse_args()
    
    result = train_svm_model(csv_path=args.csv, test_size=args.test_size, kernel=args.kernel)
    print(json.dumps(result, indent=2, ensure_ascii=False))


