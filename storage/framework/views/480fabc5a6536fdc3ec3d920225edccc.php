

<?php $__env->startSection('title', 'Preprocessing'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-10">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl shadow-2xl p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold tracking-tight mb-2">Preprocessing</h1>
                        <p class="text-blue-100 text-lg">Silahkan klik tombol di bawah ini untuk melakukan proses preprocessing dengan menggunakan data file yang sudah di upload sebelumnya</p>
                    </div>
                    <button id="preprocessBtn" class="inline-flex items-center gap-2 rounded-lg bg-white text-blue-600 px-6 py-3 text-sm font-medium shadow hover:bg-blue-50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Preprocessing
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-white rounded-3xl shadow-xl p-8 mt-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Hasil Preprocessing</h2>
            </div>
            
            <!-- Empty State -->
            <div id="emptyState" class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data</h3>
                <p class="mt-1 text-sm text-gray-500">Klik tombol Preprocessing untuk memproses data ulasan.</p>
            </div>
            
            <!-- Loading State -->
            <div id="loadingState" class="hidden text-center py-12">
                <svg class="animate-spin h-12 w-12 mx-auto text-blue-600 mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-900">Sedang memproses...</h3>
                <p class="mt-2 text-sm text-gray-600" id="processingStatus">
                    <span>Processing all data in batch mode...</span><br>
                    <span class="text-blue-600 font-semibold">⏱ 0s</span>
                </p>
                <div class="mt-4 bg-gray-200 rounded-full h-2 w-48 mx-auto overflow-hidden">
                    <div id="progressBar" class="bg-blue-600 h-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Table Section -->
            <div id="tableSection" class="hidden">
                <!-- Table Controls -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700">Show</label>
                        <select id="entriesPerPage" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Tampilkan Semua</option>
                        </select>
                        <label class="text-sm font-medium text-gray-700">entries</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700">Search:</label>
                        <input type="text" id="tableSearch" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search reviews...">
                    </div>
                </div>
                
                <!-- Table with Custom Scrollbar -->
                <div class="relative rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto overflow-y-auto max-h-96" id="tableWrapper">
                        <style>
                            #tableWrapper {
                                font-family: 'Times New Roman', serif;
                            }
                            #tableWrapper::-webkit-scrollbar {
                                height: 12px;
                                width: 12px;
                            }
                            #tableWrapper::-webkit-scrollbar-track {
                                background: #f1f5f9;
                                border-radius: 6px;
                            }
                            #tableWrapper::-webkit-scrollbar-thumb {
                                background: linear-gradient(180deg, #3b82f6 0%, #1e40af 100%);
                                border-radius: 6px;
                                border: 2px solid #f1f5f9;
                            }
                            #tableWrapper::-webkit-scrollbar-thumb:hover {
                                background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
                            }
                            #tableWrapper {
                                scrollbar-color: #3b82f6 #f1f5f9;
                                scrollbar-width: thin;
                            }
                            #preprocessTable {
                                font-family: 'Times New Roman', serif;
                                min-width: 100%;
                                width: max-content;
                            }
                            #preprocessTable thead th {
                                font-family: 'Times New Roman', serif;
                                min-width: 100px;
                            }
                            #preprocessTable tbody td {
                                font-family: 'Times New Roman', serif;
                                padding: 0.75rem 1rem;
                            }
                        </style>
                        <table class="border-collapse bg-white" id="preprocessTable">
                            <thead class="sticky top-0 bg-gradient-to-r from-blue-50 to-indigo-50 z-10">
                                <tr class="border-b-2 border-gray-300">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 whitespace-nowrap bg-blue-50 min-w-12">No</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 bg-blue-50 min-w-48">Review</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 whitespace-nowrap bg-blue-50 min-w-24">Label</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 bg-blue-50 min-w-40">Case Folding</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 bg-blue-50 min-w-36">Cleansing</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 bg-blue-50 min-w-40">Normalisasi</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 bg-blue-50 min-w-52">Tokenizing</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 bg-blue-50 min-w-52">Stopword</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-800 border border-gray-300 bg-blue-50 min-w-40">Stemming</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- Table content will be rendered by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div id="paginationSection" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-sm text-gray-700 font-medium" id="entriesInfo">
                        Showing <span class="font-bold text-blue-600">0</span> to <span class="font-bold text-blue-600">0</span> of <span class="font-bold text-blue-600">0</span> entries
                    </div>
                    <div class="flex items-center gap-2" id="paginationControls">
                        <!-- Pagination buttons will be generated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div id="errorState" class="hidden text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900" id="errorTitle">Terjadi kesalahan</h3>
                <p class="mt-1 text-sm text-gray-500" id="errorMessage">Silakan coba lagi.</p>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let entriesPerPage = 10;
