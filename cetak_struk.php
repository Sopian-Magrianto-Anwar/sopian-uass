<?php
require 'auth_check.php';

if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id = $_GET['id'];
$transaksi = query("SELECT p.*, u.nama_lengkap FROM penjualan p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = $id")[0] ?? null;

if (!$transaksi) {
    die("Transaksi tidak ditemukan.");
}

$items = query("SELECT pd.*, b.nama_barang, b.kode_barang FROM penjualan_detail pd LEFT JOIN barang b ON pd.barang_id = b.id WHERE pd.penjualan_id = $id");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #<?= $transaksi['no_transaksi'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white; }
            .no-print { display: none; }
        }
        body { font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body class="bg-gray-100 flex justify-center items-start min-h-screen pt-10">
    
    <div class="bg-white p-6 w-[350px] shadow-lg relative">
        <div class="text-center mb-6 border-b-2 border-dashed border-gray-300 pb-4">
            <h1 class="text-2xl font-bold uppercase tracking-widest">SOPIAN STORE</h1>
            <p class="text-xs text-gray-500">Jl. Teknologi No. 123, Jakarta</p>
            <p class="text-xs text-gray-500">Telp: 021-555-777</p>
        </div>
        
        <div class="mb-4 text-xs font-medium text-gray-700">
            <div class="flex justify-between">
                <span>No Ref:</span>
                <span><?= $transaksi['no_transaksi'] ?></span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span><?= date('d/m/Y H:i', strtotime($transaksi['tanggal'])) ?></span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span><?= $transaksi['nama_lengkap'] ?></span>
            </div>
        </div>
        
        <table class="w-full text-xs mb-4">
            <thead class="border-b border-gray-300">
                <tr class="text-left">
                    <th class="pb-1">Item</th>
                    <th class="text-right pb-1">Qty</th>
                    <th class="text-right pb-1">Total</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach($items as $item): ?>
                <tr>
                    <td class="pt-2" colspan="3"><?= $item['nama_barang'] ?></td>
                </tr>
                <tr>
                    <td class="pb-1 border-b border-dotted border-gray-200 pl-2">@<?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                    <td class="text-right pb-1 border-b border-dotted border-gray-200">x<?= $item['jumlah'] ?></td>
                    <td class="text-right pb-1 border-b border-dotted border-gray-200"><?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="flex justify-between font-bold text-sm mb-6 pt-2 border-t-2 border-gray-800">
            <span>TOTAL</span>
            <span>Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></span>
        </div>
        
        <div class="text-center text-xs text-gray-500">
            <p>Terima Kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar.</p>
        </div>

        <div class="no-print mt-6 flex gap-2 justify-center">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-blue-700">Cetak</button>
            <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-gray-600">Tutup</button>
        </div>
    </div>

    <script>
        // Auto print on load
        window.onload = function() {
            // setTimeout(() => window.print(), 500);
        }
    </script>
</body>
</html>
