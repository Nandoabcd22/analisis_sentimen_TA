@extends('layouts.app')

@section('title', 'TF-IDF & Data Processing')

@section('content')
<div class="content">
    <div class="mb-10">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl shadow-2xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold tracking-tight mb-2">TF-IDF & Data Processing</h1>
                    <p class="text-blue-100 text-lg">📊 Visualisasi Data, Pembagian Training/Testing, dan SMOTE. <strong>Note:</strong> TF-IDF sekarang dihitung saat training dan di-reuse otomatis untuk mencegah duplikasi.</p>
                </div>
                <div class="flex gap-3">
                    <button id="process-tfidf-btn" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        <span id="tfidf-text">Process TF-IDF</span>
                    </button>
                    <button id="apply-smote-btn" class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-green-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span id="smote-text">Apply SMOTE</span>
                    </button>
                    <button id="download-tfidf-btn" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-purple-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Results
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Overview Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="section-card">
            <div class="section-header">
                <h3 class="section-title">Data Overview</h3>
            </div>
            <div id="data-overview">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Data:</span>
                        <span class="font-semibold" id="total-data">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Positif:</span>
                        <span class="font-semibold text-green-600" id="positif-count">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Negatif:</span>
                        <span class="font-semibold text-red-600" id="negatif-count">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Netral:</span>
                        <span class="font-semibold text-yellow-600" id="netral-count">-</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h3 class="section-title">Data Split</h3>
            </div>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600 mb-3">Fixed Data Split Ratio:</p>
                    <p class="text-sm font-semibold text-gray-700">Training: <span class="text-blue-600">90%</span> | Testing: <span class="text-orange-600">10%</span></p>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Training:</span>
                        <span class="font-semibold" id="train-count">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Testing:</span>
                        <span class="font-semibold" id="test-count">-</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h3 class="section-title">SMOTE Status</h3>
            </div>
            <div id="smote-status">
                <div class="text-center py-4">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-gray-500 text-sm">SMOTE not applied yet</p>
                </div>
            </div>
        </div>
    </div>

    <!-- TF-IDF Results Section -->
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">TF-IDF Results</h2>
            <div class="search-control">
                <span>Search:</span>
                <input type="text" id="search-input" class="search-input" placeholder="Search features...">
            </div>
        </div>

        <!-- Results Table -->
        <div class="table-container">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Feature</th>
                        <th>Category</th>
                        <th title="Term Frequency (normalized)">TF</th>
                        <th title="Inverse Document Frequency">IDF</th>
                        <th title="TF × IDF Score">TF-IDF</th>
                        <th title="Times appeared in document">Term Freq</th>
                        <th title="Documents containing term">Doc Freq</th>
                        <th title="Category Doc Frequency">Cat DF</th>
                        <th title="Percentage in category">Cat %</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan dimuat secara dinamis dari API -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <span class="pagination-info" id="pagination-info">Loading...</span>
            <div class="pagination" id="pagination-controls">
                <!-- Pagination akan dimuat secara dinamis -->
            </div>
        </div>
    </div>

    <!-- Data Distribution Chart -->
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">Data Distribution</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold mb-4">Original Distribution</h3>
                <div id="original-chart" class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                    <canvas id="originalCanvas"></canvas>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">After SMOTE Distribution</h3>
                <div id="smote-chart" class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                    <canvas id="smoteCanvas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentPage = 1;
let entriesPerPage = 10;
let tfidfData = [];
let smoteData = [];
let originalDistribution = null; // Store original distribution before SMOTE

// Load data overview
async function loadDataOverview() {
    try {
        const response = await fetch('/api/statistics');
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            document.getElementById('total-data').textContent = data.total || 0;
            document.getElementById('positif-count').textContent = data.positif || 0;
            document.getElementById('negatif-count').textContent = data.negatif || 0;
            document.getElementById('netral-count').textContent = data.netral || 0;
            
            // Save original distribution if not already saved
            if (!originalDistribution) {
                originalDistribution = {
                    positif: data.positif,
                    negatif: data.negatif,
                    netral: data.netral
                };
            }
            
            updateDataSplit();
            drawOriginalChart(originalDistribution);
        }
    } catch (error) {
        console.error('Error loading data overview:', error);
    }
}

