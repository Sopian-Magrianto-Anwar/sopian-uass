<?php
require 'auth_check.php';
$title = "Data Barang";

// Handle Actions
if (isset($_POST['submit'])) {
    $kode = htmlspecialchars($_POST['kode_barang']);
    $nama = htmlspecialchars($_POST['nama_barang']);
    $kategori = $_POST['kategori_id'];
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];
    $id = $_POST['id'] ?? '';
    
    // Handle Image Upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
        $destination = 'assets/products/' . $filename;
        if(move_uploaded_file($_FILES['gambar']['tmp_name'], $destination)) {
            $gambar = $filename;
        }
    }

    if ($id) {
        $updateQuery = "UPDATE barang SET kode_barang='$kode', nama_barang='$nama', kategori_id='$kategori', stok='$stok', harga='$harga'";
        if ($gambar) {
            $updateQuery .= ", gambar='$gambar'";
        }
        $updateQuery .= " WHERE id=$id";
        mysqli_query($conn, $updateQuery);
    } else {
        $query = "INSERT INTO barang (kode_barang, nama_barang, kategori_id, stok, harga, gambar) VALUES ('$kode', '$nama', '$kategori', '$stok', '$harga', '$gambar')";
        mysqli_query($conn, $query);
    }
    
    header("Location: barang.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM barang WHERE id=$id");
    header("Location: barang.php");
    exit;
}

$barang = query("SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.kategori_id = k.id ORDER BY b.id DESC");
$kategoris = query("SELECT * FROM kategori");

require 'includes/header.php';
?>

<div class="flex justify-between items-center bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Daftar Barang</h2>
    <?php if($_SESSION['role'] == 'admin'): ?>
    <button onclick="openModal()" class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
        <span class="material-symbols-outlined">add</span> Tambah Barang
    </button>
    <?php endif; ?>
</div>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
        <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase font-semibold text-slate-500">
            <tr>
                <th class="px-6 py-4">Kode</th>
                <th class="px-6 py-4">Nama Barang</th>
                <th class="px-6 py-4">Kategori</th>
                <th class="px-6 py-4">Stok</th>
                <th class="px-6 py-4">Harga</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach($barang as $b): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <td class="px-6 py-4 font-mono text-slate-500"><?= $b['kode_barang'] ?></td>
                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white flex items-center gap-3">
                    <?php if(!empty($b['gambar']) && file_exists('assets/products/'.$b['gambar'])): ?>
                    <img src="assets/products/<?= $b['gambar'] ?>" class="w-10 h-10 object-cover rounded-md border border-slate-100">
                    <?php else: ?>
                    <div class="w-10 h-10 bg-slate-100 rounded-md flex items-center justify-center text-slate-400">
                        <span class="material-symbols-outlined text-lg">image</span>
                    </div>
                    <?php endif; ?>
                    <?= $b['nama_barang'] ?>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2 py-1 rounded-full font-bold" style="<?= getCategoryStyle($b['kategori_id'] ?? $b['nama_kategori'] ?? '0') ?>">
                        <?= $b['nama_kategori'] ?? 'Uncategorized' ?>
                    </span>
                </td>
                <td class="px-6 py-4 <?= $b['stok'] < 5 ? 'text-red-500 font-bold' : '' ?>"><?= $b['stok'] ?></td>
                <td class="px-6 py-4">Rp<?= number_format($b['harga'], 0, ',', '.') ?></td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <button onclick='editModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)' class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition">
                        <span class="material-symbols-outlined">edit</span>
                    </button>
                    <button onclick="confirmDelete(<?= $b['id'] ?>)" class="text-red-600 hover:text-red-800 p-2 rounded hover:bg-red-50 transition">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                    <?php else: ?>
                    <span class="text-slate-400 text-xs italic">View Only</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Modal -->
<div id="modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-lg shadow-2xl overflow-y-auto max-h-[90vh]">
        <form method="post" enctype="multipart/form-data" class="p-6 flex flex-col gap-5">
            <div class="flex justify-between items-center">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Tambah Barang</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <input type="hidden" name="id" id="itemId">
            
            <div class="grid grid-cols-2 gap-4">
                <label class="flex flex-col gap-2">
                    <span class="font-medium text-slate-700 dark:text-slate-200">Kode Barang</span>
                    <input type="text" name="kode_barang" id="itemCode" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="font-medium text-slate-700 dark:text-slate-200">Kategori</span>
                    <select name="kategori_id" id="itemCat" class="form-select rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
                        <?php foreach($kategoris as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= $k['nama_kategori'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            
            <label class="flex flex-col gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-200">Nama Barang</span>
                <input type="text" name="nama_barang" id="itemName" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
            </label>
            
            <div class="grid grid-cols-2 gap-4">
                <label class="flex flex-col gap-2">
                    <span class="font-medium text-slate-700 dark:text-slate-200">Stok</span>
                    <input type="number" name="stok" id="itemStock" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="font-medium text-slate-700 dark:text-slate-200">Harga (Rp)</span>
                    <input type="number" name="harga" id="itemPrice" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
                </label>
            </div>

            <label class="flex flex-col gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-200">Foto Barang (Opsional)</span>
                <input type="file" name="gambar" class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                <p class="text-xs text-slate-400">Jpg, Png, Max 2MB.</p>
            </label>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-slate-700 hover:bg-slate-100 rounded-lg font-medium">Batal</button>
                <button type="submit" name="submit" class="bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-sm shadow-2xl p-6 text-center">
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-3xl">warning</span>
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Hapus Barang Ini?</h3>
        <p class="text-slate-500 mb-6">Tindakan ini tidak dapat dibatalkan. Data akan hilang permanen.</p>
        <div class="flex justify-center gap-3">
            <button onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</button>
            <a id="btnConfirmDelete" href="#" class="px-5 py-2.5 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 shadow-lg shadow-red-600/30">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
function openModal() {
    $('#modalTitle').text('Tambah Barang');
    $('#itemId').val('');
    $('#itemCode').val('');
    $('#itemName').val('');
    $('#itemStock').val('0');
    $('#itemPrice').val('0');
    $('#modal').removeClass('hidden').addClass('flex');
}

function editModal(data) {
    $('#modalTitle').text('Edit Barang');
    $('#itemId').val(data.id);
    $('#itemCode').val(data.kode_barang);
    $('#itemName').val(data.nama_barang);
    $('#itemCat').val(data.kategori_id);
    $('#itemStock').val(data.stok);
    $('#itemPrice').val(data.harga);
    $('#modal').removeClass('hidden').addClass('flex');
}

function closeModal() {
    $('#modal').addClass('hidden').removeClass('flex');
}

function confirmDelete(id) {
    $('#btnConfirmDelete').attr('href', 'barang.php?delete=' + id);
    $('#deleteModal').removeClass('hidden').addClass('flex');
}

function closeDeleteModal() {
    $('#deleteModal').addClass('hidden').removeClass('flex');
}
</script>

<?php require 'includes/footer.php'; ?>
