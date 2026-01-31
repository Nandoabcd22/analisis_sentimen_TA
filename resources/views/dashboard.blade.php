@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-10">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl shadow-2xl p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold tracking-tight mb-2">Dashboard</h1>
                        <p class="text-blue-100 text-lg">ANALISIS SENTIMEN REVIEW PENGUNJUNG PANTAI PASIR PUTIH KABUPATEN<br>SITUBONDO MENGGUNAKAN METODE SUPPORT VECTOR MACHINE (SVM)</p>
                    </div>
                    <div class="flex gap-3 items-center">
                        <form id="uploadForm" method="POST" action="/upload-file" enctype="multipart/form-data" class="flex gap-3 items-center">
                            @csrf
                            <input type="file" name="datafile" id="fileInput" class="hidden" onchange="updateFileName(this)">
                            <label for="fileInput" class="inline-flex items-center gap-2 rounded-md bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition cursor-pointer">
                                Choose File
                            </label>
                            <span id="fileName" class="text-gray-400 text-sm">No file chosen</span>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 transition">
                                Submit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-l-4 border-blue-600">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <span class="text-xs font-semibold uppercase text-blue-600">Total</span>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $total ?? 0 }}</div>
                <div class="text-xs text-gray-500">Review dianalisis</div>
            </div>

            <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-l-4 border-emerald-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                    </div>
                    <span class="text-xs font-semibold uppercase text-emerald-600">Positif</span>
                </div>
                <div class="text-3xl font-bold text-emerald-600 mb-1">{{ $positif ?? 0 }}</div>
                <div class="text-xs text-gray-500">Sentimen baik</div>
            </div>

            <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-l-4 border-orange-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs font-semibold uppercase text-orange-500">Negatif</span>
                </div>
                <div class="text-3xl font-bold text-orange-600 mb-1">{{ $negatif ?? 0 }}</div>
                <div class="text-xs text-gray-500">Sentimen buruk</div>
            </div>

            <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-l-4 border-gray-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center group-hover:bg-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs font-semibold uppercase text-gray-600">Netral</span>
                </div>
                <div class="text-3xl font-bold text-gray-700 mb-1">{{ $netral ?? 0 }}</div>
                <div class="text-xs text-gray-500">Sentimen netral</div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-xl p-8 mt-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Ulasan & Label Sentimen</h2>
            </div>
            
            <!-- Empty State -->
            <div id="emptyState" class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data</h3>
                <p class="mt-1 text-sm text-gray-500">Silakan upload file CSV terlebih dahulu untuk melihat data ulasan dan sentimen.</p>
            </div>
            
            <!-- Table Section (Hidden Initially) -->
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
                        </select>
                        <label class="text-sm text-gray-600">entries</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Search:</label>
                        <input type="text" id="tableSearch" class="border border-gray-300 rounded px-3 py-1 text-sm" placeholder="Search reviews...">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse" id="reviewsTable">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">No.</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 border border-gray-300">Ulasan</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700 border border-gray-300">Label</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Table content will be rendered by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="flex items-center justify-between mt-6">
                    <div class="text-sm text-gray-600" id="entriesInfo">
                        Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> entries
                    </div>
                    <div class="flex items-center gap-2" id="paginationControls">
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed" id="prevBtn" disabled>
                            Previous
                        </button>
                        <button class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md" data-page="1">1</button>
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition" data-page="2">2</button>
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition" data-page="3">3</button>
                        <span class="px-2 text-sm text-gray-500">...</span>
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition" data-page="10">10</button>
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition" id="nextBtn">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

<script>
let currentPage = 1;
let entriesPerPage = 10;
let allReviews = [];
let filteredReviews = [];

function updateFileName(input) {
    const fileName = input.files[0]?.name || 'No file chosen';
    document.getElementById('fileName').textContent = fileName;
}

function showTableSection() {
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('tableSection').classList.remove('hidden');
}

function updateStatistics(data) {
    // Update statistics cards
    document.querySelector('.border-l-4.border-blue-600 .text-3xl').textContent = data.total || 0;
    document.querySelector('.border-l-4.border-emerald-500 .text-3xl').textContent = data.positif || 0;
    document.querySelector('.border-l-4.border-orange-500 .text-3xl').textContent = data.negatif || 0;
    document.querySelector('.border-l-4.border-gray-500 .text-3xl').textContent = data.netral || 0;
}

// Load overall statistics from backend (covers all pages)
async function loadStatistics() {
    try {
        const resp = await fetch('/get-statistics', { headers: { 'Accept': 'application/json' } });
        const ct = resp.headers.get('content-type') || '';
        if (!ct.includes('application/json')) return;
        const json = await resp.json();
        if (json?.success && json.data) {
            updateStatistics(json.data);
        }
    } catch (e) {
        console.error('Error loading statistics:', e);
    }
}

