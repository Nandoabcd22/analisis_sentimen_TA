#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Test Training Script - Untuk debugging training dari command line
"""

import sys
import os
import json

# Add current directory to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

print("=" * 70)
print("SENTIMENT ANALYSIS - TEST TRAINING SCRIPT")
print("=" * 70)

# Step 1: Check Python packages
print("\n[STEP 1] Memeriksa Python packages...")

packages_required = {
    'pandas': 'Data processing',
    'numpy': 'Numerical computing',
    'nltk': 'NLP tokenization',
    'sklearn': 'Machine learning (scikit-learn)',
    'Sastrawi': 'Indonesian stemming',
    'imblearn': 'SMOTE (imbalanced-learn)',
    'mysql.connector': 'MySQL connector'
}

missing_packages = []

for package, description in packages_required.items():
    try:
        __import__(package)
        print(f"  ✓ {package:20} - {description}")
    except ImportError:
        print(f"  ✗ {package:20} - {description} [MISSING]")
        missing_packages.append(package)

if missing_packages:
    print(f"\n⚠  Missing packages: {', '.join(missing_packages)}")
    print("\nInstall with:")
    print("  pip install -r requirements.txt")
    sys.exit(1)

print("\n✓ All required packages installed!")

# Step 2: Check file paths
print("\n[STEP 2] Memeriksa file paths...")

base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
paths_to_check = {
    'scripts/train_model.py': 'Training script',
    'scripts/predict_sentiment.py': 'Prediction script',
    'scripts/preprocessing.py': 'Preprocessing utilities',
    'resources/data/kamus_normalisasi.txt': 'Normalization dictionary',
    'storage/app/private': 'Model storage directory',
}

missing_paths = []

for path, description in paths_to_check.items():
    full_path = os.path.join(base_dir, path)
    exists = os.path.exists(full_path)
    status = "✓" if exists else "✗"
    print(f"  {status} {path:40} - {description}")
    if not exists:
        missing_paths.append(path)

if missing_paths:
    print(f"\n⚠  Missing paths: {', '.join(missing_paths)}")
    sys.exit(1)

print("\n✓ All required file paths exist!")

# Step 3: Test importing train_model module
print("\n[STEP 3] Testing train_model module...")

try:
    from train_model import train_model, load_kamus_normalisasi, preprocess_text
    print("  ✓ Successfully imported train_model module")
    print("  ✓ Successfully imported functions")
except Exception as e:
    print(f"  ✗ Failed to import: {str(e)}")
    sys.exit(1)

# Step 4: Test kamus loading
print("\n[STEP 4] Testing kamus normalisasi...")

try:
    kamus = load_kamus_normalisasi()
    print(f"  ✓ Loaded kamus with {len(kamus)} entries")
except Exception as e:
    print(f"  ✗ Failed to load kamus: {str(e)}")
    sys.exit(1)

# Step 5: Test preprocessing
print("\n[STEP 5] Testing preprocessing pipeline...")

test_text = "Produk ini sangat bagus sekali dan memuaskan!"

try:
    result = preprocess_text(test_text, kamus)
    print(f"  Input:  {test_text}")
    print(f"  Output: {result}")
    print(f"  ✓ Preprocessing works!")
except Exception as e:
    print(f"  ✗ Preprocessing failed: {str(e)}")
    sys.exit(1)

# Step 6: Database connection test
print("\n[STEP 6] Testing database connection...")

try:
    import mysql.connector
    from mysql.connector import Error
    
    try:
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='',
            database='analisis_sentimen_ta'
        )
        
        if conn.is_connected():
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT COUNT(*) as count FROM reviews")
            result = cursor.fetchone()
            count = result['count']
            print(f"  ✓ Database connected")
            print(f"  ✓ Found {count} reviews in database")
            cursor.close()
            conn.close()
        else:
            print("  ⚠  Could not connect to database (will use CSV instead)")
    except Error as e:
        if "Unknown database" in str(e):
            print(f"  ⚠  Database 'analisis_sentimen_ta' not found")
            print(f"    You can use CSV for training instead:")
            print(f"    python train_model.py --csv 'data.csv'")
        else:
            print(f"  ⚠  Database error: {str(e)}")

except ImportError:
    print("  ⚠  mysql-connector-python not installed (optional for CSV mode)")

# Summary
print("\n" + "=" * 70)
print("✓ ALL TESTS PASSED!")
print("=" * 70)

print("\n📊 You can now run training with:")
print("  python train_model.py                          (from database)")
print("  python train_model.py --csv 'data.csv'         (from CSV file)")

print("\n🎯 For interactive sentiment prediction:")
print("  python predict_sentiment.py --interactive")

print("\n📖 For more information, see: README.md or QUICK_START.md")
print("\n")
