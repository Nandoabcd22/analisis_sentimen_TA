@extends('layouts.app')

@section('title', 'Preprocessing')

@section('content')
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
                <p class="mt-1 text-sm text-gray-500" id="processingStatus">Harap tunggu sebentar.</p>
                <div class="mt-4 bg-gray-200 rounded-full h-2 w-48 mx-auto overflow-hidden">
                    <div id="progressBar" class="bg-blue-600 h-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Table Section -->
            <div id="tableSection" class="hidden">
                <!-- Table Controls -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Show</label>
                        <select id="entriesPerPage" class="border border-gray-300 rounded px-3 py-1 text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Tampilkan Semua</option>
                        </select>
                        <label class="text-sm text-gray-600">entries</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Search:</label>
                        <input type="text" id="tableSearch" class="border border-gray-300 rounded px-3 py-1 text-sm" placeholder="Search reviews...">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse" id="preprocessTable">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300 whitespace-nowrap">No</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">Review</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300 whitespace-nowrap">Label</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">Case Folding</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">Cleansing</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">Normalisasi</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">Tokenizing</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">Stopword</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">Stemming</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Table content will be rendered by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div id="paginationSection" class="flex items-center justify-between mt-6">
                    <div class="text-sm text-gray-600" id="entriesInfo">
                        Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> entries
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
    
    try {
        console.log('Starting batch preprocessing of all data...');
        document.getElementById('processingStatus').textContent = 'Processing all data in batch mode...';
        
        const response = await fetch('/preprocess-data', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        console.log('Response status:', response.status);
        const result = await response.json();
        
        console.log('Preprocessing result:', result);

        if (response.ok && result.success) {
            alert(`✅ Preprocessing Complete!\n${result.message}`);
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
        row.className = 'border-b border-gray-200 hover:bg-gray-50';

        // Jika menampilkan semua, gunakan index + 1, jika tidak gunakan pagination offset
        const rowNumber = showAll ? (index + 1) : ((currentPage - 1) * entriesPerPage + index + 1);

        // Tentukan warna label berdasarkan nilai label
        let labelColor = 'bg-gray-50 text-gray-700';
        let labelDot = 'bg-gray-500';
        
        if (review.label === 'Positif') {
            labelColor = 'bg-green-50 text-green-700';
            labelDot = 'bg-green-500';
        } else if (review.label === 'Negatif') {
            labelColor = 'bg-red-50 text-red-700';
            labelDot = 'bg-red-500';
        } else if (review.label === 'Netral') {
            labelColor = 'bg-blue-50 text-blue-700';
            labelDot = 'bg-blue-500';
        }

        row.innerHTML = `
            <td class="py-3 px-4 text-center border border-gray-300 whitespace-nowrap">${rowNumber}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs">${escapeHtml(review.review || '')}</td>
            <td class="py-3 px-4 text-center border border-gray-300 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${labelColor}">
                    <span class="w-1.5 h-1.5 ${labelDot} rounded-full"></span> ${review.label || 'Belum Diproses'}
                </span>
            </td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs">${escapeHtml(review.case_folding || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs">${escapeHtml(review.cleansing || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs">${escapeHtml(review.normalisasi || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs">${escapeHtml(review.tokenizing || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs">${escapeHtml(review.stopword || '')}</td>
            <td class="py-3 px-4 border border-gray-300 text-xs max-w-xs">${escapeHtml(review.stemming || '')}</td>
        `;
        tbody.appendChild(row);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updatePaginationInfo(pagination) {
    document.getElementById('entriesInfo').innerHTML =
        `Showing <span class="font-medium">${pagination.from || 0}</span> to <span class="font-medium">${pagination.to || 0}</span> of <span class="font-medium">${pagination.total}</span> entries`;
}

function updatePaginationControls(pagination) {
    const paginationControls = document.getElementById('paginationControls');

    let paginationHTML = `
        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                id="prevBtn" ${pagination.current_page === 1 ? 'disabled' : ''}>
            Previous
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
        paginationHTML += `<button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition" onclick="goToPage(1)">1</button>`;
        if (startPage > 2) {
            paginationHTML += `<span class="px-2 text-sm text-gray-500">...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === pagination.current_page;
        paginationHTML += `<button class="px-3 py-1 text-sm ${isActive ? 'bg-blue-600 text-white' : 'border border-gray-300 hover:bg-gray-50'} rounded-md transition" onclick="goToPage(${i})">${i}</button>`;
    }

    if (endPage < pagination.last_page) {
        if (endPage < pagination.last_page - 1) {
            paginationHTML += `<span class="px-2 text-sm text-gray-500">...</span>`;
        }
        paginationHTML += `<button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition" onclick="goToPage(${pagination.last_page})">${pagination.last_page}</button>`;
    }

    paginationHTML += `
        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                id="nextBtn" ${pagination.current_page >= pagination.last_page ? 'disabled' : ''} onclick="nextPage()">
            Next
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
@endsection
