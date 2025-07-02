<?php

require_once '../config.php';
$message = ''; $message_type = ''; if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) { $nama_praktikum = trim($_POST['nama_praktikum']); $deskripsi = trim($_POST['deskripsi']); $id = $_POST['id'] ?? null; if (empty($nama_praktikum)) { $message = "Nama praktikum tidak boleh kosong!"; $message_type = 'error'; } else { if ($id) { $stmt = $conn->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?"); $stmt->bind_param("ssi", $nama_praktikum, $deskripsi, $id); $message = "Data berhasil diperbarui!"; } else { $stmt = $conn->prepare("INSERT INTO mata_praktikum (nama_praktikum, deskripsi) VALUES (?, ?)"); $stmt->bind_param("ss", $nama_praktikum, $deskripsi); $message = "Data berhasil ditambahkan!"; } if ($stmt->execute()) { $message_type = 'success'; } else { $message = "Terjadi kesalahan: " . $stmt->error; $message_type = 'error'; } $stmt->close(); } } if (isset($_GET['delete'])) { $id = $_GET['delete']; $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ?"); $stmt->bind_param("i", $id); if ($stmt->execute()) { $message = "Data berhasil dihapus!"; $message_type = 'success'; } else { $message = "Gagal menghapus data."; $message_type = 'error'; } $stmt->close(); } $matkul_to_edit = null; $form_title = 'Tambah Mata Praktikum Baru'; if (isset($_GET['edit'])) { $id = $_GET['edit']; $stmt = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ?"); $stmt->bind_param("i", $id); $stmt->execute(); $result = $stmt->get_result(); if ($result->num_rows > 0) { $matkul_to_edit = $result->fetch_assoc(); $form_title = 'Edit Mata Praktikum'; } $stmt->close(); }
$pageTitle = 'Manajemen Praktikum';
$activePage = 'matkul';
require_once 'templates/header.php';
?>


<?php if ($message): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" role="alert">
    <?php echo $message; ?>
</div>
<?php endif; ?>


<div class="bg-white p-6 rounded-xl shadow-md mb-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo $form_title; ?></h3>
    <form action="matkul.php" method="POST" class="space-y-4">
        <input type="hidden" name="id" value="<?php echo $matkul_to_edit['id'] ?? ''; ?>">
        <div>
            <label for="nama_praktikum" class="block text-sm font-medium text-gray-700">Nama Praktikum</label>
            <input type="text" name="nama_praktikum" id="nama_praktikum" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?php echo htmlspecialchars($matkul_to_edit['nama_praktikum'] ?? ''); ?>" required>
        </div>
        <div>
            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
            <textarea name="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($matkul_to_edit['deskripsi'] ?? ''); ?></textarea>
        </div>
        <div class="flex items-center justify-end space-x-4">
            <?php if ($matkul_to_edit): ?>
            <a href="matkul.php" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</a>
            <?php endif; ?>
            <button type="submit" name="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg"><i class="fa-solid fa-save mr-2"></i>Simpan</button>
        </div>
    </form>
</div>


<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-6">
        <h3 class="text-xl font-bold text-gray-800">Daftar Mata Praktikum</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Nama Praktikum</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php
                $result = $conn->query("SELECT * FROM mata_praktikum ORDER BY nama_praktikum ASC");
                if ($result && $result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                    <td class="py-4 px-6 text-gray-600"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                    <td class="py-4 px-6 whitespace-nowrap space-x-2">
                        <a href="matkul.php?edit=<?php echo $row['id']; ?>" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold hover:bg-indigo-200">Edit</a>
                        <a href="matkul.php?delete=<?php echo $row['id']; ?>" class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold hover:bg-red-200" onclick="return confirm('Anda yakin?');">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="3" class="text-center py-6 text-gray-500">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>