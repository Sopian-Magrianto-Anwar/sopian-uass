<?php
require 'auth_check.php';
$title = "Dashboard";

// Fetch Stats
$total_barang = count(query("SELECT id FROM barang"));
$total_kategori = count(query("SELECT id FROM kategori"));
$total_transaksi = count(query("SELECT id FROM penjualan"));
$total_users = count(query("SELECT id FROM users"));

// Get Recent Transactions
$recent_transactions = query("SELECT p.*, u.nama_lengkap FROM penjualan p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");

foreach ($recent_transactions as &$t) {
    $tid = $t['id'];
    $items = query("SELECT pd.*, b.nama_barang 
                    FROM penjualan_detail pd 
                    JOIN barang b ON pd.barang_id = b.id 
                    WHERE pd.penjualan_id = $tid");
    $t['items_json'] = json_encode($items);
}
unset($t);

require 'includes/header.php';
?>

<!-- ... (Stats Grid removed for brevity in replacement, targeting specific block) ... -->
<!-- Wait, I need to be careful with targeting. I'll target the recent transactions query block first. -->

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-4">
             <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                <span class="material-symbols-outlined">inventory_2</span>
             </div>
             <div>
                <p class="text-slate-600 dark:text-slate-300 text-base font-medium">Total Barang</p>
                <p class="text-slate-900 dark:text-slate-50 tracking-tight text-3xl font-bold"><?= $total_barang ?></p>
             </div>
        </div>
    </div>
    <div class="flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-4">
             <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                <span class="material-symbols-outlined">receipt_long</span>
             </div>
             <div>
                <p class="text-slate-600 dark:text-slate-300 text-base font-medium">Total Transaksi</p>
                <p class="text-slate-900 dark:text-slate-50 tracking-tight text-3xl font-bold"><?= $total_transaksi ?></p>
             </div>
        </div>
    </div>
    <div class="flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-4">
             <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
                <span class="material-symbols-outlined">category</span>
             </div>
             <div>
                <p class="text-slate-600 dark:text-slate-300 text-base font-medium">Kategori</p>
                <p class="text-slate-900 dark:text-slate-50 tracking-tight text-3xl font-bold"><?= $total_kategori ?></p>
             </div>
        </div>
    </div>
    <div class="flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-4">
             <div class="p-3 bg-orange-100 text-orange-600 rounded-lg">
                <span class="material-symbols-outlined">group</span>
             </div>
             <div>
                <p class="text-slate-600 dark:text-slate-300 text-base font-medium">Users</p>
                <p class="text-slate-900 dark:text-slate-50 tracking-tight text-3xl font-bold"><?= $total_users ?></p>
             </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 mb-6">
    <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Traffic Penjualan (Harian)</h3>
    <div id="trafficChart"></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Transactions Table -->
    <div class="lg:col-span-2 flex flex-col gap-4 rounded-xl border border-slate-200 dark:border-slate-800 p-6 bg-white dark:bg-slate-900">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Transaksi Terakhir</h3>
            <a href="transaksi.php" class="text-primary text-sm font-medium hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3">No Transaksi</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php foreach($recent_transactions as $t): ?>
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white"><?= $t['no_transaksi'] ?></td>
                        <td class="px-4 py-3"><?= date('d M Y', strtotime($t['tanggal'])) ?></td>
                        <td class="px-4 py-3"><?= $t['nama_lengkap'] ?></td>
                        <td class="px-4 py-3 text-right font-bold text-primary">Rp <?= number_format($t['total_bayar'], 0, ',', '.') ?></td>
                        <td class="px-4 py-3 text-right">
                             <button onclick='showDetail(<?= htmlspecialchars($t['items_json'] ?? '[]', ENT_QUOTES) ?>)' class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition" title="Lihat Detail">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($recent_transactions)): ?>
                    <tr><td colspan="5" class="px-4 py-3 text-center">Belum ada transaksi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal (Dashboard) -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-lg shadow-2xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Detail Transaksi</h3>
                <button onclick="closeDetail()" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="overflow-y-auto max-h-[60vh]">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Barang</th>
                            <th class="px-4 py-2 text-right">Harga</th>
                            <th class="px-4 py-2 text-center">Qty</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detailItems" class="divide-y divide-slate-100 dark:divide-slate-700">
                        <!-- Items injected by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function showDetail(items) {
        const tbody = document.getElementById('detailItems');
        tbody.innerHTML = '';
        
        if(typeof items === 'string') items = JSON.parse(items);
        
        items.forEach(item => {
            const row = `
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">${item.nama_barang}</td>
                    <td class="px-4 py-3 text-right">Rp ${parseInt(item.harga_satuan).toLocaleString('id-ID')}</td>
                    <td class="px-4 py-3 text-center">${item.jumlah}</td>
                    <td class="px-4 py-3 text-right font-bold">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
        
        document.getElementById('detailModal').classList.remove('hidden');
        document.getElementById('detailModal').classList.add('flex');
    }

    function closeDetail() {
        document.getElementById('detailModal').classList.add('hidden');
        document.getElementById('detailModal').classList.remove('flex');
    }
    </script>

    <!-- Quick Actions -->
    <div class="flex flex-col gap-4 rounded-xl border border-slate-200 dark:border-slate-800 p-6 bg-white dark:bg-slate-900">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Quick Actions</h3>
        <div class="flex flex-col gap-3">
            <a href="transaksi_tambah.php" class="flex w-full items-center justify-center rounded-lg h-12 bg-primary text-white gap-2 font-bold hover:bg-blue-600 transition">
                <span class="material-symbols-outlined">add_shopping_cart</span> Tambah Transaksi
            </a>
            <?php if($_SESSION['role'] == 'admin'): ?>
            <a href="barang.php?action=add" class="flex w-full items-center justify-center rounded-lg h-12 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 gap-2 font-bold hover:bg-slate-200 transition">
                <span class="material-symbols-outlined">add_box</span> Tambah Barang
            </a>
            <?php endif; ?>
            <a href="laporan.php" class="flex w-full items-center justify-center rounded-lg h-12 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 gap-2 font-bold hover:bg-slate-200 transition">
                <span class="material-symbols-outlined">description</span> Lihat Laporan
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<?php
// Get daily traffic data (last 7 days)
$trafficQuery = "SELECT DATE(tanggal) as tgl, COUNT(*) as count, SUM(total_bayar) as total FROM penjualan WHERE tanggal >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY DATE(tanggal) ORDER BY DATE(tanggal)";
$trafficData = query($trafficQuery);

$dates = [];
$counts = [];
$totals = [];

// Initialize last 7 days with 0
for($i=6; $i>=0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $found = false;
    foreach($trafficData as $td) {
        if ($td['tgl'] == $d) {
            $dates[] = date('d M', strtotime($d));
            $counts[] = $td['count'];
            $totals[] = $td['total'];
            $found = true;
            break;
        }
    }
    if(!$found) {
        $dates[] = date('d M', strtotime($d));
        $counts[] = 0;
        $totals[] = 0;
    }
}
?>
<script>
    var options = {
        series: [{
            name: 'Total Pendapatan (Rp)',
            data: <?= json_encode($totals) ?>
        }, {
            name: 'Jumlah Transaksi',
            data: <?= json_encode($counts) ?>
        }],
        chart: {
            height: 350,
            type: 'area', // Tsunami/Wave effect
            toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: <?= json_encode($dates) ?>,
            labels: { style: { colors: '#64748b' } }
        },
        yaxis: [
            { labels: { style: { colors: '#64748b' }, formatter: (val) => { return val.toLocaleString() } } },
            { opposite: true, labels: { style: { colors: '#64748b' } } }
        ],
        colors: ['#137fec', '#22c55e'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.1, // Fade out like wave
                stops: [0, 90, 100]
            }
        },
        tooltip: { theme: 'light' },
        grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
    };

    var chart = new ApexCharts(document.querySelector("#trafficChart"), options);
    chart.render();
</script>

<?php require 'includes/footer.php'; ?>
