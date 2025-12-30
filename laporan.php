<?php
require 'auth_check.php';
$title = "Laporan Penjualan";

$start_date = $_GET['start'] ?? date('Y-m-01');
$end_date = $_GET['end'] ?? date('Y-m-d');

// Construct Query
$query = "SELECT p.*, u.nama_lengkap 
          FROM penjualan p 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE DATE(p.tanggal) BETWEEN '$start_date' AND '$end_date'
          ORDER BY p.tanggal DESC";

$data = query($query);
$total_revenue = 0;
foreach($data as $d) $total_revenue += $d['total_bayar'];

require 'includes/header.php';
?>

<div class="print:hidden flex flex-col sm:flex-row justify-between items-end sm:items-center bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm gap-4">
    <div class="flex-1 w-full text-center sm:text-left">
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-800 dark:text-white mb-2 text-center">Laporan Penjualan</h2>
        <p class="text-sm text-slate-500 text-center">Periode: <?= date('d M Y', strtotime($start_date)) ?> s/d <?= date('d M Y', strtotime($end_date)) ?></p>
    </div>
    
    <div class="flex flex-col sm:flex-row gap-4 items-end w-full sm:w-auto">
        <form method="get" class="flex gap-2 items-end">
            <label class="flex flex-col text-sm text-slate-600 font-medium">
                Mulai
                <input type="date" name="start" value="<?= $start_date ?>" class="form-input rounded-lg border-slate-300 h-10 p-2 text-sm shadow-sm focus:border-primary focus:ring-primary">
            </label>
            <label class="flex flex-col text-sm text-slate-600 font-medium">
                Sampai
                <input type="date" name="end" value="<?= $end_date ?>" class="form-input rounded-lg border-slate-300 h-10 p-2 text-sm shadow-sm focus:border-primary focus:ring-primary">
            </label>
            <button type="submit" class="bg-primary text-white h-10 px-4 rounded-lg font-bold hover:bg-blue-600 shadow-sm transition-all flex items-center justify-center">Filter</button>
        </form>
        
        <button onclick="window.print()" class="bg-slate-800 text-white h-10 px-4 rounded-lg font-bold hover:bg-slate-700 flex items-center gap-2 shadow-sm transition-all">
            <span class="material-symbols-outlined text-lg">print</span> Print
        </button>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden print:border-0 print:shadow-none">
    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
        <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase font-semibold text-slate-500">
            <tr>
                <th class="px-6 py-4">No Transaksi</th>
                <th class="px-6 py-4">Tanggal</th>
                <th class="px-6 py-4">User Admin</th>
                <th class="px-6 py-4 text-right">Total Bayar</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach($data as $d): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <td class="px-6 py-4 font-mono"><?= $d['no_transaksi'] ?></td>
                <td class="px-6 py-4"><?= date('d/m/Y H:i', strtotime($d['tanggal'])) ?></td>
                <td class="px-6 py-4"><?= $d['nama_lengkap'] ?></td>
                <td class="px-6 py-4 text-right">Rp <?= number_format($d['total_bayar'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-white">
            <tr>
                <td colspan="3" class="px-6 py-4 text-right uppercase">Total Penjualan</td>
                <td class="px-6 py-4 text-right text-lg text-primary">Rp <?= number_format($total_revenue, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Print Styles -->
<style>
@media print {
    body * { visibility: hidden; }
    .print\:hidden { display: none !important; }
    aside { display: none !important; }
    header { display: none !important; }
    main { width: 100%; margin: 0; padding: 0; overflow: visible; }
    main * { visibility: visible; }
    main { position: absolute; left: 0; top: 0; }
}
</style>

<?php require 'includes/footer.php'; ?>
