<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// Pagination and search parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 10; // Records per page
$offset = ($page - 1) * $limit;

try {
    // Base query
    $baseQuery = "FROM subscriber_lists l WHERE 1=1";
    $countQuery = "SELECT COUNT(*) " . $baseQuery;
    $params = [];

    // Add search condition
    if (!empty($search)) {
        $baseQuery .= " AND l.list_name LIKE :search";
        $countQuery .= " AND l.list_name LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Get total records for pagination
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    // Get paginated data
    $query = "SELECT l.*, 
              (SELECT COUNT(*) FROM subscribers s WHERE s.list_id = l.list_id) as count_subscriber
              " . $baseQuery . " 
              ORDER BY l.list_id DESC 
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $lists = [];
    $totalRecords = 0;
    $totalPages = 0;
}
?>

<main class="relative z-0 flex-1 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Subscriber Lists</h1>
            <button onclick="openModal('addModal')" 
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition duration-200">
                + Create New List
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-xl shadow mb-6 p-4">
            <form method="GET" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search lists by name..." 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" 
                        class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                    Search
                </button>
                <?php if (!empty($search)): ?>
                    <a href="subscriberLists.php" 
                       class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Summary -->
        <div class="mb-4 text-gray-600">
            Showing <?php echo count($lists); ?> of <?php echo $totalRecords; ?> lists
        </div>

        <!-- Lists Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">List Name</th>
                        <th class="p-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribers</th>
                        <th class="p-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (!empty($lists)): ?>
                        <?php foreach ($lists as $row): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="p-4 text-sm text-gray-500">#<?php echo $row['list_id']; ?></td>
                                <td class="p-4">
                                    <a href="listSubscriber.php?list_id=<?php echo $row['list_id']; ?>" 
                                       class="text-indigo-600 font-semibold hover:text-indigo-800 transition duration-150">
                                        <?php echo htmlspecialchars($row['list_name']); ?>
                                    </a>
                                </td>
                                <td class="p-4 text-sm">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        <?php echo $row['count_subscriber']; ?> Contacts
                                    </span>
                                </td>
                                <td class="p-4 text-right space-x-3">
                                    <button onclick="openEditModal(<?php echo $row['list_id']; ?>, '<?php echo htmlspecialchars($row['list_name'], ENT_QUOTES); ?>')" 
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition duration-150">
                                        Edit
                                    </button>
                                    <a href="action/subscriberList.php?delete_id=<?php echo $row['list_id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this list? This action cannot be undone.')"
                                       class="text-red-600 hover:text-red-800 text-sm font-medium transition duration-150">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500">
                                <?php echo empty($search) ? 'No lists found. Click "Create New List" to get started.' : 'No lists match your search criteria.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-6 flex justify-center">
                <nav class="flex items-center space-x-2">
                    <!-- Previous Page -->
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                            Previous
                        </a>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 border <?php echo $i == $page ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition duration-150">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                            Next
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>


<!-- ADD MODAL -->
<div id="addModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/70">
    <div class="bg-white p-6 rounded-xl w-96 max-w-md">
        <h3 class="text-lg font-bold mb-4">Create New List</h3>
        <form action="action/subscriberList.php" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">List Name</label>
                <input type="text" 
                       name="list_name" 
                       required 
                       placeholder="Enter list name" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeModal('addModal')" 
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                    Cancel
                </button>
                <button type="submit" 
                        name="add_list" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-150">
                    Create List
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/70">
    <div class="bg-white p-6 rounded-xl w-96 max-w-md">
        <h3 class="text-lg font-bold mb-4">Update List</h3>
        <form action="action/subscriberList.php" method="POST">
            <input type="hidden" name="list_id" id="edit_list_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">List Name</label>
                <input type="text" 
                       name="list_name" 
                       id="edit_list_name" 
                       required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeModal('editModal')" 
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                    Cancel
                </button>
                <button type="submit" 
                        name="update_list" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-150">
                    Update List
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openEditModal(id, name) {
    document.getElementById('edit_list_id').value = id;
    document.getElementById('edit_list_name').value = name;
    openModal('editModal');
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('bg-black/70')) {
        event.target.classList.add('hidden');
    }
}

// Prevent modal from closing when clicking inside
document.querySelectorAll('#addModal > div, #editModal > div').forEach(modal => {
    modal.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>
</main>
<?php include 'includes/footer.php'; ?>