// Update data split based on fixed 90:10 ratio
function updateDataSplit() {
    const trainSize = 90;  // Fixed: 90% training
    const total = parseInt(document.getElementById('total-data').textContent) || 0;
    
    const trainCount = Math.floor(total * trainSize / 100);
    const testCount = total - trainCount;
    
    document.getElementById('train-count').textContent = trainCount;
    document.getElementById('test-count').textContent = testCount;
}

// Draw original distribution chart
function drawOriginalChart(data) {
    const ctx = document.getElementById('originalCanvas').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Positif', 'Negatif', 'Netral'],
            datasets: [{
                label: 'Original Data',
                data: [data.positif, data.negatif, data.netral],
                backgroundColor: ['#10b981', '#ef4444', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Process TF-IDF
document.getElementById('process-tfidf-btn').addEventListener('click', async function() {
    const btn = this;
    const textSpan = document.getElementById('tfidf-text');
    
    btn.disabled = true;
    textSpan.textContent = 'Processing...';
    
    try {
        const response = await fetch('/process-tfidf', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('TF-IDF processing completed successfully!');
            loadTfidfResults();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('TF-IDF processing error:', error);
        alert('TF-IDF processing failed. Please try again.');
    } finally {
        btn.disabled = false;
        textSpan.textContent = 'Process TF-IDF';
    }
});

// Apply SMOTE
document.getElementById('apply-smote-btn').addEventListener('click', async function() {
    const btn = this;
    const textSpan = document.getElementById('smote-text');
    
    btn.disabled = true;
    textSpan.textContent = 'Applying SMOTE...';
    
    try {
        const response = await fetch('/apply-smote', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('SMOTE applied successfully!');
            updateSmoteStatus(result.data);
            drawSmoteChart(result.data);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('SMOTE application error:', error);
        alert('SMOTE application failed. Please try again.');
    } finally {
        btn.disabled = false;
        textSpan.textContent = 'Apply SMOTE';
    }
});

// Update SMOTE status
function updateSmoteStatus(data) {
    const statusDiv = document.getElementById('smote-status');
    const totalAfter = data.total_samples?.total || (data.original_total + data.synthetic_generated);
    const syntheticCount = data.synthetic_generated || data.total_samples?.synthetic || 0;
    
    statusDiv.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Original:</span>
                <span class="font-semibold">${data.original_total || 0}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">After SMOTE:</span>
                <span class="font-semibold text-green-600">${totalAfter}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Synthetic:</span>
                <span class="font-semibold text-blue-600">${syntheticCount}</span>
            </div>
            <div class="text-center pt-4">
                <div class="grid grid-cols-3 gap-2 text-center mb-3">
                    <div>
                        <p class="text-xs text-gray-500">Positif</p>
                        <p class="text-sm font-semibold text-green-600">${data.original_distribution?.Positif || 0}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Negatif</p>
                        <p class="text-sm font-semibold text-red-600">${data.original_distribution?.Negatif || 0}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Netral</p>
                        <p class="text-sm font-semibold text-blue-600">${data.original_distribution?.Netral || 0}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    SMOTE Applied
                </span>
            </div>
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> SMOTE Applied
                </span>
            </div>
        </div>
    `;
}

// Draw SMOTE chart
function drawSmoteChart(data) {
    const ctx = document.getElementById('smoteCanvas').getContext('2d');
    
    // Get distribution from new_distribution or fallback to old format
    const distribution = data.new_distribution || {
        'Positif': data.positif || 0,
        'Negatif': data.negatif || 0,
        'Netral': data.netral || 0
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Positif', 'Negatif', 'Netral'],
            datasets: [{
                label: 'After SMOTE',
                data: [distribution.Positif || 0, distribution.Negatif || 0, distribution.Netral || 0],
                backgroundColor: ['#10b981', '#ef4444', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Load TF-IDF results
async function loadTfidfResults() {
    const tbody = document.querySelector('.results-table tbody');
    const paginationInfo = document.getElementById('pagination-info');
    
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8">Loading data...</td></tr>';
    }
    if (paginationInfo) {
        paginationInfo.textContent = 'Loading...';
    }
    
    try {
        const response = await fetch(`/api/tfidf-results?page=${currentPage}&per_page=${entriesPerPage}&search=${document.getElementById('search-input')?.value || ''}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();

        if (result.success) {
            renderTfidfTable(result.data);
            updatePagination(result.pagination);
        } else {
            throw new Error(result.message || 'Failed to load data');
        }
    } catch (error) {
        console.error('Error loading TF-IDF results:', error);
        
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8 text-red-600">Error loading data. Please try again.</td></tr>';
        }
        if (paginationInfo) {
            paginationInfo.textContent = 'Error loading data';
        }
    }
}

// Render TF-IDF table
function renderTfidfTable(data) {
    const tbody = document.querySelector('.results-table tbody');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-8 text-gray-500">No TF-IDF data available. Please process TF-IDF first.</td></tr>';
        return;
    }

    data.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${(currentPage - 1) * entriesPerPage + index + 1}</td>
            <td class="max-w-xs truncate" title="${item.feature}">${item.feature}</td>
            <td class="text-center">
                <span class="label-badge ${item.category ? item.category.toLowerCase() : 'netral'}">${item.category || '-'}</span>
            </td>
            <td class="text-center">${item.tf ? item.tf.toFixed(6) : '-'}</td>
            <td class="text-center">${item.idf ? item.idf.toFixed(6) : '-'}</td>
            <td class="text-center font-semibold text-blue-600">${item.tfidf_score ? item.tfidf_score.toFixed(6) : '-'}</td>
            <td class="text-center">${item.term_frequency || 0}</td>
            <td class="text-center">${item.document_frequency || '-'}</td>
            <td class="text-center">${item.category_doc_frequency || '-'}</td>
            <td class="text-center">${item.category_percentage ? item.category_percentage.toFixed(2) + '%' : '-'}</td>
        `;
        tbody.appendChild(row);
    });
}

// Update pagination
function updatePagination(pagination) {
    const info = document.getElementById('pagination-info');
    const paginationDiv = document.getElementById('pagination-controls');
    
    if (!info || !paginationDiv) return;
    
    const start = (pagination.current_page - 1) * pagination.per_page + 1;
    const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
    
    info.textContent = `Showing ${start} to ${end} of ${pagination.total} entries`;
    
    // Clear existing pagination
    paginationDiv.innerHTML = '';
    
    // Previous button
    const prevBtn = createPaginationButton('Previous', pagination.current_page > 1, () => {
        if (pagination.current_page > 1) {
            currentPage--;
            loadTfidfResults();
        }
    });
    paginationDiv.appendChild(prevBtn);
    
    // Page numbers (max 5 pages)
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, startPage + 4);
    
    if (startPage > 1) {
        paginationDiv.appendChild(createPaginationButton('1', true, () => {
            currentPage = 1;
            loadTfidfResults();
        }));
        
        if (startPage > 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'pagination-dots';
            paginationDiv.appendChild(dots);
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        paginationDiv.appendChild(createPaginationButton(
            i.toString(), 
            true, 
            () => {
                currentPage = i;
                loadTfidfResults();
            },
            i === pagination.current_page
        ));
    }
    
    if (endPage < pagination.last_page) {
        if (endPage < pagination.last_page - 1) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'pagination-dots';
            paginationDiv.appendChild(dots);
        }
        
        paginationDiv.appendChild(createPaginationButton(
            pagination.last_page.toString(),
            true,
            () => {
                currentPage = pagination.last_page;
                loadTfidfResults();
            }
        ));
    }
    
    // Next button
    const nextBtn = createPaginationButton('Next', pagination.current_page < pagination.last_page, () => {
        if (pagination.current_page < pagination.last_page) {
            currentPage++;
            loadTfidfResults();
        }
    });
    paginationDiv.appendChild(nextBtn);
}

// Helper function to create pagination buttons
function createPaginationButton(text, enabled, onClick, isActive = false) {
    const button = document.createElement('button');
    button.className = `pagination-btn ${isActive ? 'active' : ''} ${!enabled ? 'disabled' : ''}`;
    button.textContent = text;
    button.disabled = !enabled;
    
    if (enabled && !isActive) {
        button.onclick = onClick;
    }
    
    return button;
}

// Download button handler
document.getElementById('download-tfidf-btn').addEventListener('click', function() {
    window.open('/api/tfidf-results?download=1', '_blank');
});

// Search functionality
const searchInput = document.getElementById('search-input');
if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadTfidfResults();
        }, 500);
    });
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDataOverview();
    loadTfidfResults();
});
</script>

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

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }

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
        padding: 8px 12px;
    }

    .search-input:focus,
    .entries-select:focus {
        outline: none;
        border-color: #2196F3;
        box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
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
        color: #333;
        border-bottom: 2px solid #ddd;
    }

    .results-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }

    .results-table tbody tr:hover {
        background: #f9f9f9;
    }

    /* Pagination */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding: 15px 0;
        border-top: 1px solid #eee;
    }

    .pagination-info {
        font-size: 14px;
        color: #666;
    }

    .pagination {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .pagination-btn {
        padding: 6px 10px;
        border: 1px solid #ddd;
        background: white;
        color: #333;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s;
    }

    .pagination-btn:hover:not(.disabled):not(.active) {
        background: #f5f5f5;
        border-color: #999;
    }

    .pagination-btn.active {
        background: #2196F3;
        color: white;
        border-color: #2196F3;
    }

    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-dots {
        padding: 6px 8px;
        color: #666;
        font-size: 13px;
    }

    /* Content wrapper */
    .content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* General utilities */
    .text-center {
        text-align: center;
    }

    .py-4 {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .text-red-600 {
        color: #dc2626;
    }

    .text-gray-500 {
        color: #6b7280;
    }

    .text-green-600 {
        color: #059669;
    }

    .text-yellow-600 {
        color: #d97706;
    }

    .max-w-xs {
        max-width: 20rem;
    }

    .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Label badges */
    .label-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .label-badge.positif {
        background: #d4edda;
        color: #155724;
    }
    
    .label-badge.negatif {
        background: #f8d7da;
        color: #721c24;
    }
    
    .label-badge.netral {
        background: #fff3cd;
        color: #856404;
    }

    /* Range slider */
    input[type="range"] {
        -webkit-appearance: none;
        appearance: none;
        background: transparent;
        cursor: pointer;
    }

    input[type="range"]::-webkit-slider-track {
        background: #ddd;
        height: 6px;
        border-radius: 3px;
    }

    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        background: #2196F3;
        height: 20px;
        width: 20px;
        border-radius: 50%;
        margin-top: -7px;
    }

    input[type="range"]::-moz-range-track {
        background: #ddd;
        height: 6px;
        border-radius: 3px;
    }

    input[type="range"]::-moz-range-thumb {
        background: #2196F3;
        height: 20px;
        width: 20px;
        border-radius: 50%;
        border: none;
    }

    /* Space utilities */
    .space-y-3 > * + * {
        margin-top: 0.75rem;
    }

    .space-y-4 > * + * {
        margin-top: 1rem;
    }

    .space-x-2 > * + * {
        margin-left: 0.5rem;
    }

    /* Grid utilities */
    .grid {
        display: grid;
    }

    .grid-cols-1 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    .grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .gap-6 {
        gap: 1.5rem;
    }

    @media (min-width: 1024px) {
        .lg\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .lg\:grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
</style>
@endsection
