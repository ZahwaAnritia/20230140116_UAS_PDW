<?php

require_once '../config.php';
$message = ''; $message_type = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) { $praktikum_id = $_POST['praktikum_id']; $nama_modul = trim($_POST['nama_modul']); $id = $_POST['id'] ?? null; $file_materi_lama = $_POST['file_materi_lama'] ?? ''; $file_materi_path = $file_materi_lama; if (empty($praktikum_id) || empty($nama_modul)) { $message = "Nama modul dan pilihan praktikum tidak boleh kosong!"; $message_type = 'error'; } else { if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) { $target_dir = "../uploads/materi/"; if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); } $file_name = time() . '_' . basename($_FILES["file_materi"]["name"]); $target_file = $target_dir . $file_name; $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); $allowed_types = ['pdf', 'doc', 'docx', 'pptx']; if (in_array($file_type, $allowed_types)) { if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) { if (!empty($file_materi_lama) && file_exists($target_dir . $file_materi_lama)) { unlink($target_dir . $file_materi_lama); } $file_materi_path = $file_name; } else { $message = "Error saat unggah file."; $message_type = 'error'; } } else { $message = "Format file tidak diizinkan."; $message_type = 'error'; } } if ($message_type != 'error') { if ($id) { $stmt = $conn->prepare("UPDATE modul SET praktikum_id = ?, nama_modul = ?, file_materi = ? WHERE id = ?"); $stmt->bind_param("issi", $praktikum_id, $nama_modul, $file_materi_path, $id); $message = "Modul berhasil diperbarui!"; } else { $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, nama_modul, file_materi) VALUES (?, ?, ?)"); $stmt->bind_param("iss", $praktikum_id, $nama_modul, $file_materi_path); $message = "Modul baru berhasil ditambahkan!"; } $stmt->execute(); $stmt->close(); $message_type = 'success'; } } }
if (isset($_GET['delete'])) { $id_to_delete = $_GET['delete']; $stmt_select = $conn->prepare("SELECT file_materi FROM modul WHERE id = ?"); $stmt_select->bind_param("i", $id_to_delete); $stmt_select->execute(); $result_file = $stmt_select->get_result()->fetch_assoc(); $stmt_select->close(); $stmt_delete = $conn->prepare("DELETE FROM modul WHERE id = ?"); $stmt_delete->bind_param("i", $id_to_delete); if ($stmt_delete->execute()) { if ($result_file && !empty($result_file['file_materi']) && file_exists('../uploads/materi/' . $result_file['file_materi'])) { unlink('../uploads/materi/' . $result_file['file_materi']); } $message = "Modul berhasil dihapus!"; $message_type = 'success'; } $stmt_delete->close(); }
$modul_to_edit = null; $form_title = 'Tambah Modul Baru'; if (isset($_GET['edit'])) { $id = $_GET['edit']; $stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?"); $stmt->bind_param("i", $id); $stmt->execute(); $modul_to_edit = $stmt->get_result()->fetch_assoc(); $form_title = 'Edit Modul'; $stmt->close(); }
$praktikum_list = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC");
$modul_result = $conn->query("SELECT m.id, m.nama_modul, m.file_materi, mp.nama_praktikum FROM modul m JOIN mata_praktikum mp ON m.praktikum_id = mp.id ORDER BY mp.nama_praktikum, m.nama_modul ASC");

$pageTitle = 'Manajemen Modul';
$activePage = 'modul';
require_once 'templates/header.php';
?>


<?php if ($message): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"><?php echo $message; ?></div>
<?php endif; ?>


<div class="bg-white p-6 rounded-xl shadow-md mb-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo $form_title; ?></h3>
    <form action="modul.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id" value="<?php echo $modul_to_edit['id'] ?? ''; ?>">
        <input type="hidden" name="file_materi_lama" value="<?php echo $modul_to_edit['file_materi'] ?? ''; ?>">
        <div>
            <label for="praktikum_id" class="block text-sm font-medium text-gray-700">Untuk Mata Praktikum</label>
            <select name="praktikum_id" id="praktikum_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                <option value="">-- Pilih Mata Praktikum --</option>
                <?php $praktikum_list->data_seek(0); while($prak = $praktikum_list->fetch_assoc()): ?>
                <option value="<?php echo $prak['id']; ?>" <?php echo (isset($modul_to_edit) && $modul_to_edit['praktikum_id'] == $prak['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($prak['nama_praktikum']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="nama_modul" class="block text-sm font-medium text-gray-700">Nama Modul</label>
            <input type="text" name="nama_modul" id="nama_modul" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?php echo htmlspecialchars($modul_to_edit['nama_modul'] ?? ''); ?>" required>
        </div>
        <div>
            <label for="file_materi" class="block text-sm font-medium text-gray-700">File Materi (PDF, DOCX)</label>
            <input type="file" name="file_materi" id="file_materi" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <?php if (isset($modul_to_edit) && !empty($modul_to_edit['file_materi'])): ?>
            <p class="text-xs text-gray-500 mt-1">File saat ini: <?php echo htmlspecialchars($modul_to_edit['file_materi']); ?>. Kosongkan jika tidak ingin mengubah.</p>
            <?php endif; ?>
        </div>
        <div class="text-right">
            <button type="submit" name="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg"><i class="fa-solid fa-save mr-2"></i>Simpan Modul</button>
        </div>
    </form>
</div>


<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-6"><h3 class="text-xl font-bold text-gray-800">Daftar Modul</h3></div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Praktikum</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Nama Modul</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">File Materi</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($modul_result && $modul_result->num_rows > 0):
                    while($row = $modul_result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 text-gray-600"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                    <td class="py-4 px-6 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_modul']); ?></td>
                    <td class="py-4 px-6">
                        <?php if (!empty($row['file_materi'])): ?>
                        <a href="../uploads/materi/<?php echo htmlspecialchars($row['file_materi']); ?>" target="_blank" class="text-indigo-600 hover:underline">
                            <i class="fa-solid fa-download mr-1"></i> Download
                        </a>
                        <?php else: echo '<span class="text-gray-400">Tidak ada</span>'; endif; ?>
                    </td>
                    <td class="py-4 px-6 whitespace-nowrap space-x-2">
                        <a href="modul.php?edit=<?php echo $row['id']; ?>" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold">Edit</a>
                        <a href="modul.php?delete=<?php echo $row['id']; ?>" class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold" onclick="return confirm('Yakin hapus?');">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="4" class="text-center py-6 text-gray-500">Belum ada modul.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>