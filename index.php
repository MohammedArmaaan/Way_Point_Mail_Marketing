	<?php
session_start();
require_once 'includes/config.php';

// Login Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1. Fetch Dynamic Counts
try {
    // Total Subscribers
    $subCount = $pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
    
    // Total Lists
    $listCount = $pdo->query("SELECT COUNT(*) FROM subscriber_lists")->fetchColumn();
    
    // Total Campaigns
    $campCount = $pdo->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();
    
    // Total Successful Emails Sent
    $totalSent = $pdo->query("SELECT SUM(success_count) FROM campaigns")->fetchColumn() ?: 0;

    // 2. Fetch Recent Campaigns for Table
    $recentCamps = $pdo->query("
        SELECT c.*, l.list_name 
        FROM campaigns c 
        JOIN subscriber_lists l ON c.list_id = l.list_id 
        ORDER BY c.created_at DESC LIMIT 5
    ")->fetchAll();

    // 3. Data for Graph (Last 7 Days Sends)
    // Note: Iske liye hum date-wise grouping query use kar rahe hain
    $graphData = $pdo->query("
        SELECT DATE(created_at) as date, SUM(success_count) as total 
        FROM campaigns 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ")->fetchAll();

    $labels = [];
    $dataPoints = [];
    foreach($graphData as $row) {
        $labels[] = date('D', strtotime($row['date']));
        $dataPoints[] = $row['total'];
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
}

include 'includes/header.php';
?>
<style>
	html, body {
    height: 100%;
    margin: 0;
    overflow-x: hidden; /* Prevent horizontal scroll during animations */
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100%;
}

main {
    flex: 1 0 auto; /* This forces the footer to the bottom */
    padding-bottom: 4rem !important; /* Creates a safe zone for the footer */
}

footer {
    flex-shrink: 0;
    width: 100%;
    background-color: white; /* Or match your theme */
    border-top: 1px solid rgba(0,0,0,0.05);
    padding-top: 1000px !importatn; /* Pushes it away from the cards */
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main class="relative z-10 flex-1 p-6 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8 animate-slide-up">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">System Overview</h1>
            <p class="text-slate-500">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>. Here's what's happening today.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-effect bg-white/70 dark:bg-slate-900/70 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 card-hover animate-slide-up stagger-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <span class="text-xs font-bold text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded-lg">+12%</span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Subscribers</h3>
                <p class="text-2xl font-bold dark:text-white"><?php echo number_format($subCount); ?></p>
            </div>

            <div class="glass-effect bg-white/70 dark:bg-slate-900/70 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 card-hover animate-slide-up stagger-2">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    </div>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Segments/Lists</h3>
                <p class="text-2xl font-bold dark:text-white"><?php echo number_format($listCount); ?></p>
            </div>

            <div class="glass-effect bg-white/70 dark:bg-slate-900/70 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 card-hover animate-slide-up stagger-3">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-pink-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Campaigns</h3>
                <p class="text-2xl font-bold dark:text-white"><?php echo number_format($campCount); ?></p>
            </div>

            <div class="glass-effect bg-white/70 dark:bg-slate-900/70 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 card-hover animate-slide-up stagger-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Emails Delivered</h3>
                <p class="text-2xl font-bold dark:text-white"><?php echo number_format($totalSent); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 glass-effect bg-white/70 dark:bg-slate-900/70 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 animate-slide-up stagger-5">
    <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
        <h3 class="text-xl font-bold dark:text-white">Email Delivery Performance</h3>
        
        <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
            <input type="date" id="startDate" class="bg-transparent text-xs font-bold px-2 py-1 outline-none border-none dark:text-white" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
            <span class="text-slate-400 text-xs">to</span>
            <input type="date" id="endDate" class="bg-transparent text-xs font-bold px-2 py-1 outline-none border-none dark:text-white" value="<?php echo date('Y-m-d'); ?>">
            <button onclick="updateChart()" class="bg-indigo-600 text-white p-1.5 rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
        </div>
    </div>
    
    <div class="h-[350px] w-full">
        <canvas id="performanceChart"></canvas>
    </div>
</div>

            <div class="glass-effect bg-white/70 dark:bg-slate-900/70 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 animate-slide-up stagger-5">
                <h3 class="text-xl font-bold dark:text-white mb-6">Latest Campaigns</h3>
                <div class="space-y-6">
                    <?php if(!empty($recentCamps)): foreach($recentCamps as $rc): ?>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-indigo-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate dark:text-white"><?php echo htmlspecialchars($rc['campaign_title']); ?></p>
                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($rc['list_name']); ?></p>
                        </div>
						<div class="flex-1 min-w-0">
                            <p class="inline-block px-3 py-1 text-xs font-semibold rounded-full 
							   bg-indigo-100 text-indigo-700 
							   dark:bg-indigo-900 dark:text-indigo-300">
							   <?php echo htmlspecialchars($rc['status']); ?>
							</p>
							<hr class="border-t border-slate-200 dark:border-slate-700 my-4">
                        </div>
						
						
                        <div class="text-right">
                            <p class="text-sm font-bold text-emerald-500"><?php echo $rc['success_count']; ?></p>
                            <p class="text-[10px] uppercase text-slate-400">Success</p>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                        <p class="text-slate-500 text-sm">No recent campaigns.</p>
                    <?php endif; ?>
                </div>
                <a href="campaigns.php" class="block w-full text-center mt-8 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    View All Campaigns
                </a>
            </div>
        </div>
    </div>


<script>
    let myChart;

async function updateChart() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;

    const response = await fetch(`action/get_chart_data.php?start=${start}&end=${end}`);
    const rawData = await response.json();

    const labels = rawData.map(item => item.date);
    const points = rawData.map(item => item.total);

    const ctx = document.getElementById('performanceChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    if (myChart) {
        myChart.destroy();
    }

    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Successful Sends',
                data: points,
                borderColor: '#6366f1',
                borderWidth: 4,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 14 },
                    cornerRadius: 10,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(156, 163, 175, 0.05)', drawBorder: false },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                }
            }
        }
    });
}

// Initial Load
document.addEventListener('DOMContentLoaded', updateChart);
</script>
	<?php include 'includes/footer.php'; ?>
</main>