let allPreprocessedData = [];
let preprocessingStartTime = null;
let elapsedTimeInterval = null;

function updateElapsedTime() {
    if (preprocessingStartTime) {
        const elapsed = Math.floor((Date.now() - preprocessingStartTime) / 1000);
        const seconds = elapsed % 60;
        const minutes = Math.floor(elapsed / 60);
        
        let timeString = '';
        if (minutes > 0) {
            timeString = `${minutes}m ${seconds}s`;
        } else {
            timeString = `${seconds}s`;
        }
        
        document.getElementById('processingStatus').innerHTML = 
            `<span>Processing all data in batch mode...</span><br><span class="text-blue-600 font-semibold">⏱ ${timeString}</span>`;
    }
}

function showLoadingState() {
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('tableSection').classList.add('hidden');
    document.getElementById('errorState').classList.add('hidden');
    document.getElementById('loadingState').classList.remove('hidden');
}

function showTableSection() {
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('errorState').classList.add('hidden');
    document.getElementById('tableSection').classList.remove('hidden');
}

function showErrorState(title, message) {
    document.getElementById('errorTitle').textContent = title;
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('tableSection').classList.add('hidden');
    document.getElementById('errorState').classList.remove('hidden');
}

function showEmptyState() {
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('tableSection').classList.add('hidden');
    document.getElementById('errorState').classList.add('hidden');
}

async function preprocessData() {
    showLoadingState();
    const preprocessBtn = document.getElementById('preprocessBtn');
    preprocessBtn.disabled = true;
    
    // Start timer
    preprocessingStartTime = Date.now();
    document.getElementById('processingStatus').innerHTML = 
        `<span>Processing all data in batch mode...</span><br><span class="text-blue-600 font-semibold">⏱ 0s</span>`;
    
    // Update elapsed time every 100ms
    elapsedTimeInterval = setInterval(updateElapsedTime, 100);
    
    try {
        console.log('Starting batch preprocessing of all data...');
        
        const response = await fetch('/preprocess-data', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        });

        console.log('Response status:', response.status);
        const result = await response.json();
        
        console.log('Preprocessing result:', result);

        if (response.ok && result.success) {
            // Calculate total time
            const totalTime = Math.floor((Date.now() - preprocessingStartTime) / 1000);
            const seconds = totalTime % 60;
            const minutes = Math.floor(totalTime / 60);
            let timeString = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
            
            alert(`✅ Preprocessing Complete!\n${result.message}\n⏱ Total time: ${timeString}`);
            // Load preprocessed reviews after processing
            currentPage = 1;
            await loadPreprocessedReviews();
        } else {
            const errorMsg = result.message || 'Preprocessing failed';
            console.error('Preprocessing failed:', result);
            showErrorState('Preprocessing Failed', errorMsg);
            alert('❌ Error: ' + errorMsg);
        }
    } catch (error) {
        console.error('Preprocessing error:', error);
        showErrorState('Error', error.message || 'Preprocessing error occurred');
        alert('❌ Error: ' + (error.message || 'Preprocessing error'));
    } finally {
        // Stop timer
        if (elapsedTimeInterval) {
            clearInterval(elapsedTimeInterval);
            elapsedTimeInterval = null;
        }
        preprocessingStartTime = null;
        preprocessBtn.disabled = false;
    }
}

async function loadPreprocessedReviews() {
    try {
        const searchValue = document.getElementById('tableSearch')?.value || '';
        const entriesValue = document.getElementById('entriesPerPage').value;
        const perPage = entriesValue === 'all' ? 10000 : parseInt(entriesValue);
        
        const response = await fetch(`/get-preprocessed-reviews?page=${currentPage}&per_page=${perPage}&search=${encodeURIComponent(searchValue)}`);
        const result = await response.json();

        console.log('Preprocessed data:', result);

        if (result.success && result.data && result.pagination) {
            if (Array.isArray(result.data) && result.data.length > 0) {
                showTableSection();
                renderTable(result.data, entriesValue === 'all');
                updatePaginationInfo(result.pagination);
                
                // Show/hide pagination based on entries setting
                const paginationSection = document.getElementById('paginationSection');
                if (entriesValue === 'all') {
                    paginationSection.classList.add('hidden');
                } else {
                    paginationSection.classList.remove('hidden');
                    updatePaginationControls(result.pagination);
                }
            } else {
                showEmptyState();
            }
        }
    } catch (error) {
        console.error('Error loading preprocessed reviews:', error);
        showErrorState('Error', 'Gagal memuat data preprocessing.');
    }
}

