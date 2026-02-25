<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// Fetch templates from local DB
$templates = $pdo->query("SELECT * FROM email_templates ORDER BY created_at DESC")->fetchAll();
?>

<main class="min-h-screen p-6 bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Email Templates</h1>
                <p class="text-sm text-slate-500">Manage your Waypoint templates and local sync.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="document.getElementById('bulkModal').classList.remove('hidden')" 
                        class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 shadow-sm">
                    Bulk Upload (ZIP/CSV)
                </button>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200">
                    + Add Single Template
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase text-slate-500 border-b">
                    <tr>
                        <th class="px-6 py-4">Template Name</th>
                        <th class="px-6 py-4">Waypoint ID</th>
                        <th class="px-6 py-4">Created Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (!empty($templates)): foreach($templates as $tmpl): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($tmpl['template_name']); ?></td>
                        <td class="px-6 py-4"><code class="text-xs text-indigo-500 bg-indigo-50 px-2 py-1 rounded"><?php echo $tmpl['waypoint_template_id']; ?></code></td>
                        <td class="px-6 py-4 text-sm text-slate-500"><?php echo date('M d, Y', strtotime($tmpl['created_at'])); ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="action/template_actions.php?delete_id=<?php echo $tmpl['template_id_pk']; ?>&w_id=<?php echo $tmpl['waypoint_template_id']; ?>" 
                                   onclick="return confirm('Delete from Local DB and Waypoint?')" 
                                   class="text-rose-500 hover:text-rose-700 text-xs font-bold">Delete Everywhere</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400">No templates found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="bulkModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-xl p-8 rounded-3xl shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold dark:text-white">Bulk Import Templates</h3>
            <button onclick="document.getElementById('bulkModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        
        <form action="action/template_actions.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="p-6 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl bg-slate-50 dark:bg-slate-800/50">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Upload Template CSV</label>
                <input type="file" name="template_csv" accept=".csv" required 
                       class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                
                <div class="mt-4 flex flex-col gap-1">
                    <p class="text-[10px] text-slate-500 font-medium italic">Format: Template Name, Waypoint Template ID</p>
                    <a href="action/download_sample.php" class="text-indigo-600 hover:underline text-xs font-bold">
                        📥 Download waypoint_template_sample.csv
                    </a>
                </div>
            </div>

            <button type="submit" name="import_csv" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 shadow-xl transition-all">
                🚀 Import to Database
            </button>
        </form>
    </div>
</div>

    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md p-8 rounded-3xl shadow-2xl">
            <h3 class="text-xl font-bold mb-4">Add Template</h3>
            <form action="action/template_actions.php" method="POST" class="space-y-4">
                <input type="text" name="template_name" placeholder="Friendly Name" required class="w-full p-3 border rounded-xl">
                <input type="text" name="waypoint_template_id" placeholder="Waypoint Template ID (if already exists)" class="w-full p-3 border rounded-xl">
                <button type="submit" name="add_single" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-bold">Save Template</button>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="w-full text-slate-500 text-sm">Cancel</button>
            </form>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>