# 🚀 Quick Start Guide - Sentiment Analysis Backend

Panduan cepat untuk mulai menggunakan sentiment analysis system.

## 1️⃣ Instalasi (First Time)

```bash
cd scripts
pip install -r requirements.txt
```

## 2️⃣ Training Model (Pilih salah satu)

### Option A: Dari Database (Recommended jika sudah ada data)

```bash
python train_model.py
```

### Option B: Dari CSV File

```bash
python train_model.py --csv "path/to/datasetTA.csv" --test_size 10
```

**CSV harus memiliki columns:**
- `text` atau `review` (kolom teks)
- `label` atau `sentiment` (kolom sentimen)

## 3️⃣ Prediksi Sentiment

### Interactive Mode (Paling mudah)

```bash
python predict_sentiment.py --interactive
```

Kemudian ketik text dan tekan Enter untuk mendapat prediksi.

### Direct Text

```bash
python predict_sentiment.py --text "Produk ini sangat bagus sekali!"
```

### Dari File

```bash
python predict_sentiment.py --file "input.txt"
```

---

## 📊 Output Format

**Successful Prediction:**
```json
{
  "success": true,
  "sentiment": "positive",
  "confidence": 0.8745,
  "probabilities": {
    "negative": 0.1255,
    "positive": 0.8745
  }
}
```

**Training Result:**
```json
{
  "success": true,
  "data": {
    "total_samples": 1000,
    "features": 5000,
    "classes": ["negative", "positive"]
  },
  "evaluation_9_1": {
    "accuracy": 0.85,
    "precision": 0.84,
    "recall": 0.85
  },
  "cross_validation_10fold": {
    "mean_accuracy": 82.5
  },
  "split_ratios": {
    "9:1": {"accuracy": 0.85},
    "8:2": {"accuracy": 0.83},
    "7:3": {"accuracy": 0.81}
  }
}
```

---

## 🔧 Preprocessing Steps

Input text diproses melalui 6 tahapan:
1. **Case Folding** → Ubah ke lowercase
2. **Cleaning** → Hapus URL, mention, numbers, special chars
3. **Tokenization** → Pisah menjadi words
4. **Normalisasi** → Gunakan kamus normalisasi
5. **Stopword Removal** → Hapus kata-kata umum
6. **Stemming** → Ubah ke bentuk dasar

---

## 📂 Model Files Location

Model tersimpan di:
```
storage/app/private/
├── svm_model.pkl             # Trained SVM model
├── tfidf_vectorizer.pkl       # TF-IDF vectorizer
└── kamus_normalisasi.pkl      # Normalization dictionary
```

---

## ⚠️ Common Issues

| Error | Solution |
|-------|----------|
| "Model not found" | Jalankan `train_model.py` dulu |
| "No data" | Pastikan CSV atau database ada dan valid |
| "Empty after preprocessing" | Data mungkin hanya special characters |
| "Connection error" | Gunakan CSV file dengan `--csv` option |

---

## 🤖 Model Specifications

- **Algorithm**: SVM (Support Vector Machine)
- **Kernel**: RBF (Radial Basis Function)
- **Vectorization**: TF-IDF (max_features=5000)
- **Balancing**: SMOTE
- **Validation**: 10-Fold Cross Validation
- **Train/Test Splits**: 9:1, 8:2, 7:3

---

## 📞 Support

**Untuk info lebih lengkap: Baca [README.md](README.md)**

---

Generated: 2026-02-18  
Version: 1.0.0 ✓