function renderTable(reviews, showAll = false) {
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';

    reviews.forEach((review, index) => {
        const row = document.createElement('tr');
        row.className = 'border-b border-gray-200 hover:bg-blue-50 transition-colors';

        // Jika menampilkan semua, gunakan index + 1, jika tidak gunakan pagination offset
        const rowNumber = showAll ? (index + 1) : ((currentPage - 1) * entriesPerPage + index + 1);

        // Tentukan warna label berdasarkan nilai label
        let labelColor = 'bg-gray-100 text-gray-700';
        let labelDot = 'bg-gray-500';
        
        if (review.label === 'Positif') {
            labelColor = 'bg-green-100 text-green-700';
            labelDot = 'bg-green-500';
        } else if (review.label === 'Negatif') {
            labelColor = 'bg-red-100 text-red-700';
            labelDot = 'bg-red-500';
        } else if (review.label === 'Netral') {
            labelColor = 'bg-blue-100 text-blue-700';
            labelDot = 'bg-blue-500';
        }

        row.innerHTML = `
            <td class="py-3 px-4 text-center border border-gray-300 whitespace-nowrap text-gray-600 font-medium">${rowNumber}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs text-gray-700">${escapeHtml(review.review || '')}</td>
            <td class="py-3 px-4 text-center border border-gray-300 whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${labelColor}">
                    <span class="w-2 h-2 ${labelDot} rounded-full"></span> ${review.label || 'Belum Diproses'}
                </span>
            </td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs text-gray-700">${escapeHtml(review.case_folding || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs text-gray-700">${escapeHtml(review.cleansing || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs text-gray-700">${escapeHtml(review.normalisasi || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs text-gray-700 font-mono">${formatTokens(review.tokenizing || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs text-gray-700 font-mono">${formatTokens(review.stopword || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs text-gray-700 font-mono">${formatStemming(review.stemming || '')}</td>
        `;
        tbody.appendChild(row);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTokens(tokenStr) {
    // Format tokens from JSON string with limit and expandable
    if (!tokenStr) return '';
    
    try {
        // Try to parse as JSON array
        let tokens = [];
        if (typeof tokenStr === 'string') {
            // If it looks like JSON
            if (tokenStr.startsWith('[')) {
                tokens = JSON.parse(tokenStr);
            } else {
                // Otherwise split by spaces
                tokens = tokenStr.split(/\s+/).filter(t => t.length > 0);
            }
        } else if (Array.isArray(tokenStr)) {
            tokens = tokenStr;
        }
        
        if (tokens.length === 0) return '[]';
        
        // Limit to first 3 items
        const limit = 3;
        const displayTokens = tokens.slice(0, limit);
        const remaining = tokens.length - limit;
        
        let result = '[';
        displayTokens.forEach((token, idx) => {
            const escapedToken = escapeHtml(String(token).trim());
            result += `"${escapedToken}"${idx < displayTokens.length - 1 ? ', ' : ''}`;
        });
        
        if (remaining > 0) {
            result += `, <span class="text-blue-600 font-semibold cursor-pointer hover:underline" onclick="showTokenModal(event, ${JSON.stringify(tokens).replace(/"/g, '&quot;')})">+${remaining} more</span>]`;
        } else {
            result += ']';
        }
        
        return result;
    } catch (e) {
        // Fallback: just escape and return as-is
        return escapeHtml(String(tokenStr));
    }
}

function showTokenModal(event, tokens) {
    event.stopPropagation();
    
    // Create modal HTML
    const modalId = 'tokenModal_' + Date.now();
    const modalHTML = `
        <div id="${modalId}" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-96 overflow-hidden flex flex-col">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">All Tokens (${tokens.length} total)</h3>
                    <button onclick="document.getElementById('${modalId}').remove()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-1 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto p-6 font-mono text-sm text-gray-700">
                    <div class="space-y-2">
                        ${tokens.map((token, idx) => `
                            <div class="flex items-start gap-3 p-2 hover:bg-blue-50 rounded transition">
                                <span class="text-gray-400 flex-shrink-0 w-6 text-right font-semibold">${idx + 1}.</span>
                                <span class="text-gray-800">"${escapeHtml(String(token).trim())}"</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                <div class="bg-gray-100 px-6 py-3 flex justify-end gap-3 border-t border-gray-200">
                    <button onclick="document.getElementById('${modalId}').remove()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        Close
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Inject modal into DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Close modal on background click
    document.getElementById(modalId).addEventListener('click', function(e) {
        if (e.target === this) {
            this.remove();
        }
    });
}

function formatStemming(tokenStr) {
    // Format stemming as one sentence (space-separated)
    if (!tokenStr) return '';
    
    try {
        // Try to parse as JSON array
        let tokens = [];
        if (typeof tokenStr === 'string') {
            // If it looks like JSON
            if (tokenStr.startsWith('[')) {
                tokens = JSON.parse(tokenStr);
            } else {
                // Otherwise split by spaces
                tokens = tokenStr.split(/\s+/).filter(t => t.length > 0);
            }
        } else if (Array.isArray(tokenStr)) {
            tokens = tokenStr;
        }
        
        // Return as space-separated sentence
        return tokens
            .map(token => escapeHtml(String(token).trim()))
            .filter(t => t.length > 0)
            .join(' ');
    } catch (e) {
        // Fallback: just escape and return as-is
        return escapeHtml(String(tokenStr));
    }
}

function updatePaginationInfo(pagination) {
    document.getElementById('entriesInfo').innerHTML =
        `Showing <span class="font-medium">${pagination.from || 0}</span> to <span class="font-medium">${pagination.to || 0}</span> of <span class="font-medium">${pagination.total}</span> entries`;
}

function updatePaginationControls(pagination) {
    const paginationControls = document.getElementById('paginationControls');

    let paginationHTML = `
        <button class="px-4 py-2 text-sm font-semibold border border-gray-300 rounded-md hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed text-gray-700"
                id="prevBtn" ${pagination.current_page === 1 ? 'disabled' : ''}>
            ← Previous
        </button>
    `;

    // Show page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, pagination.current_page - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(pagination.last_page, startPage + maxVisiblePages - 1);

    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    if (startPage > 1) {
        paginationHTML += `<button class="px-3 py-2 text-sm font-semibold border border-gray-300 rounded-md hover:bg-gray-100 transition text-gray-700" onclick="goToPage(1)">1</button>`;
        if (startPage > 2) {
            paginationHTML += `<span class="px-2 py-2 text-sm text-gray-400 font-semibold">...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === pagination.current_page;
        paginationHTML += `<button class="px-3 py-2 text-sm font-semibold rounded-md transition ${isActive ? 'bg-blue-600 text-white shadow-md' : 'border border-gray-300 text-gray-700 hover:bg-gray-100'}" onclick="goToPage(${i})">${i}</button>`;
    }

    if (endPage < pagination.last_page) {
        if (endPage < pagination.last_page - 1) {
            paginationHTML += `<span class="px-2 py-2 text-sm text-gray-400 font-semibold">...</span>`;
        }
        paginationHTML += `<button class="px-3 py-2 text-sm font-semibold border border-gray-300 rounded-md hover:bg-gray-100 transition text-gray-700" onclick="goToPage(${pagination.last_page})">${pagination.last_page}</button>`;
    }

    paginationHTML += `
        <button class="px-4 py-2 text-sm font-semibold border border-gray-300 rounded-md hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed text-gray-700"
                id="nextBtn" ${pagination.current_page >= pagination.last_page ? 'disabled' : ''} onclick="nextPage()">
            Next →
        </button>
    `;

    paginationControls.innerHTML = paginationHTML;

    // Add event listener to prev button
    const prevBtn = document.getElementById('prevBtn');
    if (prevBtn) {
        prevBtn.addEventListener('click', prevPage);
    }
}

function goToPage(page) {
    currentPage = page;
    loadPreprocessedReviews();
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        loadPreprocessedReviews();
    }
}

function nextPage() {
    currentPage++;
    loadPreprocessedReviews();
}

function handleSearch() {
    currentPage = 1;
    loadPreprocessedReviews();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Preprocess button
    document.getElementById('preprocessBtn').addEventListener('click', preprocessData);

    // Entries per page change
    document.getElementById('entriesPerPage').addEventListener('change', function() {
        entriesPerPage = parseInt(this.value);
        currentPage = 1;
        loadPreprocessedReviews();
    });

    // Search functionality
    document.getElementById('tableSearch').addEventListener('input', handleSearch);

    // Load initial data
    loadPreprocessedReviews();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laragon\www\analisis_sentimen_TA\resources\views/preprocessing.blade.php ENDPATH**/ ?>