# 🔧 Troubleshooting Guide - Training Error

Panduan untuk mengatasi error saat training model dari Laravel.

## 🚨 Error: "Python training returned no output"

Ini adalah error paling umum. Berikut cara mengatasinya:

### 1. Jalankan Test Setup Script

```bash
cd scripts
python test_setup.py
```

Script ini akan:
- ✓ Check semua Python packages terinstall
- ✓ Check semua file paths exist
- ✓ Test kamus loading
- ✓ Test preprocessing pipeline
- ✓ Test database connection (jika ada)

### 2. Debug dari Command Line

Coba jalankan training langsung dari terminal:

```bash
# Test dengan database
python train_model.py

# Test dengan CSV
python train_model.py --csv "dataset.csv"
```

Lihat output error di terminal untuk mendapat informasi lebih detail.

### 3. Debug dari Browser

Akses endpoint debug di browser:

```
http://127.0.0.1:8000/api/debug/python-setup
```

Ini akan menunjukkan:
- Python executable path yang ditemukan
- Python version
- Status setiap package (Installed/Missing)
- Path kamus dan model files

## 🛠️ Solusi Umum

### Problem 1: Python Tidak Ditemukan

**Error:** 
```
Error training model: Python tidak ditemukan
```

**Solusi:**

1. **Pastikan Python terinstall:**
```bash
python --version
```

2. **Tambahkan ke PATH (Windows):**
   - Buka Environment Variables
   - Tambahkan path Python ke PATH
   - Example: `C:\Program Files\Python311`

3. **Gunakan Python Launcher (Windows):**
```bash
py --version
```

### Problem 2: Dependencies Tidak Installed

**Error:**
```
ModuleNotFoundError: No module named 'sklearn'
```

**Solusi:**

```bash
cd scripts
pip install -r requirements.txt
```

Jika masih error, install satu-satu:

```bash
pip install pandas numpy nltk scikit-learn Sastrawi imbalanced-learn
pip install mysql-connector-python  # Optional, untuk database
```

### Problem 3: Kamus File Tidak Ditemukan

**Error:**
```
⚠ Kamus file not found
```

**Solusi:**

Pastikan file ada di lokasi yang benar:

```
resources/data/kamus_normalisasi.txt
```

Jika tidak ada, script akan menggunakan basic fallback normalization.

### Problem 4: Database Connection Failed

**Error:**
```
⚠ Database error
```

**Solusi:**

- Gunakan CSV file untuk training:
```bash
python train_model.py --csv "dataset.csv"
```

- Atau setup database terlebih dahulu
- Check `.env` file untuk database credentials

### Problem 5: Insufficient Data

**Error:**
```
Error training model: Tidak ada data
```

**Solusi:**

- Upload minimal 50-100 reviews ke database
- Atau gunakan CSV dengan format:
  ```
  text,label
  "Review teks di sini","positive"
  "Review teks di sini","negative"
  ```

## 📊 Testing dari Browser

### Test 1: Debug Python Setup

```bash
curl http://127.0.0.1:8000/api/debug/python-setup
```

Output akan menunjukkan:
```json
{
  "success": true,
  "data": {
    "python_found": true,
    "python_cmd": "python",
    "python_version": "Python 3.10.0",
    "pip_packages": {
      "pandas": "Installed",
      "sklearn": "Installed",
      ...
    }
  }
}
```

### Test 2: Test Training Setup

```bash
curl -X POST http://127.0.0.1:8000/api/debug/test-training
```

Jika berhasil:
```json
{
  "success": true,
  "message": "Training test berhasil!",
  "result": {...}
}
```

Jika error, akan menunjukkan output Python yang актуальный.

## 🔍 Manual Training Test

### Step 1: Verify Python Executable

```bash
# Windows
WHERE python
where py

# Linux/Mac
which python3
```

### Step 2: Check Train Script Exists

```bash
ls -la scripts/train_model.py
# or on Windows
DIR scripts\train_model.py
```

### Step 3: Run Training Script

```bash
cd scripts

# From database
python train_model.py

# From CSV
python train_model.py --csv "path/to/dataset.csv"
```

### Step 4: Check Output

Training akan menampilkan progress seperti:

```
======================================================================
TRAINING SENTIMENT ANALYSIS SVM MODEL (SESUAI COLAB)
======================================================================

[STEP 1] Loading Data...
✓ Loaded 150 rows from CSV
Label Distribution: {'positive': 75, 'negative': 75}

[STEP 2] Loading Normalization Dictionary...
✓ Kamus loaded: 500 entries

[STEP 3] Preprocessing Data...
  - Case Folding
  - Cleaning
  - Tokenization
  - Normalisasi Kata
  - Stopword Removal
  - Stemming
✓ Jumlah Data setelah Preprocessing: 148
...
```

## 📋 Checklist Debugging

Sebelum training, pastikan:

- [ ] Python terinstall: `python --version`
- [ ] Packages terinstall: `pip list | grep scikit`
- [ ] Scripts ada: `ls scripts/train_model.py`
- [ ] Kamus ada: `ls resources/data/kamus_normalisasi.txt`
- [ ] Data ada: Database atau CSV file
- [ ] Storage folder writable: `storage/app/private/`

## 🆘 Advanced Debugging

### Enable Verbose Logging

Edit `.env`:

```
LOG_LEVEL=debug
```

Cek logs di:

```
storage/logs/laravel.log
```

### Run with Strace (Linux/Mac)

```bash
cd scripts
strace python train_model.py
```

### Run with Python Debugger

```bash
cd scripts
python -m pdb train_model.py
```

## 📞 Getting Help

Jika masih error setelah semua langkah:

1. Buka `storage/logs/laravel.log` dan cari error message
2. Jalankan `python test_setup.py` dan share output
3. Jalankan training dari terminal dan copy full error output
4. Share semua informasi di log/forum

## 📝 Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| "Python not found" | Python tidak terinstall/PATH salah | Install Python atau update PATH |
| "No module named" | Package tidak terinstall | `pip install -r requirements.txt` |
| "No such file" | File path salah | Check file locations |
| "Connection refused" | Database tidak berjalan | Start MySQL atau gunakan CSV |
| "Not enough data" | Dataset terlalu kecil | Upload minimal 50+ reviews |
| "Permission denied" | File permission issue | Check storage folder permissions |

---

**Last Updated:** 2026-02-18  
**Tested On:** Windows 10/11, Python 3.9+, Laravel 10

Untuk info lebih lanjut, baca [README.md](README.md)
