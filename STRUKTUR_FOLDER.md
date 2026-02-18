# Struktur Folder Analisis Sentimen TA

Dokumentasi struktur folder dan organisasi file project ini.

## Struktur Umum

```
analisis_sentimen_TA/
├── app/                          # Aplikasi Laravel
│   ├── Console/                  # Artisan commands
│   ├── Http/
│   │   └── Controllers/          # Controller classes
│   ├── Models/                   # Eloquent models (Review, User)
│   ├── Providers/                # Service providers
│   └── Services/                 # Business logic services (NEW)
│
├── bootstrap/                    # Laravel bootstrap files
│   ├── app.php
│   ├── providers.php
│   └── cache/
│
├── config/                       # Konfigurasi Laravel
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   └── ...
│
├── database/                     # Database migrations & seeders
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── docs/                         # Dokumentasi project (NEW)
│   └── BACKEND_FIXES.md          # Backend fixes documentation
│
├── public/                       # Document root
│   ├── index.php
│   └── robots.txt
│
├── resources/                    # Frontend assets
│   ├── css/
│   ├── js/
│   ├── data/                     # Data files (NEW)
│   │   └── kamus_normalisasi.txt # Normalization dictionary
│   └── views/                    # Blade templates
│       ├── dashboard.blade.php
│       ├── klasifikasi.blade.php
│       ├── preprocessing.blade.php
│       ├── tfidf.blade.php
│       └── layouts/
│
├── routes/                       # Web & API routes
│   ├── console.php
│   └── web.php
│
├── scripts/                      # Python & utility scripts
│   ├── utilities/                # Testing & utility scripts (NEW)
│   │   ├── check_progress.php
│   │   ├── check_status.php
│   │   ├── get_last_log.php
│   │   ├── read_log.php
│   │   ├── show_recent_logs.php
│   │   ├── tail_log.php
│   │   ├── test_batch_update.php
│   │   ├── test_preprocess.php
│   │   ├── test_python_direct.php
│   │   └── verify_processing.php
│   ├── predict_sentiment.py      # Sentiment prediction script
│   ├── preprocessing.py          # Text preprocessing script
│   ├── train_model.py            # Model training script
│   ├── requirements.txt          # Python dependencies
│   └── README.md                 # Python scripts documentation
│
├── storage/                      # Storage files
│   ├── app/
│   │   ├── private/              # Private models & data
│   │   └── public/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   ├── testing/
│   │   └── views/
│   └── logs/
│
├── tests/                        # Unit & feature tests
│   ├── Feature/
│   ├── Unit/
│   └── TestCase.php
│
├── vendor/                       # Composer packages (auto-generated)
│
├── .env                          # Environment variables
├── .env.example                  # Environment template
├── .gitignore                    # Git ignore file
├── artisan                       # Laravel Artisan command
├── composer.json                 # Composer configuration
├── composer.lock                 # Composer lock file
├── package.json                  # NPM configuration
├── package-lock.json             # NPM lock file
├── phpunit.xml                   # PHPUnit configuration
├── README.md                     # Main README
├── vite.config.js                # Vite configuration
└── STRUKTUR_FOLDER.md            # This file

```

## Perubahan Struktur

### Catatan Penting:
1. **scripts/utilities/** - Folder baru untuk menampung semua PHP utility dan test scripts
2. **resources/data/** - Folder baru untuk menampung data files seperti kamus_normalisasi.txt
3. **docs/** - Folder baru untuk dokumentasi project
4. **app/Services/** - Folder siap untuk business logic services (opsional pengembangan)

## Path Files Penting

### Backend
- **Routes**: `routes/web.php` - Route definitions
- **Controllers**: `app/Http/Controllers/` - Controller classes
- **Models**: `app/Models/` - Database models

### Frontend
- **Views**: `resources/views/` - Blade templates
- **Assets**: `resources/css/` dan `resources/js/` - CSS dan JavaScript files

### Python/ML
- **Scripts**: `scripts/` - Python scripts untuk preprocessing, training, prediction
- **Data**: `resources/data/kamus_normalisasi.txt` - Normalization dictionary
- **Models**: `storage/app/private/` - Trained ML models

### Testing & Utilities
- **Tests**: `tests/` - Unit dan feature tests
- **Utilities**: `scripts/utilities/` - Debug dan testing scripts
- **Logs**: `storage/logs/` - Application logs

## Updated Path Reference

Setelah reorganisasi, file-file dapat diakses dengan path:

```
resources/data/kamus_normalisasi.txt
scripts/utilities/check_progress.php
scripts/utilities/check_status.php
... dan seterusnya
```

## Best Practices

1. **Jangan modifikasi folder `vendor/`** - folder ini auto-generated oleh Composer
2. **Environment variables** - gunakan `.env` file untuk konfigurasi sensitive
3. **Logs** - cek `storage/logs/` untuk debugging
4. **Static Models** - tempat trained models ada di `storage/app/private/`

