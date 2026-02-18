

<?php $__env->startSection('title', 'Klasifikasi SVM'); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="page-header">
        <h1>Klasifikasi Sentimen dengan SVM</h1>
        <p>Lakukan training model SVM untuk klasifikasi sentimen menggunakan data yang sudah dipreprocessing.</p>
    </div>

    <!-- Training Control Section -->
    <div class="section-card">
        <h2 class="section-title">Parameter Training</h2>
        
        <div class="form-group">
            <label class="form-label">Pilih Kernel SVM</label>
            <select id="kernel-select" class="form-input">
                <option value="rbf" selected>RBF (Radial Basis Function) - Default</option>
                <option value="linear">Linear</option>
                <option value="polynomial">Polynomial</option>
                <option value="sigmoid">Sigmoid</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Ukuran Test Set (%): <span id="test-size-display">10</span>%</label>
            <input type="range" id="test-size" class="form-input" min="10" max="50" value="10" step="5">
        </div>

        <div class="form-group">
            <button id="train-btn" class="btn btn-primary">Mulai Training</button>
        </div>
    </div>

    <!-- Training Progress Section -->
    <div id="progress-section" class="section-card" style="display: none;">
        <h2 class="section-title">Status Training</h2>
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

        <!-- Confusion Matrix -->
        <div class="section-card">
            <h2 class="section-title">Confusion Matrix Heatmap</h2>
            <div style="display: flex; justify-content: center;">
                <canvas id="cm-canvas" style="border: 1px solid #ddd;"></canvas>
            </div>
            <table class="cm-table">
                <thead>
                    <tr>
                        <th colspan="2">Predicted / Actual</th>
                        <td colspan="3" style="text-align: center;">Predicted</td>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
                        <td id="cm-header-0">Class 0</td>
                        <td id="cm-header-1">Class 1</td>
                        <td id="cm-header-2">Class 2</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="3" style="writing-mode: vertical-rl; transform: rotate(180deg);">Actual</td>
                        <td id="cm-label-0">Class 0</td>
                        <td id="cm-00">0</td>
                        <td id="cm-01">0</td>
                        <td id="cm-02">0</td>
                    </tr>
                    <tr>
                        <td id="cm-label-1">Class 1</td>
                        <td id="cm-10">0</td>
                        <td id="cm-11">0</td>
                        <td id="cm-12">0</td>
                    </tr>
                    <tr>
                        <td id="cm-label-2">Class 2</td>
                        <td id="cm-20">0</td>
                        <td id="cm-21">0</td>
                        <td id="cm-22">0</td>
                    </tr>
                </tbody>
            </table>
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

    // Update test size display
    document.getElementById('test-size').addEventListener('input', function(e) {
        document.getElementById('test-size-display').textContent = e.target.value;
    });

    // Train button
    document.getElementById('train-btn').addEventListener('click', trainModel);

    async function trainModel() {
        const kernel = document.getElementById('kernel-select').value;
        const testSize = parseInt(document.getElementById('test-size').value);
        
        const trainBtn = document.getElementById('train-btn');
        trainBtn.disabled = true;
        trainBtn.textContent = 'Training...';

        document.getElementById('progress-section').style.display = 'block';
        document.getElementById('results-section').style.display = 'none';

        let progress = 0;
        const progressInterval = setInterval(() => {
            progress = Math.min(progress + Math.random() * 20, 95);
            updateProgress(progress, 'Processing...');
        }, 400);

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
            updateProgress(100, 'Completed!');

            if (data.success) {
                displayResults(data.data);
                document.getElementById('results-section').style.display = 'block';
                alert('✓ Model training berhasil!');
            } else {
                alert('✗ Error: ' + (data.message || 'Training failed'));
            }
        } catch (error) {
            clearInterval(progressInterval);
            console.error('Error:', error);
            alert('✗ Error: ' + error.message);
        } finally {
            trainBtn.disabled = false;
            trainBtn.textContent = 'Mulai Training';
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

        // Draw heatmap
        drawConfusionMatrixHeatmap(cm, classes);
    }

    function drawConfusionMatrixHeatmap(cm, classes) {
        const canvas = document.getElementById('cm-canvas');
        const ctx = canvas.getContext('2d');
        const cellSize = 80;
        const padding = 70;
        const fontSize = 14;

        canvas.width = 3 * cellSize + padding;
        canvas.height = 3 * cellSize + padding + 20;

        const maxVal = Math.max(...cm.flat());

        // Draw background
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.font = 'bold ' + fontSize + 'px Arial';
        ctx.fillStyle = '#666';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Draw top labels
        for (let i = 0; i < 3; i++) {
            ctx.fillText(classes[i], padding + (i + 0.5) * cellSize, 25);
        }

        // Draw left labels
        for (let i = 0; i < 3; i++) {
            ctx.fillText(classes[i], 35, padding + (i + 0.5) * cellSize);
        }

        // Draw cells
        for (let i = 0; i < 3; i++) {
            for (let j = 0; j < 3; j++) {
                const x = padding + j * cellSize;
                const y = padding + i * cellSize;
                const value = cm[i][j];
                const intensity = maxVal > 0 ? value / maxVal : 0;

                // Cell background
                ctx.fillStyle = `rgba(33, 150, 243, ${intensity * 0.7 + 0.1})`;
                ctx.fillRect(x, y, cellSize, cellSize);

                // Cell border
                ctx.strokeStyle = '#ddd';
                ctx.lineWidth = 1;
                ctx.strokeRect(x, y, cellSize, cellSize);

                // Value text
                ctx.fillStyle = intensity > 0.5 ? 'white' : '#333';
                ctx.font = 'bold 16px Arial';
                ctx.fillText(value, x + cellSize / 2, y + cellSize / 2);
            }
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laragon\www\analisis_sentimen_TA\resources\views/klasifikasi.blade.php ENDPATH**/ ?>