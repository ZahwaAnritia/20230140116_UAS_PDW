<?php

require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$message = ''; $message_type = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) { $id = $_POST['id'] ?? null; $nama = trim($_POST['nama']); $email = trim($_POST['email']); $password = $_POST['password']; $role = $_POST['role']; if (empty($nama) || empty($email) || empty($role)) { $message = "Nama, Email, dan Peran tidak boleh kosong!"; $message_type = 'error'; } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $message = "Format email tidak valid!"; $message_type = 'error'; } else { $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND (id != ? OR ? IS NULL)"); $stmt_check->bind_param("sii", $email, $id, $id); $stmt_check->execute(); if ($stmt_check->get_result()->num_rows > 0) { $message = "Email sudah digunakan."; $message_type = 'error'; } $stmt_check->close(); } if (empty($message_type)) { if ($id) { if (!empty($password)) { $hashed_password = password_hash($password, PASSWORD_BCRYPT); $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, password = ?, role = ? WHERE id = ?"); $stmt->bind_param("ssssi", $nama, $email, $hashed_password, $role, $id); } else { $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?"); $stmt->bind_param("sssi", $nama, $email, $role, $id); } if ($stmt->execute()) { $message = "Data pengguna diperbarui!"; $message_type = 'success'; } else { $message = "Gagal memperbarui."; $message_type = 'error'; } $stmt->close(); } else { if (empty($password)) { $message = "Password wajib diisi untuk pengguna baru!"; $message_type = 'error'; } else { $hashed_password = password_hash($password, PASSWORD_BCRYPT); $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)"); $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role); if ($stmt->execute()) { $message = "Pengguna baru ditambahkan!"; $message_type = 'success'; } else { $message = "Gagal menambahkan."; $message_type = 'error'; } $stmt->close(); } } } }
if (isset($_GET['delete'])) { $id = $_GET['delete']; if ($id == ($_SESSION['user_id'] ?? 0)) { $message = "Anda tidak dapat menghapus akun Anda sendiri."; $message_type = 'error'; } else { $stmt = $conn->prepare("DELETE FROM users WHERE id = ?"); $stmt->bind_param("i", $id); if ($stmt->execute()) { $message = "Pengguna berhasil dihapus!"; $message_type = 'success'; } else { $message = "Gagal menghapus pengguna."; $message_type = 'error'; } $stmt->close(); } }
$user_to_edit = null; $form_title = 'Tambah Pengguna Baru'; if (isset($_GET['edit'])) { $id = $_GET['edit']; $stmt = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?"); $stmt->bind_param("i", $id); $stmt->execute(); $user_to_edit = $stmt->get_result()->fetch_assoc(); $form_title = 'Edit Pengguna'; $stmt->close(); }

$pageTitle = 'Manajemen Pengguna';
$activePage = 'users';
require_once 'templates/header.php';
?>

<?php if ($message): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="bg-white p-6 rounded-xl shadow-md mb-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo $form_title; ?></h3>
    <form action="users.php" method="POST" class="space-y-4">
        <input type="hidden" name="id" value="<?php echo $user_to_edit['id'] ?? ''; ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?php echo htmlspecialchars($user_to_edit['nama'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?php echo htmlspecialchars($user_to_edit['email'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" <?php echo isset($user_to_edit) ? '' : 'required'; ?>>
                <?php if (isset($user_to_edit)): ?>
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                <?php endif; ?>
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Peran</label>
                <select name="role" id="role" class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="mahasiswa" <?php echo (isset($user_to_edit) && $user_to_edit['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                    <option value="asisten" <?php echo (isset($user_to_edit) && $user_to_edit['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
                </select>
            </div>
        </div>
        <div class="mt-6 text-right">
            <button type="submit" name="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg"><i class="fa-solid fa-save mr-2"></i>Simpan</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-6"><h3 class="text-xl font-bold text-gray-800">Daftar Pengguna</h3></div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Peran</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php
                $result = $conn->query("SELECT id, nama, email, role FROM users ORDER BY nama ASC");
                while($row = $result->fetch_assoc()):
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td class="py-4 px-6 text-gray-600"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td class="py-4 px-6"><span class="px-2 py-1 font-semibold leading-tight text-sm rounded-full <?php echo ($row['role'] == 'asisten') ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800'; ?>"><?php echo ucfirst($row['role']); ?></span></td>
                    <td class="py-4 px-6 whitespace-nowrap space-x-2">
                        <a href="users.php?edit=<?php echo $row['id']; ?>" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold">Edit</a>
                        <?php if ($row['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                        <a href="users.php?delete=<?php echo $row['id']; ?>" class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold" onclick="return confirm('Yakin hapus?');">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>