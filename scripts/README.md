# 📊 Sentiment Analysis - Python Scripts

Implementasi Sentiment Analysis menggunakan SVM dengan TF-IDF, sesuai dengan Google Colab notebook Anda.

## 📋 Daftar Isi

1. [Instalasi Dependencies](#instalasi-dependencies)
2. [Struktur File](#struktur-file)
3. [Workflow Lengkap](#workflow-lengkap)
4. [Cara Penggunaan](#cara-penggunaan)
5. [Preprocessing Pipeline](#preprocessing-pipeline)
6. [Model Training](#model-training)
7. [Sentiment Prediction](#sentiment-prediction)

---

## 🔧 Instalasi Dependencies

### 1. Setup Python Environment (Optional tapi Recommended)

```bash
# Windows - Buat virtual environment
python -m venv venv
venv\Scripts\activate

# Linux/Mac
python -m venv venv
source venv/bin/activate
```

### 2. Install Dependencies

```bash
cd scripts
pip install -r requirements.txt
```

### 3. Verifikasi Instalasi

```bash
python -c "import sklearn, nltk, pandas; print('✓ All packages installed!')"
```

---

## 📁 Struktur File

```
scripts/
├── train_model.py              # Training SVM model
├── predict_sentiment.py         # Prediksi sentiment untuk input baru
├── preprocessing.py             # Preprocessing utilities
├── requirements.txt             # Python dependencies
├── utilities/                   # Testing & debug scripts
│   ├── check_progress.php
│   ├── test_batch_update.php
│   └── ... (PHP utility scripts)
└── README.md                    # Dokumentasi ini
```

---

## 🚀 Workflow Lengkap

```
1. LOAD DATA
   ↓
2. PREPROCESSING (Case Folding → Cleaning → Tokenization → Normalisasi → Stopword → Stemming)
   ↓
3. TF-IDF VECTORIZATION (max_features=5000)
   ↓
4. SMOTE (Balance Training Data)
   ↓
5. SVM TRAINING (kernel='rbf', C=1, gamma='scale')
   ↓
6. EVALUATION
   ├─ Main Split: 9:1 (Train:Test)
   ├─ 10-Fold Cross Validation
   └─ Multiple Ratios: 9:1, 8:2, 7:3
   ↓
7. SAVE MODEL ARTIFACTS
   ↓
8. PREDICT SENTIMENT (untuk input baru)
```

---

## 📖 Cara Penggunaan

### 1️⃣ Training Model

#### Option A: Dari Database

```bash
python train_model.py
```

Persyaratan: Database `analisis_sentimen_ta` dengan tabel `reviews` (columns: `review`, `label`)

#### Option B: Dari CSV File

```bash
python train_model.py --csv "path/to/data.csv" --test_size 10
```

**CSV Format:**
```
text,label
"Ini review sangat bagus",positive
"Ini review sangat jelek",negative
...
```

Atau dengan kolom `review` dan `sentiment`:
```
review,sentiment
"Tempatnya rapi dan nyaman",positive
...
```

#### Output Training

```json
{
  "success": true,
  "data": {
    "total_samples": 1000,
    "train_samples": 900,
    "test_samples": 100,
    "features": 5000,
    "classes": ["negative", "positive"],
    "timestamp": "2026-02-18T..."
  },
  "evaluation_9_1": {
    "accuracy": 0.85,
    "precision": 0.84,
    "recall": 0.85,
    "f1": 0.84,
    "confusion_matrix": [...]
  },
  "cross_validation_10fold": {
    "results": [...],
    "mean_accuracy": 82.5,
    "mean_precision": 81.2,
    "mean_recall": 82.8
  },
  "split_ratios": {
    "9:1": { "accuracy": 0.85, ... },
    "8:2": { "accuracy": 0.83, ... },
    "7:3": { "accuracy": 0.81, ... }
  }
}
```

**Model disimpan di:**
- `storage/app/private/svm_model.pkl`
- `storage/app/private/tfidf_vectorizer.pkl`
- `storage/app/private/kamus_normalisasi.pkl`

---

### 2️⃣ Prediksi Sentiment

#### Option A: Interactive Mode (Recommended)

```bash
python predict_sentiment.py --interactive
```

```
[INPUT] Masukkan teks: Produk ini berkualitas dan bagus!

[HASIL] Sentiment: positive
[CONFIDENCE] 87.45%

[PROBABILITIES]
  negative           [████░░░░░░░░░░░░░░░░░░░░░░░░] 12.55%
  positive           [██████████████████████████░░░] 87.45%

[INPUT] Masukkan teks: exit
```

#### Option B: Text Argument

```bash
python predict_sentiment.py --text "Ini review yang sangat bagus sekali!"
```

Output JSON:
```json
{
  "success": true,
  "text": "Ini review yang sangat bagus sekali!",
  "processed_text": "bagus",
  "sentiment": "positive",
  "confidence": 0.9145,
  "probabilities": {
    "negative": 0.0855,
    "positive": 0.9145
  },
  "timestamp": "2026-02-18T..."
}
```

#### Option C: File Input

```bash
python predict_sentiment.py --file "input_text.txt"
```

---

## 🔄 Preprocessing Pipeline

Setiap text melalui 6 tahapan preprocessing:

### 1. Case Folding
```
Input:  "Produk INI Sangat BAGUS!"
Output: "produk ini sangat bagus!"
```

### 2. Cleaning
Menghapus:
- URLs: `http://...`, `www...`
- Mentions: `@user`
- Hashtags: `#hashtag`
- Angka: `123`
- Karakter spesial: `!@#$%^&*()`

```
Input:  "Produk ini sangat bagus!!! Check www.example.com #bagus @admin"
Output: "Produk ini sangat bagus"
```

### 3. Tokenization
```
Input:  "Produk ini sangat bagus"
Output: ["Produk", "ini", "sangat", "bagus"]
```

### 4. Normalisasi Kata
Menggunakan kamus di `resources/data/kamus_normalisasi.txt`:
```
Input:  ["gk", "bagus", "banget"]
Output: ["tidak", "bagus", "banget"]
```

### 5. Stopword Removal
Menghapus kata-kata umum yang tidak bermakna:
```
Input:  ["tidak", "bagus", "dan", "sangat"]
Output: ["tidak", "bagus", "sangat"]
```

### 6. Stemming
Mengubah kata ke bentuk dasar:
```
Input:  ["bagus", "ditinggal", "menunggu"]
Output: ["bagus", "tinggal", "tunggu"]
```

---

## 🤖 Model Training Details

### SVM Configuration (Sesuai Colab)

```python
SVC(
    kernel='rbf',           # Radial Basis Function kernel
    C=1,                    # Regularization parameter
    gamma='scale',          # Kernel coefficient
    probability=True,       # Enable probability estimates
    random_state=42         # Deterministic results
)
```

### Preprocessing Configuration

```python
TfidfVectorizer(
    max_features=5000         # Maximum 5000 features
)

SMOTE(
    random_state=42           # Oversample minority class
)
```

### Cross-Validation

```python
KFold(
    n_splits=10,              # 10-fold cross validation
    shuffle=True,             # Shuffle data before split
    random_state=42
)
```

---

## 📊 Sentiment Prediction

Setelah model ditraining, gunakan prediction script untuk:

1. **Terminal Input** - Masukkan text langsung di terminal
2. **File Input** - Prediksi text dari file
3. **Batch Processing** - Prediksi multiple texts dengan custom script

---

## 📊 Understanding Results

### Metrics

- **Accuracy**: Persentase prediksi yang benar
- **Precision**: Dari yang diprediksi positive, berapa persen yang benar positive
- **Recall**: Dari actual positive, berapa persen yang tertangkap
- **F1-Score**: Harmonic mean antara precision dan recall

### Confusion Matrix

```
                Predicted
              Negative | Positive
Actual  |Negative|  TN   |  FP   |
        |Positive|  FN   |  TP   |
```

---

## 🔍 Troubleshooting

### Error: "Model not found"
**Solusi:** Jalankan `train_model.py` terlebih dahulu untuk training model

### Error: "Kamus file not found"
**Solusi:** Pastikan file `resources/data/kamus_normalisasi.txt` ada. Script akan menggunakan fallback basic normalization jika file tidak ada.

### Error: "Not enough data after preprocessing"
**Solusi:** Beberapa text menjadi kosong setelah preprocessing. Pastikan dataset minimal 50+ samples yang valid.

### Error: "MySQL connection error"
**Solusi:** Gunakan `--csv` option untuk training dari CSV file jika database tidak tersedia.

---

## 📝 File Format Details

### requirements.txt

```
pandas>=1.3.0              # Data processing
nltk>=3.6.0                # NLP tokenization & stopwords
Sastrawi>=1.0.1            # Indonesian stemming
numpy>=1.21.0              # Numerical computing
scikit-learn>=0.24.0       # Machine learning
imbalanced-learn>=0.8.0    # SMOTE
mysql-connector-python     # Database connection
```

### kamus_normalisasi.txt Format

```
gak	tidak
nggak	tidak
gk	tidak
bgt	banget
dgn	dengan
yg	yang
...
```

Tab-separated values (TSV): `[kata_asli]\t[kata_normalisasi]`

---

## 🎯 Best Practices

1. **Data Quality**: Bersihkan data sebelum training (remove duplicates, null values)
2. **Balance Data**: Gunakan SMOTE untuk balanced training data
3. **Cross Validation**: Selalu validasi dengan multiple folds
4. **Multiple Splits**: Test dengan berbagai train/test ratios
5. **Monitoring**: Simpan logs untuk tracking performance

---

## 🔗 Integration dengan Laravel

Untuk integration dengan Laravel controller, gunakan:

```php
// In Laravel Controller
$output = shell_exec("python ../scripts/predict_sentiment.py --text '" . escapeshellarg($userText) . "'");
$result = json_decode($output, true);

if ($result['success']) {
    $sentiment = $result['sentiment'];
    $confidence = $result['confidence'];
}
```

---

## 📄 License & Credits

Implementasi sesuai dengan:
- Google Colab Notebook: Sentiment Analysis TA
- Dataset: `datasetTA.csv`
- Framework: Laravel + Python

---

**Last Updated:** 2026-02-18  
**Version:** 1.0.0  
**Status:** Production Ready ✓
