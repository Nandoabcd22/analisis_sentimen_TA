@extends('layouts.app')

@section('title', 'Klasifikasi SVM')

@section('content')
<div class="content">
    <div class="page-header">
        <h1>Klasifikasi Sentimen dengan SVM</h1>
        <p>Lakukan training model SVM untuk klasifikasi sentimen menggunakan data yang sudah dipreprocessing.</p>
    </div>

    <!-- Training Control Section -->
    <div class="section-card">
        <h2 class="section-title">Parameter Training</h2>
        
        <div class="form-group">
            <label class="form-label">Kernel SVM</label>
            <div style="padding: 12px 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; color: #333; font-weight: 500;">
                ✅ RBF (Radial Basis Function) - Default
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Data Split Ratio (Fixed)</label>
            <p class="text-sm font-semibold text-gray-700">Training: <span class="text-blue-600">90%</span> | Testing: <span class="text-orange-600">10%</span></p>
        </div>

        <div class="form-group">
            <button id="train-btn" class="btn btn-primary">Mulai Training</button>
        </div>
    </div>

    <!-- Training Progress Section -->
    <div id="progress-section" class="section-card" style="display: none;">
        <h2 class="section-title">Status Training</h2>
        <div style="margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div style="background: #f5f5f5; padding: 15px; border-radius: 6px; border-left: 3px solid #2196F3;">
                    <div style="font-size: 12px; color: #666; font-weight: 600; margin-bottom: 5px;">⏳ Waktu Berjalan</div>
                    <div id="elapsed-time" style="font-size: 20px; font-weight: 700; color: #2196F3;">0s</div>
                </div>
                <div style="background: #f5f5f5; padding: 15px; border-radius: 6px; border-left: 3px solid #4CAF50;">
                    <div style="font-size: 12px; color: #666; font-weight: 600; margin-bottom: 5px;">📊 Tahap Proses</div>
                    <div id="process-stage" style="font-size: 14px; font-weight: 600; color: #4CAF50;">Initializing...</div>
                </div>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
        </div>
        <p id="progress-text" class="progress-text">Initializing...</p>
    </div>

    <!-- Results Section -->
    <div id="results-section" style="display: none;">
        <!-- Configuration -->
        <div class="section-card">
            <h2 class="section-title">Konfigurasi Model</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Kernel:</span>
                    <span class="info-value" id="result-kernel">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Data:</span>
                    <span class="info-value" id="result-total">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Training:</span>
                    <span class="info-value" id="result-train">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Testing:</span>
                    <span class="info-value" id="result-test">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Features:</span>
                    <span class="info-value" id="result-features">-</span>
                </div>
            </div>
        </div>

        <!-- Metrics -->
        <div class="section-card">
            <h2 class="section-title">Hasil Evaluasi Model</h2>
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">Accuracy</div>
                    <div class="metric-value" id="metric-accuracy">-</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Precision</div>
                    <div class="metric-value" id="metric-precision">-</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Recall</div>
                    <div class="metric-value" id="metric-recall">-</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">F1 Score</div>
                    <div class="metric-value" id="metric-f1">-</div>
                </div>
            </div>
        </div>

        <!-- Per-Class Metrics -->
        <div class="section-card">
            <h2 class="section-title">Metrik Per Kelas</h2>
            <table class="metrics-table">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Precision</th>
                        <th>Recall</th>
                        <th>F1-Score</th>
                        <th>Support</th>
                    </tr>
                </thead>
                <tbody id="per-class-tbody">
                    <tr><td colspan="5" style="text-align: center; color: #999;">No data</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Confusion Matrix & Word Cloud (Side by Side) -->
        <div class="section-card">
            <h2 class="section-title" style="margin-bottom: 30px;">📊 Hasil Visualisasi</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;">
                <!-- Confusion Matrix -->
                <div>
                    <h3 style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 15px 0;">Confusion Matrix</h3>
                    <div style="display: flex; justify-content: center; align-items: flex-start; gap: 30px; padding: 20px; background: #f9f9f9; border-radius: 6px;">
                        <!-- Heatmap Container -->
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <!-- Y-axis label -->
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="writing-mode: vertical-rl; text-orientation: mixed; transform: rotate(180deg); font-weight: 600; font-size: 12px; color: #555; width: 25px; text-align: center;">Actual</div>
                                <!-- Heatmap Grid -->
                                <div style="display: inline-block;">
                                    <!-- Header Row -->
                                    <div style="display: flex; gap: 0; margin-bottom: 5px;">
                                        <div style="width: 35px; height: 25px;"></div>
                                        <div id="cm-header-0" style="width: 70px; height: 25px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; color: #333;">Negatif</div>
                                        <div id="cm-header-1" style="width: 70px; height: 25px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; color: #333;">Netral</div>
                                        <div id="cm-header-2" style="width: 70px; height: 25px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; color: #333;">Positif</div>
                                    </div>
                                    
                                    <!-- Row 0 -->
                                    <div style="display: flex; gap: 0; margin-bottom: 2px;">
                                        <div id="cm-label-0" style="width: 35px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 11px; color: #333; background: #f5f5f5;">Negatif</div>
                                        <div id="cm-00" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                        <div id="cm-01" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                        <div id="cm-02" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                    </div>
                                    
                                    <!-- Row 1 -->
                                    <div style="display: flex; gap: 0; margin-bottom: 2px;">
                                        <div id="cm-label-1" style="width: 35px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 11px; color: #333; background: #f5f5f5;">Netral</div>
                                        <div id="cm-10" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                        <div id="cm-11" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                        <div id="cm-12" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                    </div>
                                    
                                    <!-- Row 2 -->
                                    <div style="display: flex; gap: 0;">
                                        <div id="cm-label-2" style="width: 35px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 11px; color: #333; background: #f5f5f5;">Positif</div>
                                        <div id="cm-20" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                        <div id="cm-21" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                        <div id="cm-22" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; cursor: pointer; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.3);">0</div>
                                    </div>
                                    
                                    <!-- X-axis label -->
                                    <div style="margin-top: 8px; text-align: center; font-weight: 600; font-size: 12px; color: #555; margin-left: 35px;">Predicted</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Color Bar Legend -->
                        <div style="display: flex; flex-direction: column; gap: 8px; align-items: center;">
                            <div style="font-weight: 600; font-size: 11px; color: #666; text-align: center; width: 25px;">Value</div>
                            <div style="width: 25px; height: 200px; background: linear-gradient(to top, #0d47a1, #1565c0, #1976d2, #1e88e5, #2196f3, #42a5f5, #64b5f6, #81c784, #c8e6c9, #f0f4c3); border: 2px solid #999; border-radius: 3px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                            <div style="font-size: 10px; color: #888; font-weight: 500;">Max</div>
                            <div style="font-size: 10px; color: #888; font-weight: 500;">Min</div>
                        </div>
                    </div>
                </div>
                
                <!-- Word Cloud Visualization -->
                <div>
                    <h3 style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 15px 0;">Word Cloud</h3>
                    <p style="color: #666; margin: 0 0 12px 0; font-size: 13px;">Kata-kata paling sering muncul</p>
                    <div id="wordcloud-container" style="width: 100%; min-height: 350px; display: flex; align-items: center; justify-content: center; background: #f9f9f9; border-radius: 6px; border: 1px solid #e0e0e0; overflow: auto;">
                        <div style="text-align: center; color: #999;">
                            <p style="font-size: 13px;">📊 Word cloud akan ditampilkan setelah training selesai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sentiment Prediction Section -->
        <div class="section-card">
            <h2 class="section-title">🔮 Prediksi Sentimen</h2>
            <p style="color: #666; margin: 0 0 20px 0; font-size: 14px;">Masukkan teks untuk memprediksi sentimen menggunakan model yang sudah dilatih.</p>
            
            <div class="form-group">
                <label class="form-label">Masukkan Teks:</label>
                <textarea id="predict-text" class="form-input" style="resize: vertical; min-height: 120px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;" placeholder="Contoh: Produk ini sangat bagus dan memuaskan!"></textarea>
            </div>

            <div class="form-group">
                <button id="predict-btn" class="btn btn-primary">Prediksi Sentimen</button>
            </div>

            <!-- Prediction Results -->
            <div id="prediction-result" style="display: none; margin-top: 30px;">
                <div style="background: linear-gradient(135deg, #f5f5f5, #fafafa); padding: 25px; border-radius: 8px; border: 2px solid #e0e0e0;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <div style="font-size: 12px; font-weight: 600; color: #2196F3; text-transform: uppercase; margin-bottom: 10px;">Prediksi Sentimen</div>
                            <div id="predict-sentiment" style="font-size: 24px; font-weight: 700; color: #1a1a1a; padding: 15px; background: white; border-radius: 6px; text-align: center; border-left: 4px solid #2196F3;">-</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; font-weight: 600; color: #FF9800; text-transform: uppercase; margin-bottom: 10px;">Confidence Score</div>
                            <div id="predict-confidence" style="font-size: 24px; font-weight: 700; color: #1a1a1a; padding: 15px; background: white; border-radius: 6px; text-align: center; border-left: 4px solid #FF9800;">-</div>
                        </div>
                    </div>

                    <!-- Probability Distribution -->
                    <div>
                        <div style="font-size: 12px; font-weight: 600; color: #4CAF50; text-transform: uppercase; margin-bottom: 15px;">Distribusi Probabilitas Per Kelas</div>
                        <div id="probability-bars" style="display: grid; gap: 12px;">
                            <!-- Akan diisi oleh JavaScript -->
                        </div>
                    </div>

                    <!-- Input Text Used -->
                    <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 6px; border-left: 4px solid #9C27B0;">
                        <div style="font-size: 12px; font-weight: 600; color: #666; text-transform: uppercase; margin-bottom: 8px;">Teks Input:</div>
                        <div id="predict-input-text" style="font-size: 14px; color: #333; line-height: 1.6; font-style: italic;"></div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div id="prediction-error" style="display: none; margin-top: 20px;">
                <div style="background: #ffebee; padding: 15px; border-radius: 6px; border-left: 4px solid #f44336; color: #c62828;">
                    <strong>⚠️ Error:</strong> <span id="error-message"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .content {
        max-width: 1200px;
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
    }

    .form-input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
    }

    .form-input:focus {
        outline: none;
        border-color: #2196F3;
        box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #2196F3;
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        background: #1976D2;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
    }

    .btn-primary:disabled {
        background: #90caf9;
        cursor: not-allowed;
        transform: none;
    }

    .progress-bar {
        width: 100%;
        height: 10px;
        background: #f0f0f0;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 15px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50, #45a049);
        width: 0%;
        transition: width 0.3s ease;
    }

    .progress-text {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 6px;
        border-left: 4px solid #2196F3;
    }

    .info-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .info-value {
        display: block;
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
    }

    .metric-card {
        background: linear-gradient(135deg, #f5f5f5, #fafafa);
        padding: 25px;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        text-align: center;
    }

    .metric-label {
        font-size: 12px;
        font-weight: 600;
        color: #2196F3;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .metric-value {
        font-size: 36px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .metrics-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .metrics-table thead {
        background: #f5f5f5;
    }

    .metrics-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #ddd;
    }

    .metrics-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        color: #666;
    }

    .metrics-table tbody tr:hover {
        background: #f9f9f9;
    }

    .metrics-table tbody td:nth-child(2),
    .metrics-table tbody td:nth-child(3),
    .metrics-table tbody td:nth-child(4),
    .metrics-table tbody td:nth-child(5) {
        text-align: center;
        font-weight: 600;
        color: #1a1a1a;
    }

    .cm-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 13px;
    }

    .cm-table td, .cm-table th {
        padding: 12px;
        text-align: center;
        border: 1px solid #ddd;
    }

    .cm-table th {
        background: #f5f5f5;
        font-weight: 600;
        color: #333;
    }

    .cm-table tbody td {
        color: #1a1a1a;
        font-weight: 500;
    }

    .cm-table tbody tr:nth-child(odd) {
        background: #f9f9f9;
    }

    .text-sm {
        font-size: 13px;
    }

    .font-semibold {
        font-weight: 600;
    }

    .text-gray-700 {
        color: #666;
    }

    .text-blue-600 {
        color: #2196F3;
    }

    .text-orange-600 {
        color: #ff9800;
    }

    @media (max-width: 768px) {
        .section-card {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .metrics-grid {
            grid-template-columns: 1fr 1fr;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let trainingStartTime = null;
    let elapsedTimeInterval = null;
    let trainModelListenerAttached = false;  // Prevent duplicate listeners

    function updateElapsedTime() {
        if (trainingStartTime) {
            const elapsed = Math.floor((Date.now() - trainingStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            
            let timeString = '';
            if (minutes > 0) {
                timeString = `${minutes}m ${seconds}s`;
            } else {
                timeString = `${seconds}s`;
            }
            
            document.getElementById('elapsed-time').textContent = timeString;
        }
    }

    // Check cache status on page load
    async function checkCacheStatus() {
        try {
            let hasPreprocessCache = false;
            let hasTfidfCache = false;
            let estimatedTime = "~5 menit";
            let timeBreakdown = "Preprocess: 2-3min, TF-IDF: 1min, SMOTE: 1min, SVM: 1min";
            
            // Try to detect preprocessing cache from statistics endpoint
            try {
                const statsResp = await fetch('/api/statistics');
                const statsData = await statsResp.json();
                if (statsData.success && statsData.data) {
                    // If we can get stats, preprocessing might be cached
                    // (This is heuristic, not definitive)
                }
            } catch (e) {}
            
            // Check for TF-IDF cache
            try {
                const metricsResp = await fetch('/api/model-metrics');
                const metricsData = await metricsResp.json();
                
                if (metricsData.success && metricsData.data && metricsData.data.timestamp) {
                    const modelTime = new Date(metricsData.data.timestamp);
                    const now = new Date();
                    const hoursDiff = (now - modelTime) / (1000 * 60 * 60);
                    
                    if (hoursDiff < 2) {
                        hasTfidfCache = true;
                        document.getElementById('tfidf-cache-status').textContent = '✅ Ada (Fresh) (~saved 1 min)';
                        document.getElementById('tfidf-cache-status').style.color = '#4CAF50';
                        estimatedTime = "~3-4 menit";
                    }
                }
            } catch (e) {}
            
            // Update UI
            document.getElementById('preprocess-cache-status').innerHTML = 
                localStorage.getItem('preprocess_done') ? 
                '✅ Ada (Fresh) (~saved 2-3 min)' : 
                '❌ Tidak Ada (~2-3 min)';
            document.getElementById('preprocess-cache-status').style.color = 
                localStorage.getItem('preprocess_done') ? '#4CAF50' : '#f44336';
            
            document.getElementById('time-estimate').textContent = estimatedTime;
            
        } catch (error) {
            console.log('Cache check failed (non-critical):', error);
        }
    }

    // Mark preprocessing as done when user visits preprocessing page
    // (Check if page has preprocessing done indicator)
    try {
        if (document.location.pathname === '/preprocessing' || document.location.search.includes('preprocessed=1')) {
            localStorage.setItem('preprocess_done', 'true');
            localStorage.setItem('preprocess_time', new Date().toISOString());
        }
    } catch (e) {}

    // Check cache on page load
    window.addEventListener('DOMContentLoaded', checkCacheStatus);

    // Periodically update cache status
    setInterval(checkCacheStatus, 30000); // Every 30 seconds

    // Train button - attach event listener when DOM is ready (only once)
    window.addEventListener('DOMContentLoaded', function() {
        if (!trainModelListenerAttached) {
            console.log('DOMContentLoaded event fired');
            const trainBtn = document.getElementById('train-btn');
            console.log('Train button element:', trainBtn);
            if (trainBtn) {
                trainBtn.addEventListener('click', trainModel);
                trainModelListenerAttached = true;
                console.log('Click event listener attached to train button (once)');
            } else {
                console.error('Train button not found!');
            }
        }
    });

    async function trainModel() {
        console.log('trainModel function called');
        const kernel = 'rbf';  // Fixed to RBF
        const testSize = 10; // Fixed 90:10 split (10% test, 90% train)
        
        const trainBtn = document.getElementById('train-btn');
        trainBtn.disabled = true;
        trainBtn.textContent = 'Training...';

        document.getElementById('progress-section').style.display = 'block';
        document.getElementById('results-section').style.display = 'none';

        // Start elapsed time tracking
        trainingStartTime = Date.now();
        document.getElementById('elapsed-time').textContent = '0s';
        
        // Update elapsed time every 100ms
        elapsedTimeInterval = setInterval(updateElapsedTime, 100);

        let progress = 0;
        let elapsedSeconds = 0;
        
        const stages = [
            { progress: 0, message: '🔄 Memuat data...' },
            { progress: 15, message: '📝 Preprocessing...' },
            { progress: 35, message: '⚙️ TF-IDF Vectorization...' },
            { progress: 55, message: '⚖️ SMOTE Balancing...' },
            { progress: 75, message: '🤖 SVM Training...' },
            { progress: 90, message: '📊 Evaluasi & Saving...' }
        ];
        
        let currentStageIndex = 0;

        const progressInterval = setInterval(() => {
            // Update stage message
            for (let i = stages.length - 1; i >= 0; i--) {
                if (progress >= stages[i].progress) {
                    if (i !== currentStageIndex) {
                        currentStageIndex = i;
                        document.getElementById('process-stage').textContent = stages[i].message;
                    }
                    break;
                }
            }
            
            // Faster progress increment for parallel processing
            if (progress < 85) {
                progress += Math.random() * 5 + 1;  // Increased from 3+0.5
                progress = Math.min(progress, 84);
            }
            
            updateProgress(progress, 'Processing');
        }, 300);  // Faster updates (was 400)

        try {
            const response = await fetch('/api/train-model', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    kernel,
                    test_size: testSize
                })
            });

            const data = await response.json();

            clearInterval(progressInterval);
            clearInterval(elapsedTimeInterval);
            
            updateProgress(100, 'Completed!');
            document.getElementById('process-stage').textContent = '✅ Selesai!';
            
            // Calculate total time with validation
            let timeString = '0s';
            if (trainingStartTime && typeof trainingStartTime === 'number' && trainingStartTime > 0) {
                const elapsedMs = Date.now() - trainingStartTime;
                // Sanity check: elapsed time should be positive and less than 1 hour (3600000ms)
                if (elapsedMs > 0 && elapsedMs < 3600000) {
                    const totalTime = Math.floor(elapsedMs / 1000);
                    const minutes = Math.floor(totalTime / 60);
                    const seconds = totalTime % 60;
                    timeString = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
                } else {
                    // Fallback: extract from DOM elapsed-time display
                    const elapsedDisplay = document.getElementById('elapsed-time')?.textContent || '0s';
                    timeString = elapsedDisplay;
                }
            }
            
            if (data.success) {
                console.log('Training successful, data:', data.data);
                
                // Display results section immediately (fastest)
                document.getElementById('results-section').style.display = 'block';
                
                // Display metrics & matrix instantly
                displayResults(data.data);
                
                // Load wordcloud in background (don't wait for it)
                if (data.data.wordcloud) {
                    console.log('WordCloud found in response, displaying...');
                    displayWordCloudImage(data.data.wordcloud);
                } else {
                    console.log('No wordcloud in response, attempting API fetch...');
                    loadWordCloud(); // Async - will update when ready
                }
                
                checkCacheStatus(); // Update cache status for next training
                alert('✓ Model training berhasil!\n\nWaktu: ' + timeString + '\n\n🚀 OPTIMIZED: Faster with parallel SVM training!');
            } else {
                alert('✗ Error: ' + (data.message || 'Training failed'));
            }
        } catch (error) {
            clearInterval(progressInterval);
            clearInterval(elapsedTimeInterval);
            console.error('Error:', error);
            alert('✗ Error: ' + error.message);
        } finally {
            trainBtn.disabled = false;
            trainBtn.textContent = 'Mulai Training';
            trainingStartTime = null;
        }
    }

    function updateProgress(percent, text) {
        document.getElementById('progress-fill').style.width = percent + '%';
        document.getElementById('progress-text').textContent = text + ' (' + Math.round(percent) + '%)';
    }

    function displayResults(data) {
        // Config
        document.getElementById('result-kernel').textContent = data.kernel || '-';
        document.getElementById('result-total').textContent = (data.total_samples || 0).toLocaleString();
        document.getElementById('result-train').textContent = (data.train_samples || 0).toLocaleString();
        document.getElementById('result-test').textContent = (data.test_samples || 0).toLocaleString();
        document.getElementById('result-features').textContent = (data.features || 0).toLocaleString();

        // Metrics
        const accuracy = parseFloat(data.accuracy || 0);
        const precision = parseFloat(data.precision || 0);
        const recall = parseFloat(data.recall || 0);
        const f1 = parseFloat(data.f1_score || 0);

        document.getElementById('metric-accuracy').textContent = (accuracy * 100).toFixed(2) + '%';
        document.getElementById('metric-precision').textContent = (precision * 100).toFixed(2) + '%';
        document.getElementById('metric-recall').textContent = (recall * 100).toFixed(2) + '%';
        document.getElementById('metric-f1').textContent = (f1 * 100).toFixed(2) + '%';

        // Per-class metrics
        const perClass = data.per_class_metrics || {};
        const tbody = document.getElementById('per-class-tbody');
        tbody.innerHTML = '';

        for (const [className, metrics] of Object.entries(perClass)) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${className}</strong></td>
                <td>${(metrics.precision * 100).toFixed(2)}%</td>
                <td>${(metrics.recall * 100).toFixed(2)}%</td>
                <td>${(metrics['f1-score'] * 100).toFixed(2)}%</td>
                <td>${metrics.support || 0}</td>
            `;
            tbody.appendChild(row);
        }

        // Confusion Matrix
        const cm = data.confusion_matrix || [[0, 0, 0], [0, 0, 0], [0, 0, 0]];
        const classes = data.classes || ['Class 0', 'Class 1', 'Class 2'];

        // Update CM header and labels
        for (let i = 0; i < 3; i++) {
            document.getElementById('cm-header-' + i).textContent = classes[i] || 'Class ' + i;
            document.getElementById('cm-label-' + i).textContent = classes[i] || 'Class ' + i;
        }

        // Fill CM values
        for (let i = 0; i < 3; i++) {
            for (let j = 0; j < 3; j++) {
                const cellId = 'cm-' + i + j;
                document.getElementById(cellId).textContent = cm[i]?.[j] || 0;
            }
        }

        // If confusion matrix image available from Python, display it
        if (data.confusion_matrix_image) {
            displayConfusionMatrixImage(data.confusion_matrix_image);
        } else {
            // Fallback: display HTML heatmap
            updateConfusionMatrixHeatmap(cm);
        }
    }

    function displayConfusionMatrixImage(base64Image) {
        try {
            // Find the CM container (the first column in grid)
            const cmContainer = document.querySelector('#wordcloud-container').parentElement.previousElementSibling;
            if (!cmContainer) {
                console.log('CM container not found, using HTML heatmap fallback');
                return;
            }

            // Get the flex container inside CM section
            const flexContainer = cmContainer.querySelector('div[style*="display: flex"]');
            if (!flexContainer) {
                console.log('Flex container not found');
                return;
            }

            // Create image element
            const img = document.createElement('img');
            img.src = 'data:image/png;base64,' + base64Image;
            img.style.cssText = 'max-width: 100%; height: auto; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
            img.alt = 'Confusion Matrix Heatmap';

            // Clear and replace with image
            flexContainer.innerHTML = '';
            flexContainer.appendChild(img);
            flexContainer.style.justifyContent = 'center';
            flexContainer.style.padding = '20px';

            console.log('✓ Confusion matrix image displayed');
        } catch (error) {
            console.error('Error displaying CM image:', error);
        }
    }

    function updateConfusionMatrixHeatmap(cm) {
        // Find max value for color scaling
        const maxVal = Math.max(...cm.flat(), 1);
        
        // Better color gradient: white → light blue → blue → dark blue
        const getHeatmapColor = (value) => {
            const normalized = value / maxVal; // 0 to 1
            
            // Define gradient stops (like matplotlib 'Blues')
            const stops = [
                { pos: 0.0, color: [240, 248, 255] },      // alice blue
                { pos: 0.2, color: [173, 216, 230] },      // light blue
                { pos: 0.4, color: [135, 206, 235] },      // sky blue
                { pos: 0.6, color: [65, 165, 245] },       // blue
                { pos: 0.8, color: [21, 101, 192] },       // darker blue
                { pos: 1.0, color: [13, 71, 161] }         // dark blue
            ];
            
            // Find the two stops to interpolate between
            let lower = stops[0];
            let upper = stops[stops.length - 1];
            
            for (let i = 0; i < stops.length - 1; i++) {
                if (normalized >= stops[i].pos && normalized <= stops[i + 1].pos) {
                    lower = stops[i];
                    upper = stops[i + 1];
                    break;
                }
            }
            
            // Interpolate between lower and upper
            const range = upper.pos - lower.pos;
            const t = (normalized - lower.pos) / range;
            
            const r = Math.round(lower.color[0] + (upper.color[0] - lower.color[0]) * t);
            const g = Math.round(lower.color[1] + (upper.color[1] - lower.color[1]) * t);
            const b = Math.round(lower.color[2] + (upper.color[2] - lower.color[2]) * t);
            
            return `rgb(${r}, ${g}, ${b})`;
        };
        
        const getTextColor = (value, maxVal) => {
            const normalized = value / maxVal;
            return normalized > 0.5 ? '#ffffff' : '#333333';
        };
        
        // Apply heatmap to each cell
        for (let i = 0; i < 3; i++) {
            for (let j = 0; j < 3; j++) {
                const cellId = 'cm-' + i + j;
                const cellElement = document.getElementById(cellId);
                const value = cm[i]?.[j] || 0;
                
                // Apply background color
                const bgColor = getHeatmapColor(value);
                cellElement.style.backgroundColor = bgColor;
                cellElement.style.boxShadow = '0 1px 3px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.2)';
                
                // Apply text color for contrast
                cellElement.style.color = getTextColor(value, maxVal);
                
                // Add hover effect
                cellElement.style.cursor = 'pointer';
                cellElement.onmouseover = function() {
                    this.style.transform = 'scale(1.05)';
                    this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.2)';
                };
                cellElement.onmouseout = function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.2)';
                };
            }
        }
    }

    function loadWordCloud() {
        fetch('/api/wordcloud-image', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.image) {
                console.log('Wordcloud image loaded');
                displayWordCloudImage(data.data.image);
            } else {
                console.error('No wordcloud image:', data);
                document.getElementById('wordcloud-container').innerHTML = 
                    '<div style="text-align: center; padding: 40px; color: #999;"><p>⚠️ Wordcloud belum tersedia</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading wordcloud:', error);
            document.getElementById('wordcloud-container').innerHTML = 
                '<div style="text-align: center; padding: 40px; color: #999;"><p>⚠️ Error: ' + error.message + '</p></div>';
        });
    }

    function displayWordCloudImage(base64Image) {
        if (!base64Image) {
            console.error('No base64 image provided');
            document.getElementById('wordcloud-container').innerHTML = 
                '<div style="text-align: center; padding: 40px; color: #999;"><p>⚠️ WordCloud generation gagal</p></div>';
            return;
        }
        
        const container = document.getElementById('wordcloud-container');
        const img = document.createElement('img');
        img.src = 'data:image/png;base64,' + base64Image;
        img.style.maxWidth = '100%';
        img.style.maxHeight = '600px';
        img.style.borderRadius = '4px';
        img.alt = 'Wordcloud Image';
        
        container.innerHTML = '';
        container.appendChild(img);
        console.log('WordCloud image displayed successfully');
    }

    // Sentiment Prediction Functions
    window.addEventListener('DOMContentLoaded', function() {
        const predictBtn = document.getElementById('predict-btn');
        if (predictBtn) {
            predictBtn.addEventListener('click', predictSentiment);
        }
    });

    async function predictSentiment() {
        const textInput = document.getElementById('predict-text').value.trim();
        const predictBtn = document.getElementById('predict-btn');

        if (!textInput) {
            showPredictionError('Masukkan teks terlebih dahulu!');
            return;
        }

        predictBtn.disabled = true;
        predictBtn.textContent = 'Sedang Memprediksi...';
        document.getElementById('prediction-error').style.display = 'none';
        document.getElementById('prediction-result').style.display = 'none';

        try {
            const response = await fetch('/api/predict-sentiment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    text: textInput
                })
            });

            const data = await response.json();

            if (data.success) {
                displayPredictionResult(data.data);
            } else {
                showPredictionError(data.message || 'Prediksi gagal');
            }
        } catch (error) {
            console.error('Error:', error);
            showPredictionError(error.message);
        } finally {
            predictBtn.disabled = false;
            predictBtn.textContent = 'Prediksi Sentimen';
        }
    }

    function displayPredictionResult(data) {
        console.log('Prediction result:', data);

        // Display sentiment and confidence
        const sentimentMap = {
            'Negatif': '😢 Negatif',
            'Netral': '😐 Netral',
            'Positif': '😊 Positif',
            0: '😢 Negatif',
            1: '😐 Netral',
            2: '😊 Positif'
        };

        const sentiment = sentimentMap[data.prediction] || 'Tidak Diketahui';
        const confidence = (data.confidence * 100).toFixed(2);

        document.getElementById('predict-sentiment').textContent = sentiment;
        document.getElementById('predict-confidence').textContent = confidence + '%';
        document.getElementById('predict-input-text').textContent = data.text;

        // Display probability distribution
        const probBarsContainer = document.getElementById('probability-bars');
        probBarsContainer.innerHTML = '';

        const classLabels = data.classes || ['Negatif', 'Netral', 'Positif'];
        const probabilities = data.probabilities || [0, 0, 0];
        const maxProb = Math.max(...probabilities);

        const colors = ['#f44336', '#FF9800', '#4CAF50'];

        for (let i = 0; i < classLabels.length; i++) {
            const prob = probabilities[i];
            const percent = (prob * 100).toFixed(2);
            const isMaxProb = prob === maxProb;

            const barHtml = `
                <div style="display: grid; grid-template-columns: 100px 1fr 80px; gap: 15px; align-items: center;">
                    <div style="font-size: 13px; font-weight: 600; color: #333;">${classLabels[i]}</div>
                    <div style="background: #e0e0e0; height: 24px; border-radius: 4px; overflow: hidden; position: relative;">
                        <div style="background: ${colors[i]}; height: 100%; width: ${percent}%; transition: width 0.3s ease; ${isMaxProb ? 'box-shadow: 0 0 8px ' + colors[i] + '80;' : ''}"></div>
                    </div>
                    <div style="text-align: right; font-size: 13px; font-weight: 600; color: ${colors[i]};">${percent}%</div>
                </div>
            `;

            probBarsContainer.innerHTML += barHtml;
        }

        document.getElementById('prediction-result').style.display = 'block';
    }

    function showPredictionError(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('prediction-error').style.display = 'block';
    }</script>

@endsection
