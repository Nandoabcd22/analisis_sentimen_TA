<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleReviews = [
            [
                'username' => '@pengguna1',
                'review' => 'Pantai Pasir Putih Situbondo benar-benar indah! Pemandangan sunset-nya tak terlupakan. Pengunjung yang datang pasti akan puas 😍',
                'label' => 'Positif',
                'case_folding' => 'pantai pasir putih situbondo benar-benar indah! pemandangan sunset-nya tak terlupakan. pengunjung yang datang pasti akan puas 😍',
                'cleansing' => 'pantai pasir putih situbondo benarbenar indah pemandangan sunsetnya tak terlupakan pengunjung yang datang pasti akan puas',
                'normalisasi' => 'pantai pasir putih situbondo benar benar indah pemandangan matahari terbenam tidak terlupakan pengunjung yang datang pasti akan puas',
                'tokenizing' => ['pantai', 'pasir', 'putih', 'situbondo', 'benar', 'benar', 'indah', 'pemandangan', 'matahari', 'terbenam', 'tidak', 'terlupakan', 'pengunjung', 'yang', 'datang', 'pasti', 'akan', 'puas'],
                'stopword' => ['pantai', 'pasir', 'putih', 'situbondo', 'benar', 'benar', 'indah', 'pemandangan', 'matahari', 'terbenam', 'tidak', 'terlupakan', 'puas'],
                'stemming' => ['pantai', 'pasir', 'putih', 'situbondo', 'benar', 'benar', 'indah', 'pemandang', 'matahari', 'terbenam', 'tidak', 'terlupakan', 'puas']
            ],
            [
                'username' => '@pengguna2',
                'review' => 'Fasilitas di pantai masih kurang memadai. Toilet dan tempat istirahat perlu ditambah. Pengalaman liburanku terganggu 😞',
                'label' => 'Negatif',
                'case_folding' => 'fasilitas di pantai masih kurang memadai. toilet dan tempat istirahat perlu ditambah. pengalaman liburanku terganggu 😞',
                'cleansing' => 'fasilitas di pantai masih kurang memadai toilet dan tempat istirahat perlu ditambah pengalaman liburanku terganggu',
                'normalisasi' => 'fasilitas di pantai masih kurang memadai toilet dan tempat istirahat perlu ditambah pengalaman liburan saya terganggu',
                'tokenizing' => ['fasilitas', 'di', 'pantai', 'masih', 'kurang', 'memadai', 'toilet', 'dan', 'tempat', 'istirahat', 'perlu', 'ditambah', 'pengalaman', 'liburan', 'saya', 'terganggu'],
                'stopword' => ['fasilitas', 'di', 'pantai', 'kurang', 'memadai', 'toilet', 'tempat', 'istirahat', 'perlu', 'ditambah', 'pengalaman', 'liburan', 'saya', 'terganggu'],
                'stemming' => ['fasilitas', 'di', 'pantai', 'kurang', 'memadai', 'toilet', 'tempat', 'istirahat', 'perlu', 'tambah', 'pengalaman', 'liburan', 'saya', 'terganggu']
            ],
            [
                'username' => '@pengguna3',
                'review' => 'Pasir putihnya bersih dan halus. Airnya jernih dan cocok untuk berenang. Tempat yang sangat recommended untuk keluarga 👨‍👩‍👧‍👦',
                'label' => 'Positif',
                'case_folding' => 'pasir putihnya bersih dan halus. airnya jernih dan cocok untuk berenang. tempat yang sangat recommended untuk keluarga 👨‍👩‍👧‍👦',
                'cleansing' => 'pasir putihnya bersih dan halus airnya jernih dan cocok untuk berenang tempat yang sangat recommended untuk keluarga',
                'normalisasi' => 'pasir putihnya bersih dan halus airnya jernih dan cocok untuk berenang tempat yang sangat direkomendasikan untuk keluarga',
                'tokenizing' => ['pasir', 'putihnya', 'bersih', 'dan', 'halus', 'airnya', 'jernih', 'dan', 'cocok', 'untuk', 'berenang', 'tempat', 'yang', 'sangat', 'direkomendasikan', 'untuk', 'keluarga'],
                'stopword' => ['pasir', 'putihnya', 'bersih', 'halus', 'airnya', 'jernih', 'cocok', 'berenang', 'tempat', 'sangat', 'direkomendasikan', 'keluarga'],
                'stemming' => ['pasir', 'putih', 'bersih', 'halus', 'air', 'jernih', 'cocok', 'berenang', 'tempat', 'sangat', 'rekomendasi', 'keluarga']
            ],
            [
                'username' => '@pengguna4',
                'review' => 'Harga tiket masuk terlalu mahal! Tidak sebanding dengan fasilitas yang diberikan. Akan mencari pantai lain yang lebih murah',
                'label' => 'Negatif',
                'case_folding' => 'harga tiket masuk terlalu mahal! tidak sebanding dengan fasilitas yang diberikan. akan mencari pantai lain yang lebih murah',
                'cleansing' => 'harga tiket masuk terlalu mahal tidak sebanding dengan fasilitas yang diberikan akan mencari pantai lain yang lebih murah',
                'normalisasi' => 'harga tiket masuk terlalu mahal tidak sebanding dengan fasilitas yang diberikan akan mencari pantai lain yang lebih murah',
                'tokenizing' => ['harga', 'tiket', 'masuk', 'terlalu', 'mahal', 'tidak', 'sebanding', 'dengan', 'fasilitas', 'yang', 'diberikan', 'akan', 'mencari', 'pantai', 'lain', 'yang', 'lebih', 'murah'],
                'stopword' => ['harga', 'tiket', 'masuk', 'terlalu', 'mahal', 'sebanding', 'fasilitas', 'diberikan', 'mencari', 'pantai', 'lain', 'lebih', 'murah'],
                'stemming' => ['harga', 'tiket', 'masuk', 'terlalu', 'mahal', 'sebanding', 'fasilitas', 'beri', 'mencari', 'pantai', 'lain', 'lebih', 'murah']
            ],
            [
                'username' => '@pengguna5',
                'review' => 'Suasana pantai sangat tenang dan nyaman, cocok untuk bersantai melepas penat. Akan datang lagi ke sini',
                'label' => 'Positif',
                'case_folding' => 'suasana pantai sangat tenang dan nyaman, cocok untuk bersantai melepas penat. akan datang lagi ke sini',
                'cleansing' => 'suasana pantai sangat tenang dan nyaman cocok untuk bersantai melepas penat akan datang lagi ke sini',
                'normalisasi' => 'suasana pantai sangat tenang dan nyaman cocok untuk bersantai melepas penat akan datang lagi ke sini',
                'tokenizing' => ['suasana', 'pantai', 'sangat', 'tenang', 'dan', 'nyaman', 'cocok', 'untuk', 'bersantai', 'melepas', 'penat', 'akan', 'datang', 'lagi', 'ke', 'sini'],
                'stopword' => ['suasana', 'pantai', 'sangat', 'tenang', 'nyaman', 'cocok', 'bersantai', 'melepas', 'penat', 'datang', 'sini'],
                'stemming' => ['suasana', 'pantai', 'sangat', 'tenang', 'nyaman', 'cocok', 'santai', 'lepas', 'penat', 'datang', 'sini']
            ]
        ];

        // Convert tokenizing, stopword, and stemming arrays to JSON for storage
        foreach ($sampleReviews as &$review) {
            $review['tokenizing'] = json_encode($review['tokenizing']);
            $review['stopword'] = json_encode($review['stopword']);
            $review['stemming'] = json_encode($review['stemming']);
        }

        Review::insert($sampleReviews);

        // Generate more random data for testing
        for ($i = 6; $i <= 50; $i++) {
            $labels = ['Positif', 'Negatif', 'Netral'];
            $label = $labels[array_rand($labels)];
            
            Review::create([
                'username' => '@pengguna' . $i,
                'review' => 'Sample review text ' . $i . ' for testing pagination functionality with more data entries',
                'label' => $label,
                'case_folding' => 'sample review text ' . $i . ' for testing pagination functionality with more data entries',
                'cleansing' => 'sample review text ' . $i . ' for testing pagination functionality with more data entries',
                'normalisasi' => 'sample review text ' . $i . ' for testing pagination functionality with more data entries',
                'tokenizing' => json_encode(['sample', 'review', 'text', $i, 'testing', 'pagination', 'functionality']),
                'stopword' => json_encode(['sample', 'review', 'text', $i, 'testing', 'pagination', 'functionality']),
                'stemming' => json_encode(['sample', 'review', 'text', $i, 'test', 'paginasi', 'fungsi'])
            ]);
        }
    }
}
