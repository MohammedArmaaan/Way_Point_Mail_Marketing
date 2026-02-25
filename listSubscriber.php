<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// 1. Error Reporting On (sirf debug ke liye, error dekhne ke liye)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$list_id = (int)$_GET['list_id'];
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; 
$offset = ($page - 1) * $limit;

// Fetch List Info
$stmt = $pdo->prepare("SELECT * FROM subscriber_lists WHERE list_id = ?");
$stmt->execute([$list_id]);
$list = $stmt->fetch();

// 2. Count Total Records (For Pagination)
$countQuery = "SELECT COUNT(*) FROM subscribers WHERE list_id = ?";
$countParams = [$list_id];
if($search != '') {
    $countQuery .= " AND (subscriber_name LIKE ? OR subscriber_email LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}
$stmt = $pdo->prepare($countQuery);
$stmt->execute($countParams);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// 3. Fetch Subscribers (Correct Order)
$query = "SELECT * FROM subscribers WHERE list_id = ?";
$params = [$list_id];
if($search != '') {
    $query .= " AND (subscriber_name LIKE ? OR subscriber_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$query .= " ORDER BY subscriber_id DESC LIMIT $limit OFFSET $offset"; // Direct variable injection for limit/offset to avoid PDO string issues

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$subscribers = $stmt->fetchAll();
?>

<style>
    /* Main Layout Fix */
    .page-wrapper {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    main {
        flex: 1 0 auto;
        padding-bottom: 2rem;
    }
    
    /* Table Container with Limited Height */
    .table-container {
        background: white;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    }
    
    .dark .table-container {
        background: #1e293b;
        border-color: #334155;
    }
    
    /* Scrollable Table Body */
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
    }
    
    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
    
    /* Sticky Header */
    .table-responsive thead th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        z-index: 10;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .dark .table-responsive thead th {
        background: #1e293b;
        border-bottom-color: #334155;
    }
    
    /* Pagination Styles */
    .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    
    .dark .pagination {
        background: #1e293b;
        border-top-color: #334155;
    }
    
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #334155;
        background: white;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .dark .pagination-btn {
        background: #0f172a;
        border-color: #334155;
        color: #cbd5e1;
    }
    
    .pagination-btn:hover:not(:disabled) {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
    
    .pagination-btn.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
    
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .pagination-info {
        color: #64748b;
        font-size: 0.875rem;
        margin-left: 1rem;
    }
    
    .dark .pagination-info {
        color: #94a3b8;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #64748b;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .table-responsive {
            max-height: 300px;
        }
        
        .pagination {
            flex-wrap: wrap;
        }
        
        .pagination-info {
            width: 100%;
            text-align: center;
            margin: 0.5rem 0 0;
        }
    }
</style>

<div class="page-wrapper">
    <main class="relative z-10 flex-1 p-6 bg-slate-50 dark:bg-slate-950">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
                        <?php echo htmlspecialchars($list['list_name']); ?>
                    </h1>
                    <p class="text-sm text-slate-500">
                        Total <?php echo $totalRecords; ?> subscribers • Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    </p>
                </div>
                <a href="action/listSubscriber.php?export_list=<?php echo $list_id; ?>" 
                   class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-sm hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">
                    Export CSV
                </a>
            </div>

            <!-- Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Sidebar - Import Forms -->
                <div class="space-y-6">
                    <!-- CSV Import Card -->
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-bold uppercase text-slate-500">Import CSV</h3>
                            <button onclick="downloadSample()" class="text-xs text-indigo-500 hover:underline font-bold">
                                Sample CSV
                            </button>
                        </div>
                        <form method="POST" action="action/listSubscriber.php" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                            <input type="file" name="csv_file" accept=".csv" required 
                                   class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:bg-indigo-50 file:text-indigo-700">
                            <button name="import_csv" 
                                    class="w-full py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">
                                Upload & Import
                            </button>
                        </form>
                    </div>

                    <!-- Bulk Add Card -->
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl">
                        <h3 class="text-sm font-bold uppercase text-slate-500 mb-4">Bulk Add (Name, Email)</h3>
                        <form method="POST" action="action/listSubscriber.php" class="space-y-4">
                            <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                            <textarea name="bulk_data" rows="5" 
                                      placeholder="John Doe, john@example.com&#10;Jane Smith, jane@example.com" 
                                      class="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono dark:text-white"></textarea>
                            <button name="import_bulk" 
                                    class="w-full py-2.5 bg-purple-600 text-white rounded-xl font-bold text-sm hover:bg-purple-700 transition-all">
                                Import Bulk
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Side - Subscribers Table -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Search Form -->
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                        <div class="relative flex-1">
                            <input type="text" name="search" placeholder="Search subscribers..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   class="w-full px-4 py-2.5 pl-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 dark:text-white">
                            <svg class="w-4 h-4 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button class="px-6 py-2.5 bg-slate-900 dark:bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-slate-800 dark:hover:bg-indigo-700 transition-all">
                            Search
                        </button>
                        <?php if($search != ''): ?>
                            <a href="?list_id=<?php echo $list_id; ?>" 
                               class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 transition-all">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>

                    <!-- Subscribers Table with Pagination -->
                    <form method="POST" action="action/listSubscriber.php" id="subscriberForm">
                        <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                        
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-4 w-10">
                                                <input type="checkbox" id="selectAll" class="rounded border-slate-300">
                                            </th>
                                            <th class="px-6 py-4">Subscriber Details</th>
                                            <th class="px-6 py-4 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <?php if(count($subscribers) > 0): ?>
                                            <?php foreach($subscribers as $index => $sub): ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                                <td class="px-6 py-4">
                                                    <input type="checkbox" name="bulk_ids[]" value="<?php echo $sub['subscriber_id']; ?>" 
                                                           class="sub-checkbox rounded border-slate-300">
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="font-bold text-slate-900 dark:text-white">
                                                        <?php echo htmlspecialchars($sub['subscriber_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo htmlspecialchars($sub['subscriber_email']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <a href="action/listSubscriber.php?delete=<?php echo $sub['subscriber_id']; ?>&list_id=<?php echo $list_id; ?>" 
                                                       onclick="return confirm('Are you sure you want to delete this subscriber?')" 
                                                       class="text-rose-500 font-bold hover:underline">
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="empty-state">
                                                    <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                    </svg>
                                                    <p class="text-lg font-medium">No subscribers found</p>
                                                    <p class="text-sm">Import subscribers using CSV or bulk add form</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if($totalPages > 1): ?>
                            <div class="pagination">
                                <button type="button" class="pagination-btn" onclick="goToPage(1)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                                    ⟪
                                </button>
                                <button type="button" class="pagination-btn" onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                                    ⟨
                                </button>
                                
                                <div class="flex items-center gap-1">
                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    
                                    for($i = $start; $i <= $end; $i++):
                                    ?>
                                        <button type="button" class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>" 
                                                onclick="goToPage(<?php echo $i; ?>)">
                                            <?php echo $i; ?>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                                
                                <button type="button" class="pagination-btn" onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>
                                    ⟩
                                </button>
                                <button type="button" class="pagination-btn" onclick="goToPage(<?php echo $totalPages; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>
                                    ⟫
                                </button>
                                
                                <span class="pagination-info">
                                    Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $totalRecords); ?> of <?php echo $totalRecords; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Bulk Delete Button -->
                        <?php if(count($subscribers) > 0): ?>
                        <div class="mt-4 flex items-center justify-between">
                            <button name="bulk_delete" onclick="return confirm('Are you sure you want to delete selected subscribers?')" 
                                    class="px-4 py-2 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-xs font-bold hover:bg-rose-600 hover:text-white transition-all">
                                Delete Selected (<span id="selectedCount">0</span>)
                            </button>
                            
                            <span class="text-xs text-slate-500">
                                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</div>

<script>
// Select All & Count Functionality
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.sub-checkbox');
const countDisplay = document.getElementById('selectedCount');

function updateCount() {
    if(countDisplay) {
        const checked = document.querySelectorAll('.sub-checkbox:checked').length;
        countDisplay.textContent = checked;
        
        if(selectAll) {
            selectAll.checked = checkboxes.length > 0 && checked === checkboxes.length;
        }
    }
}

if(selectAll) {
    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateCount();
    });
}

checkboxes.forEach(cb => {
    cb.addEventListener('change', updateCount);
});

// Pagination Function
function goToPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
}

// Download Sample CSV
function downloadSample() {
    const headers = ['Name', 'Email'];
    const sampleData = [
        ['John Doe', 'john@example.com'],
        ['Jane Smith', 'jane@example.com'],
        ['Bob Johnson', 'bob@example.com']
    ];
    
    let csvContent = headers.join(',') + '\n';
    sampleData.forEach(row => {
        csvContent += row.join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'sample.csv';
    link.click();
    window.URL.revokeObjectURL(url);
}

// Initialize count on page load
document.addEventListener('DOMContentLoaded', updateCount);
</script>