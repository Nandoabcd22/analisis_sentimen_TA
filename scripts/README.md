# Python Text Preprocessing

Script Python untuk preprocessing teks bahasa Indonesia dengan metode yang sama seperti Google Colab Anda.

## 📁 Struktur Folder

```
scripts/
├── preprocessing.py    # Main preprocessing script
├── requirements.txt    # Python dependencies
└── README.md          # Documentation ini
```

## 🚀 Cara Install

```bash
cd scripts
pip install -r requirements.txt
```

## 📋 Dependencies

- `pandas>=1.3.0` - Data manipulation
- `nltk>=3.6.0` - Tokenization dan stopwords
- `Sastrawi>=1.0.1` - Indonesian stemming

## 🔧 Cara Penggunaan

### 1. Preprocessing Single Text

```python
from preprocessing import preprocess_single_text

text = "Tempatnya kotor dan tidak terawat, kecewa banget dan nyaman untuk kelurga."
result = preprocess_single_text(text)
```

### 2. Preprocessing CSV File

```python
from preprocessing import preprocess_csv

preprocess_csv("input.csv", "output.csv")
```

### 3. Run dari Command Line

```bash
python preprocessing.py
```

## 📊 Proses Preprocessing

Script ini melakukan 6 steps preprocessing sesuai Google Colab Anda:

### 1. **Case Folding**
- Convert text ke lowercase
- Contoh: `Tempatnya KOTOR` → `tempatnya kotor`

### 2. **Cleansing**
- Remove links: `http\S+|www\S+|https\S+`
- Remove hashtags & mentions: `@\w+|#\w+`
- Remove numbers: `\d+`
- Remove non-alphabetic: `[^a-zA-Z\s]`
- Remove extra whitespace: `\s+`

### 3. **Normalization**
- Load dari `kamus_normalisasi.txt`
- Format: `kata_asli<TAB>kata_normalisasi`
- Contoh: `gak → tidak`, `bgt → banget`

### 4. **Tokenization**
- Split text menjadi tokens menggunakan NLTK
- Contoh: `["tempat", "kotor", "tidak", "terawat"]`

### 5. **Stopword Removal**
- Remove Indonesian stopwords dari NLTK
- 200+ common words: `yang`, `dan`, `di`, `ke`, dll

### 6. **Stemming**
- Indonesian stemming dengan Sastrawi
- Remove prefixes, suffixes, infixes
- Contoh: `terawat → rawat`, `kecewa → kecewa`

## 📄 Format Output CSV

| text | case_folded | case_folding | cleansing | normalisasi | tokenizing | stopword | stemming | label |
|------|-------------|--------------|-----------|-------------|------------|----------|----------|-------|
| Original text | Lowercase | Same as case_folded | Cleaned text | Normalized text | JSON array | JSON array | JSON array | Label |

## 🎯 Contoh Hasil

**Input:**
```
"Tempatnya kotor dan tidak terawat, kecewa banget dan nyaman untuk kelurga."
```

**Output:**
```
{
  "original": "Tempatnya kotor dan tidak terawat, kecewa banget dan nyaman untuk kelurga.",
  "case_folding": "tempatnya kotor dan tidak terawat, kecewa banget dan nyaman untuk kelurga.",
  "cleansing": "tempatnya kotor dan tidak terawat kecewa banget dan nyaman untuk kelurga",
  "normalisasi": "tempatnya kotor dan tidak terawat kecewa banget dan nyaman untuk kelurga",
  "tokenizing": ["tempatnya", "kotor", "dan", "tidak", "terawat", "kecewa", "banget", "dan", "nyaman", "untuk", "kelurga"],
  "stopword": ["tempatnya", "kotor", "terawat", "kecewa", "banget", "nyaman", "kelurga"],
  "stemming": ["tempat", "kotor", "rawat", "kecewa", "banget", "nyaman", "kelurga"]
}
```

## 🔧 Integrasi dengan Laravel

### Opsi 1: Call Python dari Controller

```php
public function preprocessWithPython()
{
    $scriptPath = base_path('scripts/preprocessing.py');
    $inputFile = storage_path('app/reviews.csv');
    $outputFile = storage_path('app/reviews_preprocessed.csv');
    
    $command = "python {$scriptPath} {$inputFile} {$outputFile}";
    exec($command, $output, $returnCode);
    
    return response()->json([
        'success' => $returnCode === 0,
        'output' => $output
    ]);
}
```

### Opsi 2: Python API Service

```python
from flask import Flask, request, jsonify
from preprocessing import TextPreprocessor

app = Flask(__name__)
processor = TextPreprocessor()

@app.route('/preprocess', methods=['POST'])
def preprocess_text():
    data = request.get_json()
    text = data.get('text', '')
    
    result = processor.preprocess_text(text)
    return jsonify(result)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
```

### Opsi 3: Scheduled Task

```python
# cron job atau Windows Task Scheduler
import schedule
import time
from preprocessing import preprocess_csv

def job():
    preprocess_csv("data/reviews.csv", "data/reviews_preprocessed.csv")

schedule.every().day.at("02:00").do(job)

while True:
    schedule.run_pending()
    time.sleep(60)
```

## 📝 Customization

### Update Kamus Normalisasi

Edit file `kamus_normalisasi.txt`:
```
gak	tidak
bgt	banget
dgn	dengan
```

### Custom Stopwords

```python
custom_stopwords = ['kata1', 'kata2', 'kata3']
processor.stopwords.update(custom_stopwords)
```

### Custom Stemming Rules

```python
def custom_stemming(word):
    # Custom stemming logic
    return word

processor.stemmer.stem = custom_stemming
```

## 🐛 Troubleshooting

### NLTK Download Error
```bash
python -c "import nltk; nltk.download('punkt'); nltk.download('stopwords')"
```

### Sastrawi Installation Error
```bash
pip install --upgrade pip
pip install Sastrawi
```

### File Not Found Error
Pastikan file `kamus_normalisasi.txt` ada di folder yang sama:
```
scripts/
├── preprocessing.py
├── kamus_normalisasi.txt
└── requirements.txt
```

## 📈 Performance Tips

1. **Batch Processing**: Process multiple texts at once
2. **Memory Management**: Use chunks for large CSV files
3. **Caching**: Cache normalization dictionary
4. **Parallel Processing**: Use multiprocessing for large datasets

## 🤝 Contributing

1. Fork repository
2. Create feature branch
3. Add tests
4. Submit pull request

## 📄 License

MIT License - feel free to use and modify.
