<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Records per page
$offset = ($page - 1) * $limit;

try {
    // Get total records for pagination
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM campaigns");
    $totalRecords = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    // Fetch past campaigns from DB with pagination
    $stmt = $pdo->prepare("
        SELECT c.*, l.list_name 
        FROM campaigns c 
        JOIN subscriber_lists l ON c.list_id = l.list_id 
        ORDER BY c.created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $campaigns = $stmt->fetchAll();

    // Fetch groups for the modal dropdown
    $group_stmt = $pdo->query("SELECT list_id, list_name FROM subscriber_lists ORDER BY list_name ASC");
    $groups = $group_stmt->fetchAll();

    // NEW: Fetch templates for the dropdown
    $tmpl_stmt = $pdo->query("SELECT waypoint_template_id, template_name FROM email_templates ORDER BY template_name ASC");
    $db_templates = $tmpl_stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $campaigns = [];
    $totalRecords = 0;
    $totalPages = 0;
}

?>
<style>
    /* Ensure body and html take full height */
    html, body {
        height: 100%;
        margin: 0;
    }
    /* Wrapper to push footer down */
    .site-wrapper {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    main {
        flex: 1 0 auto; /* Yeh content ko space lene dega aur footer ko niche rakhega */
    }
    footer {
        flex-shrink: 0;
    }
</style>

<main class="min-h-screen p-6 bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Email Campaigns</h1>
                <p class="text-sm text-slate-500">Track and manage your Waypoint email sends.</p>
            </div>
            <button onclick="document.getElementById('campaignModal').classList.remove('hidden')" 
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200">
                + Create Campaign
            </button>
        </div>

        <!-- Results Summary -->
        <div class="mb-4 text-sm text-slate-500">
            Showing <?php echo count($campaigns); ?> of <?php echo $totalRecords; ?> campaigns
        </div>

        <!-- Campaigns Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase text-slate-500 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Campaign Title</th>
                        <th class="px-6 py-4">Target Group</th>
                        <th class="px-6 py-4">Recipients</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (!empty($campaigns)): ?>
                        <?php foreach($campaigns as $camp): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($camp['campaign_title']); ?></div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-wider"><?php echo $camp['template_id']; ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300"><?php echo htmlspecialchars($camp['list_name']); ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold"><?php echo $camp['success_count']; ?></span>
                                    <span class="text-slate-300">/</span>
                                    <span class="text-slate-500"><?php echo $camp['total_recipients']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
    <?php if($camp['status'] == 'sending'): ?>
        <span class="flex items-center justify-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-600 border border-amber-100 animate-pulse">
            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
            Sending...
        </span>
    <?php elseif($camp['status'] == 'scheduled'): ?>
        <div class="flex flex-col items-center">
            <span class="flex items-center justify-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-purple-50 text-purple-600 border border-purple-100">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Scheduled
            </span>
            <span class="text-[9px] text-purple-400 mt-1 font-medium">
                at <?php echo date('M d, H:i', strtotime($camp['scheduled_at'])); ?>
            </span>
        </div>
    <?php elseif($camp['status'] == 'completed'): ?>
        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-600 border border-emerald-100">
            Completed
        </span>
    <?php elseif($camp['status'] == 'delivered'): ?>
        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-600 border border-blue-100">
            Delivered
        </span>
    <?php else: ?>
        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-50 text-slate-400 border border-slate-200">
            <?php echo $camp['status']; ?>
        </span>
    <?php endif; ?>
</td>
                            <td class="px-6 py-4 text-right">
    <div class="flex flex-col items-end gap-1">
        <span class="text-[10px] text-slate-400"><?php echo date('M d, Y', strtotime($camp['created_at'])); ?></span>
        
        <div class="flex gap-3">
            <?php if($camp['status'] == 'scheduled'): ?>
    <a href="action/runCampaign.php?cancel_campaign_id=<?php echo $camp['campaign_id']; ?>" 
       onclick="return confirm('Stop this campaign from sending?')"
       class="text-amber-600 hover:text-amber-700 text-xs font-bold transition-colors bg-amber-50 px-2 py-0.5 rounded border border-amber-100">
        Cancel Send
    </a>
<?php endif; ?>

            <a href="action/runCampaign.php?delete_campaign_id=<?php echo $camp['campaign_id']; ?>" 
               onclick="return confirm('Are you sure you want to delete this campaign record?')"
               class="text-rose-500 hover:text-rose-700 text-xs font-bold transition-colors">
                Delete
            </a>
        </div>
    </div>
</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                No campaigns found. Click "Create Campaign" to get started.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="flex items-center space-x-2" aria-label="Pagination">
                <!-- Previous Page -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?>" 
                       class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-sm">
                        Previous
                    </a>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1) {
                    echo '<span class="px-4 py-2 text-slate-400">...</span>';
                }
                
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="px-4 py-2 border <?php echo $i == $page ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'; ?> rounded-xl transition-colors text-sm">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <span class="px-4 py-2 text-slate-400">...</span>
                    <a href="?page=<?php echo $totalPages; ?>" 
                       class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-sm">
                        <?php echo $totalPages; ?>
                    </a>
                <?php endif; ?>

                <!-- Next Page -->
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo ($page + 1); ?>" 
                       class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-sm">
                        Next
                    </a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>

        <!-- Page Info -->
        <?php if ($totalPages > 0): ?>
        <div class="mt-4 text-center text-xs text-slate-400">
            Page <?php echo $page; ?> of <?php echo $totalPages; ?>
        </div>
        <?php endif; ?>
    </div>


<!-- Auto-refresh for sending campaigns -->
<?php 
$isSending = false;
foreach($campaigns as $c) { 
    if($c['status'] == 'sending') { 
        $isSending = true; 
        break; 
    } 
}
if($isSending): 
?>
<script>
    setTimeout(function(){
        window.location.reload();
    }, 10000); // Reload every 10 seconds while sending
</script>
<?php endif; ?>

<!-- Campaign Creation Modal -->
<div id="campaignModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-xl p-8 rounded-3xl shadow-2xl animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold dark:text-white">New Campaign Configuration</h3>
            <button onclick="document.getElementById('campaignModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="action/runCampaign.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Campaign Title</label>
                <input type="text" name="campaign_title" placeholder="e.g. Welcome Series" required 
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Sender Email (Approved Only)</label>
                <select name="from_email" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="mail@zaveristudios.store">Zaveri Studios (mail@zaveristudios.store)</option>
                </select>
                <p class="text-[10px] text-slate-500 mt-1">Verified domain: zaveristudios.store</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Target Group</label>
                <select name="list_id" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php foreach($groups as $g): ?>
                        <option value="<?php echo $g['list_id']; ?>"><?php echo htmlspecialchars($g['list_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Select Template</label>
        <select name="template_id" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="" disabled selected>Choose a template...</option>
            <?php foreach($db_templates as $tmpl): ?>
                <option value="<?php echo htmlspecialchars($tmpl['waypoint_template_id']); ?>">
                    <?php echo htmlspecialchars($tmpl['template_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="text-[10px] text-slate-500 mt-1">Templates are synced from your local library.</p>
    </div>

            <div>
    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Schedule (Optional)</label>
    <input type="datetime-local" name="scheduled_at" 
           class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
    <p class="text-[10px] text-slate-500 mt-1">Leave blank to send immediately.</p>
</div>

<button type="submit" name="send_campaign" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg">
    🚀 Send / Schedule
</button>
        </form>
    </div>
</div>

<script>
// Close modal when clicking outside
document.getElementById('campaignModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

// Prevent modal from closing when clicking inside
document.querySelector('#campaignModal > div').addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>
</main>
<?php include 'includes/footer.php'; ?>