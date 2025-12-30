<?php
require 'auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Hanya Admin yang boleh mengakses halaman ini.");
}

$title = "Data Users";

// Handle Actions
if (isset($_POST['submit'])) {
    $username = htmlspecialchars($_POST['username']);
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $role = $_POST['role'];
    $id = $_POST['id'] ?? '';
    // Password handling
    $password = $_POST['password'];

    // Handle Image Upload
    $avatar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . time() . '_' . rand(100, 999) . '.' . $ext;
        $destination = 'assets/avatars/' . $filename;
        if(move_uploaded_file($_FILES['gambar']['tmp_name'], $destination)) {
            $avatar = $filename;
        }
    }
    
    if ($id) {
        $updateQuery = "UPDATE users SET username='$username', nama_lengkap='$nama', role='$role'";
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $updateQuery .= ", password='$hash'";
        }
        if ($avatar) {
            $updateQuery .= ", avatar='$avatar'";
        }
        $updateQuery .= " WHERE id=$id";
        mysqli_query($conn, $updateQuery);
        
        // Update session if editing self
        if ($id == $_SESSION['user_id'] && $avatar) {
            $_SESSION['avatar'] = $avatar;
        }
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password, nama_lengkap, role, avatar) VALUES ('$username', '$hash', '$nama', '$role', '$avatar')";
        mysqli_query($conn, $query);
    }
    header("Location: users.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $_SESSION['user_id']) { // Prevent self-delete
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    }
    header("Location: users.php");
    exit;
}

$users = query("SELECT * FROM users ORDER BY id DESC");
require 'includes/header.php';
?>

<div class="flex justify-between items-center bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Management Users</h2>
    <button onclick="openModal()" class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
        <span class="material-symbols-outlined">person_add</span> Tambah User
    </button>
</div>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
        <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase font-semibold text-slate-500">
            <tr>
                <th class="px-6 py-4">User</th>
                <th class="px-6 py-4">Username</th>
                <th class="px-6 py-4">Role</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach($users as $u): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <td class="px-6 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-200 flex-shrink-0 border border-slate-200 dark:border-slate-700">
                        <?php if($u['avatar'] && file_exists('assets/avatars/'.$u['avatar'])): ?>
                        <img src="assets/avatars/<?= $u['avatar'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-slate-500 font-bold text-lg bg-slate-200">
                            <?= strtoupper(substr($u['username'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="font-medium text-slate-900 dark:text-white"><?= $u['nama_lengkap'] ?></span>
                </td>
                <td class="px-6 py-4 font-mono text-slate-600 dark:text-slate-400"><?= $u['username'] ?></td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-bold <?= $u['role'] == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= strtoupper($u['role']) ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-right flex justify-end gap-2">
                    <button onclick='editModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)' class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition">
                        <span class="material-symbols-outlined">edit</span>
                    </button>
                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                    <button onclick="confirmDelete(<?= $u['id'] ?>)" class="text-red-600 hover:text-red-800 p-2 rounded hover:bg-red-50 transition">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md shadow-2xl">
        <form method="post" enctype="multipart/form-data" class="p-6 flex flex-col gap-5">
            <div class="flex justify-between items-center">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Tambah User</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <input type="hidden" name="id" id="userId">
            
            <label class="flex flex-col gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-200">Username</span>
                <input type="text" name="username" id="userName" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
            </label>
            
            <label class="flex flex-col gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-200">Nama Lengkap</span>
                <input type="text" name="nama_lengkap" id="userFullname" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
            </label>
            
            <label class="flex flex-col gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-200">Role</span>
                <select name="role" id="userRole" class="form-select rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </label>

            <label class="flex flex-col gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-200">Password <small id="passHint" class="text-slate-400 font-normal hidden">(Kosongkan jika tidak ingin mengubah)</small></span>
                <input type="password" name="password" id="userPass" class="form-input rounded-lg border-slate-300 dark:border-slate-700 focus:ring-primary focus:border-primary">
            </label>

            <label class="flex flex-col gap-2">
                <span class="font-medium text-slate-700 dark:text-slate-200">Foto Profil (Opsional)</span>
                <input type="file" name="gambar" class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
            </label>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
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
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Hapus User Ini?</h3>
        <p class="text-slate-500 mb-6">Tindakan ini tidak dapat dibatalkan.</p>
        <div class="flex justify-center gap-3">
            <button onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</button>
            <a id="btnConfirmDelete" href="#" class="px-5 py-2.5 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 shadow-lg shadow-red-600/30">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
function openModal() {
    $('#modalTitle').text('Tambah User');
    $('#userId').val('');
    $('#userName').val('');
    $('#userFullname').val('');
    $('#userRole').val('user');
    $('#userPass').attr('required', 'required'); // Pass required for new
    $('#passHint').addClass('hidden');
    $('#modal').removeClass('hidden').addClass('flex');
}

function editModal(data) {
    $('#modalTitle').text('Edit User');
    $('#userId').val(data.id);
    $('#userName').val(data.username);
    $('#userFullname').val(data.nama_lengkap);
    $('#userRole').val(data.role);
    $('#userPass').removeAttr('required'); // Pass optional for edit
    $('#passHint').removeClass('hidden');
    $('#modal').removeClass('hidden').addClass('flex');
}

function closeModal() {
    $('#modal').addClass('hidden').removeClass('flex');
}

function confirmDelete(id) {
    $('#btnConfirmDelete').attr('href', 'users.php?delete=' + id);
    $('#deleteModal').removeClass('hidden').addClass('flex');
}

function closeDeleteModal() {
    $('#deleteModal').addClass('hidden').removeClass('flex');
}
</script>

<?php require 'includes/footer.php'; ?>