async function loadReviews() {
    try {
        const searchValue = document.getElementById('tableSearch')?.value || '';
        const response = await fetch(`/get-reviews?page=${currentPage}&per_page=${entriesPerPage}&search=${encodeURIComponent(searchValue)}`);
        const result = await response.json();
        
        console.log('API Response:', result);
        
        if (result.success && result.data && result.pagination) {
            // Show table section if we have data
            if (Array.isArray(result.data) && result.data.length > 0) {
                console.log('Data is valid array with', result.data.length, 'items');
                showTableSection();
                renderTable(result.data);
                updatePaginationInfo(result.pagination);
                updatePaginationControls(result.pagination);
                
                // Refresh overall statistics from backend
                loadStatistics();
            } else {
                console.log('No data or not an array. Data type:', typeof result.data, 'Length:', result.data?.length);
                // Hide table if no data
                document.getElementById('emptyState').classList.remove('hidden');
                document.getElementById('tableSection').classList.add('hidden');
            }
        } else {
            console.log('Invalid response structure. Missing:', {
                success: !result.success,
                data: !result.data,
                pagination: !result.pagination
            });
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
    }
}

function renderTable(reviews) {
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';
    
    console.log('Rendering reviews:', reviews);
    
    reviews.forEach((review, index) => {
        const row = document.createElement('tr');
        row.className = 'border-b border-gray-200';
        
        // Debug log untuk melihat struktur data
        console.log('Review object:', review);
        
        const reviewText = review?.review || review?.text || review?.comment || 'No text';
        const reviewLabel = review?.label || review?.sentiment || 'Netral';
        
        const labelClass = reviewLabel === 'Positif' ? 'bg-green-50 text-green-700' : 
                           reviewLabel === 'Negatif' ? 'bg-red-50 text-red-700' : 
                           'bg-gray-50 text-gray-700';
        const dotClass = reviewLabel === 'Positif' ? 'bg-green-500' : 
                        reviewLabel === 'Negatif' ? 'bg-red-500' : 
                        'bg-gray-500';
        
        row.innerHTML = `
            <td class="py-3 px-4 text-center border border-gray-300">${(currentPage - 1) * entriesPerPage + index + 1}</td>
            <td class="py-3 px-4 border border-gray-300">${reviewText}</td>
            <td class="py-3 px-4 text-center border border-gray-300">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium ${labelClass}">
                    <span class="w-1.5 h-1.5 ${dotClass} rounded-full"></span> ${reviewLabel}
                </span>
            </td>
        `;
        tbody.appendChild(row);
    });
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
    
    // Add event listeners to prev button
    const prevBtn = document.getElementById('prevBtn');
    if (prevBtn) {
        prevBtn.addEventListener('click', prevPage);
    }
}

function goToPage(page) {
    currentPage = page;
    loadReviews();
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        loadReviews();
    }
}

function nextPage() {
    currentPage++;
    loadReviews();
}

function handleSearch() {
    currentPage = 1;
    loadReviews();
}

// File upload handler
async function handleFileUpload(event) {
    event.preventDefault();
    
    const fileInput = document.getElementById('fileInput');
    const submitBtn = event.target.querySelector('button[type="submit"]');
    
    if (!fileInput.files.length) {
        alert('Silakan pilih file terlebih dahulu');
        return;
    }
    
    const formData = new FormData();
    formData.append('datafile', fileInput.files[0]);
    
    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="inline-flex items-center gap-2">Uploading... <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>';
    
    try {
        const response = await fetch('/upload-file', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const contentType = response.headers.get('content-type') || '';
        let result;

        if (contentType.includes('application/json')) {
            result = await response.json();
        } else {
            const text = await response.text();
            throw new Error(text || 'Unexpected non-JSON response');
        }

        if (response.ok && result.success) {
            alert('File uploaded and processed successfully!');
            fileInput.value = '';
            document.getElementById('fileName').textContent = 'No file chosen';

            // Load reviews and update statistics
            currentPage = 1;
            await loadReviews();
        } else {
            const message = result?.message || 'Upload failed';
            alert('Error: ' + message);
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Upload error: ' + (error.message || 'Please try again.'));
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Submit';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Entries per page change
    document.getElementById('entriesPerPage').addEventListener('change', function() {
        entriesPerPage = parseInt(this.value);
        currentPage = 1;
        loadReviews();
    });
    
    // Search functionality
    document.getElementById('tableSearch').addEventListener('input', handleSearch);
    
    // File upload form
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', handleFileUpload);
    }
    
    // Initial load (will show empty state if no data)
    loadReviews();
});
</script>

