@extends('layouts.app')

@section('title', 'Klasifikasi')

@section('content')
<div class="content">
    <div class="page-header">
        <h1>Klasifikasi Sentimen</h1>
    </div>

    <!-- Model Training Section -->
    <div class="section-card">
        <h2 class="section-title">Training Model SVM</h2>

        <div class="training-section">
            <div class="form-group">
                <label for="kernel-select" class="form-label">Pilih Kernel</label>
                <select id="kernel-select" class="form-input">
                    <option value="linear">Linear</option>
                    <option value="rbf">RBF (Radial Basis Function)</option>
                    <option value="polynomial">Polynomial</option>
                    <option value="sigmoid">Sigmoid</option>
                </select>
            </div>

            <div class="form-group">
                <label for="test-size" class="form-label">Ukuran Test Set (%)</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <input type="range" id="test-size" class="form-range" min="10" max="50" value="20" step="5">
                    <span class="test-size-value">20%</span>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" id="train-model-btn">Train Model</button>
                <button class="btn btn-secondary" id="load-model-btn">Load Model</button>
            </div>
        </div>

        <!-- Training Progress -->
        <div id="training-progress" style="display: none; margin-top: 30px;">
            <h3 style="margin-bottom: 15px;">Status Training</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 0%"></div>
            </div>
            <p class="progress-text">Initializing...</p>
        </div>
    </div>

    <!-- Model Evaluation Section -->
    <div class="section-card">
        <h2 class="section-title">Evaluasi Model</h2>

        <div class="metrics-grid">
            <div class="metric-card">
                <h3>Akurasi</h3>
                <p class="metric-value">85.5%</p>
                <p class="metric-label">Model Accuracy</p>
            </div>
            <div class="metric-card">
                <h3>Presisi</h3>
                <p class="metric-value">87.2%</p>
                <p class="metric-label">Precision Score</p>
            </div>
            <div class="metric-card">
                <h3>Recall</h3>
                <p class="metric-value">83.8%</p>
                <p class="metric-label">Recall Score</p>
            </div>
            <div class="metric-card">
                <h3>F1-Score</h3>
                <p class="metric-value">85.4%</p>
                <p class="metric-label">F1 Score</p>
            </div>
        </div>
    </div>

    <!-- Confusion Matrix Section -->
    <div class="section-card">
        <h2 class="section-title">Confusion Matrix</h2>

        <div class="matrix-container">
            <table class="confusion-matrix">
                <thead>
                    <tr>
                        <th colspan="2"></th>
                        <th colspan="3" style="text-align: center;">Predicted</th>
                    </tr>
                    <tr>
                        <th colspan="2">Actual</th>
                        <th>Positif</th>
                        <th>Negatif</th>
                        <th>Netral</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th rowspan="3">Actual</th>
                        <td>Positif</td>
                        <td class="matrix-true">45</td>
                        <td class="matrix-false">5</td>
                        <td class="matrix-false">2</td>
                    </tr>
                    <tr>
                        <td>Negatif</td>
                        <td class="matrix-false">4</td>
                        <td class="matrix-true">48</td>
                        <td class="matrix-false">3</td>
                    </tr>
                    <tr>
                        <td>Netral</td>
                        <td class="matrix-false">2</td>
                        <td class="matrix-false">3</td>
                        <td class="matrix-true">11</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Test Prediction Section -->
    <div class="section-card">
        <h2 class="section-title">Test Prediksi</h2>

        <div class="test-section">
            <div class="form-group">
                <label for="test-text" class="form-label">Masukkan Teks untuk Prediksi</label>
                <textarea id="test-text" class="form-textarea" placeholder="Ketikkan review di sini..."></textarea>
            </div>

            <button class="btn btn-primary" id="predict-btn">Prediksi Sentimen</button>

            <div id="prediction-result" style="display: none; margin-top: 20px;">
                <div class="prediction-box">
                    <h3 style="margin: 0 0 15px 0;">Hasil Prediksi</h3>
                    <p style="margin: 0 0 10px 0; color: #999;">Teks:</p>
                    <p id="result-text" style="margin: 0 0 20px 0; background: #f5f5f5; padding: 15px; border-radius: 4px;"></p>
                    
                    <p style="margin: 0 0 10px 0; color: #999;">Sentimen:</p>
                    <div class="prediction-sentiment">
                        <div class="sentiment-badge" id="sentiment-badge"></div>
                    </div>

                    <p style="margin: 20px 0 10px 0; color: #999;">Confidence Score:</p>
                    <div class="confidence-bar">
                        <div class="confidence-fill" id="confidence-fill"></div>
                    </div>
                    <p id="confidence-text" style="text-align: right; margin: 5px 0 0 0; font-weight: 600;"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .section-card {
        background: white;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 30px;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 10px;
    }

    .form-input,
    .form-range,
    .form-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
    }

    .form-input:focus,
    .form-range:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #2196F3;
        box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .test-size-value {
        min-width: 40px;
        text-align: right;
        font-weight: 600;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #2196F3;
        color: white;
    }

    .btn-primary:hover {
        background: #1976D2;
    }

    .btn-secondary {
        background: #f5f5f5;
        color: #666;
        border: 1px solid #e0e0e0;
    }

    .btn-secondary:hover {
        background: #f0f0f0;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    /* Progress Bar */
    .progress-bar {
        width: 100%;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50, #45a049);
        transition: width 0.3s ease;
    }

    .progress-text {
        margin: 0;
        font-size: 13px;
        color: #666;
    }

    /* Metrics Grid */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .metric-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 8px;
        text-align: center;
    }

    .metric-card h3 {
        margin: 0 0 15px 0;
        font-size: 14px;
        font-weight: 600;
        opacity: 0.9;
    }

    .metric-value {
        margin: 0 0 8px 0;
        font-size: 36px;
        font-weight: 700;
    }

    .metric-label {
        margin: 0;
        font-size: 12px;
        opacity: 0.85;
    }

    /* Confusion Matrix */
    .matrix-container {
        overflow-x: auto;
    }

    .confusion-matrix {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .confusion-matrix th,
    .confusion-matrix td {
        padding: 12px;
        text-align: center;
        border: 1px solid #e0e0e0;
        font-size: 14px;
        font-weight: 500;
    }

    .confusion-matrix th {
        background: #f5f5f5;
        color: #1a1a1a;
    }

    .confusion-matrix td {
        background: white;
    }

    .matrix-true {
        background: #e8f5e9;
        color: #2e7d32;
        font-weight: 600;
    }

    .matrix-false {
        background: #ffebee;
        color: #c62828;
    }

    /* Prediction Result */
    .prediction-box {
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 20px;
    }

    .prediction-sentiment {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .sentiment-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 16px;
        color: white;
        min-width: 150px;
    }

    .sentiment-badge.positif {
        background: linear-gradient(135deg, #4CAF50, #45a049);
    }

    .sentiment-badge.negatif {
        background: linear-gradient(135deg, #f44336, #da190b);
    }

    .sentiment-badge.netral {
        background: linear-gradient(135deg, #ff9800, #f57c00);
    }

    /* Confidence Bar */
    .confidence-bar {
        width: 100%;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }

    .confidence-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50, #45a049);
        transition: width 0.3s ease;
    }

    #confidence-text {
        font-size: 13px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .section-card {
            padding: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .metrics-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .confusion-matrix th,
        .confusion-matrix td {
            padding: 8px;
            font-size: 12px;
        }

        .metric-value {
            font-size: 28px;
        }
    }
</style>

<script>
    // Range slider
    document.getElementById('test-size').addEventListener('input', function(e) {
        document.querySelector('.test-size-value').textContent = e.target.value + '%';
    });

    // Train model
    document.getElementById('train-model-btn').addEventListener('click', function() {
        const progressDiv = document.getElementById('training-progress');
        progressDiv.style.display = 'block';
        
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
            }
            document.querySelector('.progress-fill').style.width = progress + '%';
            
            const status = progress < 50 ? 'Data preparation...' : 
                          progress < 80 ? 'Training SVM...' : 
                          progress < 100 ? 'Evaluating model...' : 
                          'Training completed!';
            document.querySelector('.progress-text').textContent = status;
        }, 500);
    });

    // Predict sentiment
    document.getElementById('predict-btn').addEventListener('click', function() {
        const text = document.getElementById('test-text').value;
        if (!text.trim()) {
            alert('Silakan masukkan teks untuk prediksi');
            return;
        }

        const sentiments = ['positif', 'negatif', 'netral'];
        const sentiment = sentiments[Math.floor(Math.random() * sentiments.length)];
        const confidence = Math.random() * 0.3 + 0.7;

        document.getElementById('result-text').textContent = text;
        
        const badge = document.getElementById('sentiment-badge');
        badge.textContent = sentiment.charAt(0).toUpperCase() + sentiment.slice(1);
        badge.className = 'sentiment-badge ' + sentiment;

        document.getElementById('confidence-fill').style.width = (confidence * 100) + '%';
        document.getElementById('confidence-text').textContent = (confidence * 100).toFixed(2) + '%';

        document.getElementById('prediction-result').style.display = 'block';
    });

    // Load model
    document.getElementById('load-model-btn').addEventListener('click', function() {
        alert('Loading model dari storage...');
    });
</script>
@endsection
