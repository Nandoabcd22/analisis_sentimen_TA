

<?php $__env->startSection('title', 'Hasil dan Laporan'); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="page-header">
        <h1>Hasil dan Laporan</h1>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Review</p>
                <p class="stat-value">150</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon positif">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Sentimen Positif</p>
                <p class="stat-value">65</p>
                <p class="stat-percentage">43.3%</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon negatif">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Sentimen Negatif</p>
                <p class="stat-value">72</p>
                <p class="stat-percentage">48%</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon netral">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-13c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 9c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Sentimen Netral</p>
                <p class="stat-value">13</p>
                <p class="stat-percentage">8.7%</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-card">
            <h2 class="chart-title">Distribusi Sentimen</h2>
            <canvas id="sentiment-chart" height="100"></canvas>
        </div>

        <div class="chart-card">
            <h2 class="chart-title">Sentiment Trend</h2>
            <canvas id="trend-chart" height="100"></canvas>
        </div>
    </div>

    <!-- Detailed Results Table -->
    <div class="section-card">
        <h2 class="section-title">Hasil Klasifikasi Detail</h2>

        <div class="table-controls">
            <div class="entries-control">
                <span>Show</span>
                <select id="entries-per-page" class="entries-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>entries</span>
            </div>
            <div class="search-control">
                <span>Search:</span>
                <input type="text" id="search-input" class="search-input">
            </div>
        </div>

        <div class="table-container">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Review</th>
                        <th>Hasil Preprocessing</th>
                        <th>Sentimen</th>
                        <th>Confidence</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>@penggun1</td>
                        <td>Tempatnya kotor dan tidak terawat...</td>
                        <td>[tempat, kotor, terawat, kecewa, banget]</td>
                        <td><span class="badge negatif">Negatif</span></td>
                        <td><span class="confidence positif">92.5%</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>@penggun3</td>
                        <td>Tempatnya kotor dan tidak terawat...</td>
                        <td>[tempat, kotor, terawat, kecewa, banget]</td>
                        <td><span class="badge negatif">Negatif</span></td>
                        <td><span class="confidence positif">88.3%</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>@penggun2</td>
                        <td>Cukup bagus, tapi terlihat ramai saat...</td>
                        <td>[cukup, bagus, ramai, weekend]</td>
                        <td><span class="badge netral">Netral</span></td>
                        <td><span class="confidence positif">76.8%</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <span class="pagination-info">Showing 1 to 3 of 150 entries</span>
            <div class="pagination">
                <button class="pagination-btn" disabled>Previous</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">Next</button>
            </div>
        </div>
    </div>

    <!-- Report Section -->
    <div class="section-card">
        <h2 class="section-title">Generate Laporan</h2>

        <div class="report-options">
            <div class="report-option">
                <h3>Laporan PDF</h3>
                <p>Download laporan lengkap dalam format PDF termasuk charts dan tabel hasil klasifikasi.</p>
                <button class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    Download PDF
                </button>
            </div>

            <div class="report-option">
                <h3>Laporan Excel</h3>
                <p>Download data hasil klasifikasi dalam format Excel untuk analisis lebih lanjut.</p>
                <button class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    Download Excel
                </button>
            </div>

            <div class="report-option">
                <h3>Laporan CSV</h3>
                <p>Download data hasil klasifikasi dalam format CSV untuk integrasi dengan tools lain.</p>
                <button class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    Download CSV
                </button>
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

    /* Statistics Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon svg {
        width: 32px;
        height: 32px;
    }

    .stat-icon.positif {
        background: linear-gradient(135deg, #4CAF50, #45a049);
    }

    .stat-icon.negatif {
        background: linear-gradient(135deg, #f44336, #da190b);
    }

    .stat-icon.netral {
        background: linear-gradient(135deg, #ff9800, #f57c00);
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        margin: 0;
        font-size: 14px;
        color: #999;
        font-weight: 500;
    }

    .stat-value {
        margin: 5px 0 0 0;
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .stat-percentage {
        margin: 5px 0 0 0;
        font-size: 13px;
        color: #4CAF50;
        font-weight: 600;
    }

    /* Charts Section */
    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    .chart-card {
        background: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .chart-title {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0 0 20px 0;
    }

    /* Section Card */
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

    /* Table Controls */
    .table-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 20px;
    }

    .entries-control,
    .search-control {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #666;
    }

    .entries-select,
    .search-input {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 10px;
        font-size: 14px;
    }

    .entries-select {
        width: 60px;
    }

    .search-input {
        width: 250px;
    }

    /* Table */
    .table-container {
        overflow-x: auto;
        margin-bottom: 20px;
    }

    .results-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .results-table thead {
        background: #f5f5f5;
    }

    .results-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1a1a1a;
        border-bottom: 2px solid #e0e0e0;
    }

    .results-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    .results-table tbody tr:hover {
        background: #fafafa;
    }

    .results-table tbody tr:nth-child(even) {
        background: #fafafa;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .badge.positif {
        background: #4CAF50;
    }

    .badge.negatif {
        background: #f44336;
    }

    .badge.netral {
        background: #ff9800;
    }

    .confidence {
        font-weight: 600;
    }

    .confidence.positif {
        color: #4CAF50;
    }

    /* Pagination */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        gap: 20px;
    }

    .pagination-info {
        font-size: 13px;
        color: #666;
    }

    .pagination {
        display: flex;
        gap: 5px;
    }

    .pagination-btn {
        padding: 6px 10px;
        border: 1px solid #ddd;
        background: white;
        color: #666;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .pagination-btn:hover:not(:disabled) {
        background: #f5f5f5;
    }

    .pagination-btn.active {
        background: #2196F3;
        color: white;
        border-color: #2196F3;
    }

    .pagination-btn:disabled {
        color: #ccc;
        cursor: not-allowed;
    }

    /* Report Options */
    .report-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
    }

    .report-option {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 25px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .report-option:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #2196F3;
    }

    .report-option h3 {
        margin: 0 0 10px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .report-option p {
        margin: 0 0 20px 0;
        font-size: 13px;
        color: #666;
        line-height: 1.5;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
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

    .btn svg {
        width: 18px;
        height: 18px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .charts-section {
            grid-template-columns: 1fr;
        }

        .stat-card {
            flex-direction: column;
            text-align: center;
        }

        .table-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .entries-control,
        .search-control {
            width: 100%;
        }

        .search-input,
        .entries-select {
            width: 100%;
        }

        .pagination-container {
            flex-direction: column;
            align-items: stretch;
        }

        .pagination {
            justify-content: center;
        }

        .report-options {
            grid-template-columns: 1fr;
        }

        .section-card {
            padding: 20px;
        }
    }
</style>

<script>
    // Chart initialization (using Chart.js library would be ideal)
    // For now, we'll create simple canvas-based visualization

    // Sentiment Distribution Chart
    const sentimentCtx = document.getElementById('sentiment-chart');
    if (sentimentCtx) {
        const ctx = sentimentCtx.getContext('2d');
        // Simple pie chart visualization
        const width = sentimentCtx.width;
        const height = sentimentCtx.height;
        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) / 2 - 20;

        ctx.fillStyle = '#4CAF50';
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, (65/150) * Math.PI * 2);
        ctx.lineTo(centerX, centerY);
        ctx.fill();

        ctx.fillStyle = '#f44336';
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, (65/150) * Math.PI * 2, ((65+72)/150) * Math.PI * 2);
        ctx.lineTo(centerX, centerY);
        ctx.fill();

        ctx.fillStyle = '#ff9800';
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, ((65+72)/150) * Math.PI * 2, Math.PI * 2);
        ctx.lineTo(centerX, centerY);
        ctx.fill();

        // Legend
        ctx.fillStyle = '#666';
        ctx.font = '12px Arial';
        ctx.fillText('Positif (43.3%)', 20, 20);
        ctx.fillText('Negatif (48%)', 20, 40);
        ctx.fillText('Netral (8.7%)', 20, 60);
    }

    // Search functionality
    document.getElementById('search-input').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const tableRows = document.querySelectorAll('.results-table tbody tr');
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Download buttons
    document.querySelectorAll('.btn-primary').forEach(btn => {
        btn.addEventListener('click', function() {
            const format = this.textContent.toLowerCase();
            alert('Downloading laporan dalam format ' + format);
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laragon\www\analisis_sentimen_TA\resources\views/hasil-laporan.blade.php ENDPATH**/ ?>