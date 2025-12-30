<?php
require 'auth_check.php';
$title = "Data Transaksi";

if (isset($_GET['delete'])) {
    if ($_SESSION['role'] == 'admin') {
        $id = $_GET['delete'];
        mysqli_query($conn, "DELETE FROM penjualan WHERE id=$id");
        header("Location: transaksi.php");
        exit;
    } else {
        echo "<script>alert('Akses Ditolak');</script>";
    }
}

$transaksi = query("SELECT p.*, u.nama_lengkap FROM penjualan p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");

// Fetch details for each transaction for the modal
foreach ($transaksi as &$t) {
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

<div class="flex justify-between items-center bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Riwayat Transaksi</h2>
    <a href="transaksi_tambah.php" class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
        <span class="material-symbols-outlined">add_shopping_cart</span> Transaksi Baru
    </a>
</div>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
        <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase font-semibold text-slate-500">
            <tr>
                <th class="px-6 py-4">No Transaksi</th>
                <th class="px-6 py-4">Tanggal</th>
                <th class="px-6 py-4">Kasir/User</th>
                <th class="px-6 py-4">Total Bayar</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach($transaksi as $t): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <td class="px-6 py-4 font-mono text-slate-900 dark:text-white"><?= $t['no_transaksi'] ?></td>
                <td class="px-6 py-4"><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td>
                <td class="px-6 py-4"><?= $t['nama_lengkap'] ?></td>
                <td class="px-6 py-4 font-bold text-primary text-right">Rp <?= number_format($t['total_bayar'], 0, ',', '.') ?></td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <button onclick='showDetail(<?= htmlspecialchars($t['items_json'] ?? '[]', ENT_QUOTES) ?>)' class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition" title="Lihat Detail">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <a href="cetak_struk.php?id=<?= $t['id'] ?>" target="_blank" class="text-green-600 hover:text-green-800 p-2 rounded hover:bg-green-50 transition" title="Cetak Struk">
                       <span class="material-symbols-outlined">print</span>
                    </a>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <button onclick="confirmDelete(<?= $t['id'] ?>)" class="text-red-600 hover:text-red-800 p-2 rounded hover:bg-red-50 transition" title="Hapus Transaksi">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Detail Modal -->
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

<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center">
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-3xl">warning</span>
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Hapus Transaksi?</h3>
        <p class="text-slate-500 mb-6">Data transaksi akan dihapus permanen. Stok barang tidak akan dikembalikan.</p>
        <div class="flex justify-center gap-3">
            <button onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</button>
            <a id="btnConfirmDelete" href="#" class="px-5 py-2.5 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 shadow-lg shadow-red-600/30">Ya, Hapus</a>
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

function confirmDelete(id) {
    document.getElementById('btnConfirmDelete').href = 'transaksi.php?delete=' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
}
</script>

<?php require 'includes/footer.php'; ?>
