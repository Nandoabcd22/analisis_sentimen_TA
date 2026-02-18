# Backend Fix - Analisis Sentimen TA

## Perubahan yang Dibuat

### 1. **Membuat ClassificationController** 
   - File: `app/Http/Controllers/ClassificationController.php`
   - Fungsi utama:
     - `trainModel()` - Melatih model SVM dengan kernel pilihan
     - `loadModel()` - Memuat model yang sudah tersimpan
     - `predictSentiment()` - Prediksi sentimen untuk text input
     - `getMetrics()` - Mendapatkan metrik model (akurasi, presisi, recall, F1-score)
     - `getConfusionMatrix()` - Mendapatkan confusion matrix
     - `getModelStatus()` - Mengecek status model

### 2. **Menambahkan Routes untuk Klasifikasi**
   - Routes ditambahkan di `routes/web.php`:
     - `POST /api/train-model` - Endpoint untuk melatih model
     - `POST /api/load-model` - Endpoint untuk memuat model
     - `POST /api/predict-sentiment` - Endpoint untuk prediksi sentimen
     - `GET /api/model-metrics` - Mendapatkan metrik model
     - `GET /api/confusion-matrix` - Mendapatkan confusion matrix
     - `GET /api/model-status` - Mengecek status model

### 3. **Update Frontend (klasifikasi.blade.php)**
   - Mengintegrasikan dengan backend API
   - Fungsi JavaScript untuk:
     - `trainModel()` - Memanggil endpoint training dengan parameter kernel dan test_size
     - `predictSentiment()` - Memanggil endpoint prediksi dengan text input
     - `loadModelMetrics()` - Load metrik model saat halaman dibuka
     - `loadConfusionMatrix()` - Load confusion matrix saat halaman dibuka
     - `checkModelStatus()` - Mengecek apakah model sudah dilatih

### 4. **Membuat Python Scripts Helper**
   - `scripts/train_model.py` - Script untuk melatih SVM model
   - `scripts/predict_sentiment.py` - Script untuk prediksi sentimen

## Fitur Backend yang Sekarang Tersedia

### Training Model
- **Kernel Options**: Linear, RBF, Polynomial, Sigmoid
- **Test Size**: Dapat dikonfigurasi antara 10-50%
- **Metrics**: Mengembalikan accuracy, precision, recall, F1-score
- **Error Handling**: Validasi data dan error reporting yang baik

### Prediction
- **Input**: Text input dari user
- **Output**: Sentiment (Positif/Negatif/Netral) + Confidence Score
- **Validation**: Minimal 3 karakter, maksimal 5000 karakter
- **Error Handling**: Pengecekan apakah model sudah ada sebelum prediksi

### Model Management
- **Save/Load**: Model disimpan di `storage/app/private/svm_model.pkl`
- **Status Check**: Endpoint untuk mengecek apakah model sudah dilatih
- **Confusion Matrix**: Menampilkan performa model dengan detail

## Integrasi Frontend-Backend

### CSRF Protection
- Token CSRF sudah tersedia di layout (`meta name="csrf-token"`)
- JavaScript otomatis mengambil token dari meta tag

### API Response Format
Semua endpoint mengembalikan JSON dengan format:
```json
{
  "success": true/false,
  "message": "status message",
  "data": { /* response data */ },
  "errors": { /* validation errors jika ada */ }
}
```

### Error Handling
- Validasi input di backend
- Try-catch exception handling
- Logging untuk debugging
- User-friendly error messages

## Testing Endpoints

### 1. Test Training Model
```bash
curl -X POST http://127.0.0.1:8000/api/train-model \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: <token>" \
  -d '{"kernel":"rbf","test_size":20}'
```

### 2. Test Prediction
```bash
curl -X POST http://127.0.0.1:8000/api/predict-sentiment \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: <token>" \
  -d '{"text":"Produk ini sangat bagus dan memuaskan"}'
```

### 3. Get Model Metrics
```bash
curl http://127.0.0.1:8000/api/model-metrics
```

### 4. Get Model Status
```bash
curl http://127.0.0.1:8000/api/model-status
```

## Dependencies yang Dibutuhkan

Python packages (untuk scripts):
- numpy
- scikit-learn
- pandas

Install dengan:
```bash
pip install -r scripts/requirements.txt
```

## Notes

- Model disimpan di `storage/app/private/`
- Logs tersedia di `storage/logs/`
- CSRF token diperlukan untuk POST requests
- Frontend mengotomasi pengambilan CSRF token dari meta tag

## Status

✅ Backend API siap digunakan
✅ Frontend terintegrasi dengan backend
✅ Error handling dan validation selesai
⚠️ Python scripts perlu di-test dengan data nyata

## Next Steps (Opsional)

1. Buat database migrations untuk menyimpan hasil training
2. Implement batch prediction untuk multiple texts
3. Tambahkan model versioning
4. Implement model retraining scheduler
5. Tambahkan model performance tracking/monitoring
