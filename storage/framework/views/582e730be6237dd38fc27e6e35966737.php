

<?php $__env->startSection('title', 'Hasil dan Laporan'); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="page-header">
        <h1>📊 Hasil dan Laporan</h1>
        <p>Analisis mendalam terhadap model sentiment analysis dan dataset reviews.</p>
    </div>

    <!-- Model Metrics Section -->
    <div class="section-card">
        <h2 class="section-title">🤖 Performa Model Terbaru</h2>
        <div id="metrics-container">
            <div class="loading-spinner">
                <p>Memuat data model...</p>
            </div>
        </div>
    </div>

    <!-- Class Distribution Section -->
    <div class="section-card">
        <h2 class="section-title">📈 Distribusi Kelas Dataset</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <canvas id="classDistributionChart" style="max-height: 300px;"></canvas>
            </div>
            <div id="distribution-stats" style="padding: 20px;">
                <div class="stat-item">
                    <span>Total Reviews</span>
                    <span id="total-reviews" class="stat-value">-</span>
                </div>
                <div class="stat-item">
                    <span>Sentimen Positif</span>
                    <span id="positif-count" class="stat-value" style="color: #4CAF50;">-</span>
                </div>
                <div class="stat-item">
                    <span>Sentimen Netral</span>
                    <span id="netral-count" class="stat-value" style="color: #FF9800;">-</span>
                </div>
                <div class="stat-item">
                    <span>Sentimen Negatif</span>
                    <span id="negatif-count" class="stat-value" style="color: #f44336;">-</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Wordcloud Section -->
    <div class="section-card">
        <h2 class="section-title">☁️ Wordcloud - Kata-Kata Paling Sering Muncul</h2>
        <div style="margin-bottom: 15px;">
            <label style="font-weight: 600; margin-right: 10px;">Filter Sentimen:</label>
            <select id="wordcloud-filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <option value="">Semua Sentimen</option>
                <option value="Positif">Positif ✓</option>
                <option value="Netral">Netral ≈</option>
                <option value="Negatif">Negatif ✗</option>
            </select>
        </div>
        <div id="wordcloud-container" style="width: 100%; height: 500px; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 8px; position: relative;">
            <div class="loading-spinner">
                <p>Membuat wordcloud...</p>
            </div>
        </div>
    </div>

    <!-- Word Statistics -->
    <div class="section-card">
        <h2 class="section-title">📝 Statistik Kata</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div class="stat-box">
                <div class="stat-label">Kata Unik</div>
                <div id="unique-words" class="stat-number">-</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Kata</div>
                <div id="total-words" class="stat-number">-</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Rata-rata Kata/Review</div>
                <div id="avg-words" class="stat-number">-</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Terakhir Diperbarui</div>
                <div id="last-updated" class="stat-number" style="font-size: 12px;">-</div>
            </div>
        </div>
    </div>

    <!-- Top Words Table -->
    <div class="section-card">
        <h2 class="section-title">🏆 Top 20 Kata Paling Frequent</h2>
        <div style="overflow-x: auto;">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kata</th>
                        <th>Frekuensi</th>
                        <th>Persen</th>
                        <th>Visualisasi</th>
                    </tr>
                </thead>
                <tbody id="top-words-tbody">
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999;">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    .page-header {
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 15px 0;
        color: #1a1a1a;
    }

    .page-header p {
        font-size: 16px;
        color: #666;
        margin: 0;
        line-height: 1.5;
    }

    .section-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0 0 25px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #2196F3;
    }

    .loading-spinner {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }

    .loading-spinner::before {
        content: '';
        display: inline-block;
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #2196F3;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 10px;
        vertical-align: middle;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #2196F3;
    }

    .stat-box {
        background: linear-gradient(135deg, #f5f5f5, #fafafa);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #e0e0e0;
    }

    .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #2196F3;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .metric-card {
        background: linear-gradient(135deg, #f5f5f5, #fafafa);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #2196F3;
        text-align: center;
    }

    .metric-label {
        font-size: 12px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .metric-value {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .metric-subtitle {
        font-size: 11px;
        color: #999;
        margin-top: 5px;
    }

    .results-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .results-table thead {
        background: #f5f5f5;
    }

    .results-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #ddd;
        font-size: 13px;
    }

    .results-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
        color: #666;
    }

    .results-table tbody tr:hover {
        background: #f9f9f9;
    }

    .bar-container {
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
        height: 20px;
        position: relative;
    }

    .bar-fill {
        background: linear-gradient(90deg, #2196F3, #1976D2);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 8px;
        color: white;
        font-size: 11px;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .error-message {
        background: #ffebee;
        border: 1px solid #ef5350;
        color: #c62828;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .section-card {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .metrics-grid {
            grid-template-columns: 1fr;
        }

        #wordcloud-container {
            height: 400px;
        }

        .stat-box {
            flex: 1;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/wordcloud2.js@2.1.2/dist/wordcloud2.min.js"></script>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let classDistributionChart = null;
    let currentWordCloudData = [];

    // Load all data on page load
    window.addEventListener('DOMContentLoaded', function() {
        loadHasilData();
        loadWordCloud();
        setupFilters();
    });

    function loadHasilData() {
        fetch('/api/hasil-data', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMetrics(data.data);
                displayClassDistribution(data.data);
            } else {
                showError('Gagal memuat data hasil');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error memuat data: ' + error.message);
        });
    }

    function displayMetrics(data) {
        const container = document.getElementById('metrics-container');
        const metrics = data.metrics;
        const timestamp = data.metrics_timestamp;

        let html = '';

        if (!metrics || !metrics.accuracy) {
            html = '<div class="empty-state"><p>📊 Belum ada training model. Silakan lakukan training terlebih dahulu di halaman Klasifikasi.</p></div>';
        } else {
            const accuracy = (metrics.accuracy * 100).toFixed(2);
            const precision = (metrics.precision * 100).toFixed(2);
            const recall = (metrics.recall * 100).toFixed(2);
            const f1 = (metrics.f1_score * 100).toFixed(2);

            html = `
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-label">Accuracy</div>
                        <div class="metric-value">${accuracy}%</div>
                        <div class="metric-subtitle">Tingkat Akurasi</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Precision</div>
                        <div class="metric-value">${precision}%</div>
                        <div class="metric-subtitle">Presisi Prediksi</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Recall</div>
                        <div class="metric-value">${recall}%</div>
                        <div class="metric-subtitle">Cakupan Prediksi</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">F1-Score</div>
                        <div class="metric-value">${f1}%</div>
                        <div class="metric-subtitle">Rata-rata Harmonis</div>
                    </div>
                </div>
                <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
                    <p style="margin: 0; font-size: 13px; color: #666;">
                        📅 <strong>Terakhir diperbarui:</strong> ${timestamp || 'Tidak diketahui'}
                    </p>
                </div>
            `;
        }

        container.innerHTML = html;
    }

    function displayClassDistribution(data) {
        const dist = data.class_distribution;
        const total = data.total_reviews;

        // Update stats
        document.getElementById('total-reviews').textContent = total.toLocaleString();
        document.getElementById('positif-count').textContent = dist.Positif;
        document.getElementById('netral-count').textContent = dist.Netral;
        document.getElementById('negatif-count').textContent = dist.Negatif;
        document.getElementById('last-updated').textContent = data.metrics_timestamp || 'Tidak ada data';

        // Create chart
        const ctx = document.getElementById('classDistributionChart').getContext('2d');
        
        if (classDistributionChart) {
            classDistributionChart.destroy();
        }

        classDistributionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Positif', 'Netral', 'Negatif'],
                datasets: [{
                    data: [dist.Positif, dist.Netral, dist.Negatif],
                    backgroundColor: ['#4CAF50', '#FF9800', '#f44336'],
                    borderColor: ['#388E3C', '#F57C00', '#d32f2f'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: { size: 13, weight: '600' }
                        }
                    },
                    tooltip: {
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percent = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function setupFilters() {
        document.getElementById('wordcloud-filter').addEventListener('change', function() {
            loadWordCloud(this.value);
        });
    }

    function loadWordCloud(label = '') {
        const url = `/api/word-frequencies?limit=100${label ? '&label=' + label : ''}`;

        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentWordCloudData = data.data;
                renderWordCloud(data.data);
                updateWordStats(data);
                updateTopWordsTable(data.data);
            } else {
                showError('Gagal memuat wordcloud');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error memuat wordcloud: ' + error.message);
        });
    }

    function renderWordCloud(wordData) {
        if (!wordData || wordData.length === 0) {
            document.getElementById('wordcloud-container').innerHTML = 
                '<div class="empty-state"><p>Tidak ada data kata untuk wordcloud</p></div>';
            return;
        }

        const container = document.getElementById('wordcloud-container');
        container.innerHTML = '';

        // Convert data format for wordcloud2 library
        const words = wordData.map(item => [item.text, item.weight]);

        // Render wordcloud using wordcloud2.js
        try {
            WordCloud(container, {
                list: words,
                fontFamily: 'Arial, Helvetica, sans-serif',
                color: () => {
                    const colors = ['#2196F3', '#4CAF50', '#FF9800', '#f44336', '#9C27B0', '#00BCD4', '#E91E63', '#FFC107'];
                    return colors[Math.floor(Math.random() * colors.length)];
                },
                weightFactor: (size) => {
                    return Math.pow(size / Math.max(...words.map(w => w[1])), 1.5) * 80 + 12;
                },
                rotationRatio: 0.2,
                rotationSteps: 2,
                minSize: 12,
                shuffle: true,
                wait: 100,
                click: (item) => {
                    console.log('Clicked word:', item[0], 'Frequency:', item[1]);
                }
            });
        } catch(e) {
            console.error('Wordcloud render error:', e);
            // Fallback: show as list
            let html = '<div style="padding: 20px;">';
            wordData.slice(0, 30).forEach((item, i) => {
                const size = Math.max(12, Math.min(40, 12 + (item.weight / wordData[0].weight) * 28));
                html += `<span style="font-size: ${size}px; margin: 5px; display: inline-block; color: ${['#2196F3', '#4CAF50', '#FF9800'][i % 3]};">${item.text}</span>`;
            });
            html += '</div>';
            container.innerHTML = html;
        }
    }

    function updateWordStats(data) {
        document.getElementById('unique-words').textContent = data.total_unique_words?.toLocaleString() || '-';
        document.getElementById('total-words').textContent = data.total_words?.toLocaleString() || '-';

        // Get total reviews for average calculation
        const totalReviews = document.getElementById('total-reviews').textContent.replace(/,/g, '');
        if (totalReviews && data.total_words) {
            const avgWords = (data.total_words / parseInt(totalReviews)).toFixed(1);
            document.getElementById('avg-words').textContent = avgWords;
        }
    }

    function updateTopWordsTable(wordData) {
        const tbody = document.getElementById('top-words-tbody');
        const maxFreq = wordData.length > 0 ? wordData[0].weight : 1;
        const top20 = wordData.slice(0, 20);

        if (top20.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Tidak ada data</td></tr>';
            return;
        }

        let html = '';
        top20.forEach((item, index) => {
            const percent = ((item.weight / maxFreq) * 100).toFixed(1);
            html += `
                <tr>
                    <td style="font-weight: 700; color: #2196F3;">${index + 1}</td>
                    <td style="font-weight: 600;">${item.text}</td>
                    <td style="text-align: center; font-weight: 600;">${item.weight}</td>
                    <td style="text-align: center;">${percent}%</td>
                    <td>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: ${percent}%;">
                                ${percent}%
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function showError(message) {
        const container = document.querySelector('.section-card');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        container.insertAdjacentElement('afterbegin', errorDiv);
        setTimeout(() => errorDiv.remove(), 5000);
    }
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laragon\www\analisis_sentimen_TA\resources\views/hasil.blade.php ENDPATH**/ ?>