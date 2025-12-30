<?php
require 'auth_check.php';
$title = "Data Kategori";

// Handle Actions
if (isset($_POST['submit'])) {
    $nama = htmlspecialchars($_POST['nama_kategori']);
    $id = $_POST['id'] ?? '';

    if ($id) {
        $query = "UPDATE kategori SET nama_kategori='$nama' WHERE id=$id";
    } else {
        $query = "INSERT INTO kategori (nama_kategori) VALUES ('$nama')";
    }
    
    if (mysqli_query($conn, $query)) {
        header("Location: kategori.php");
        exit;
    } else {
        $error = mysqli_error($conn);
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM kategori WHERE id=$id");
    header("Location: kategori.php");
    exit;
}

$kategori = query("SELECT * FROM kategori ORDER BY id DESC");
require 'includes/header.php';
?>

<div class="flex justify-between items-center bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Daftar Kategori</h2>
    <?php if($_SESSION['role'] == 'admin'): ?>
    <button onclick="openModal()" class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
        <span class="material-symbols-outlined">add</span> Tambah Kategori
    </button>
    <?php endif; ?>
</div>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
        <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase font-semibold text-slate-500">
            <tr>
                <th class="px-6 py-4">ID</th>
                <th class="px-6 py-4">Nama Kategori</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach($kategori as $k): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <td class="px-6 py-4">#<?= $k['id'] ?></td>
                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white"><?= $k['nama_kategori'] ?></td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <button onclick='editModal(<?= $k['id'] ?>, <?= htmlspecialchars(json_encode($k['nama_kategori']), ENT_QUOTES) ?>)' class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition">
                        <span class="material-symbols-outlined">edit</span>
                    </button>
                    <button onclick="confirmDelete(<?= $k['id'] ?>)" class="text-red-600 hover:text-red-800 p-2 rounded hover:bg-red-50 transition">
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

<!-- Modal using core HTML/CSS/JS -->
<div id="modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md shadow-2xl transform transition-all">
        <form method="post" class="p-6 flex flex-col gap-6">
            <div class="flex justify-between items-center">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Tambah Kategori</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <input type="hidden" name="id" id="catId">
            
            <label class="flex flex-col gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-200">Nama Kategori</span>
                <input type="text" name="nama_kategori" id="catName" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-primary focus:border-primary">
            </label>
            
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-slate-700 hover:bg-slate-100 rounded-lg">Batal</button>
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
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Hapus Kategori Ini?</h3>
        <p class="text-slate-500 mb-6">Tindakan ini tidak dapat dibatalkan. Kategori yang dihapus akan hilang dari barang terkait.</p>
        <div class="flex justify-center gap-3">
            <button onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</button>
            <a id="btnConfirmDelete" href="#" class="px-5 py-2.5 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 shadow-lg shadow-red-600/30">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
function openModal() {
    $('#modalTitle').text('Tambah Kategori');
    $('#catId').val('');
    $('#catName').val('');
    $('#modal').removeClass('hidden').addClass('flex');
}

function editModal(id, name) {
    $('#modalTitle').text('Edit Kategori');
    $('#catId').val(id);
    $('#catName').val(name);
    $('#modal').removeClass('hidden').addClass('flex');
}

function closeModal() {
    $('#modal').addClass('hidden').removeClass('flex');
}

function confirmDelete(id) {
    $('#btnConfirmDelete').attr('href', 'kategori.php?delete=' + id);
    $('#deleteModal').removeClass('hidden').addClass('flex');
}

function closeDeleteModal() {
    $('#deleteModal').addClass('hidden').removeClass('flex');
}
</script>

<?php require 'includes/footer.php'; ?>
