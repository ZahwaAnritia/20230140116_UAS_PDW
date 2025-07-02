<?php
// ... (Bagian PHP Anda, tidak perlu diubah) ...
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa' || !isset($_GET['id'])) { header("Location: ../login.php"); exit(); }
$mahasiswa_id = $_SESSION['user_id']; $praktikum_id = $_GET['id'];
$message = ''; $message_type = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kumpul_laporan'])) {
    $modul_id = $_POST['modul_id'];
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
        $target_dir = "../uploads/laporan/"; if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
        $file_name = "laporan_" . $mahasiswa_id . "_" . $modul_id . "_" . time() . "_" . basename($_FILES["file_laporan"]["name"]);
        $target_file = $target_dir . $file_name; $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['pdf', 'doc', 'docx', 'zip', 'rar'];
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["file_laporan"]["tmp_name"], $target_file)) {
                $stmt_check = $conn->prepare("SELECT id, file_laporan FROM laporan WHERE mahasiswa_id = ? AND modul_id = ?");
                $stmt_check->bind_param("ii", $mahasiswa_id, $modul_id); $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                if ($result_check->num_rows > 0) {
                    $old_report = $result_check->fetch_assoc();
                    $stmt_update = $conn->prepare("UPDATE laporan SET file_laporan = ?, tanggal_kumpul = NOW(), status = 'Terkumpul', nilai = NULL, feedback = NULL WHERE id = ?");
                    $stmt_update->bind_param("si", $file_name, $old_report['id']); $stmt_update->execute();
                    if (file_exists($target_dir . $old_report['file_laporan'])) { unlink($target_dir . $old_report['file_laporan']); }
                    $message = "Laporan berhasil diperbarui!";
                } else {
                    $stmt_insert = $conn->prepare("INSERT INTO laporan (modul_id, mahasiswa_id, file_laporan, status) VALUES (?, ?, ?, 'Terkumpul')");
                    $stmt_insert->bind_param("iis", $modul_id, $mahasiswa_id, $file_name); $stmt_insert->execute();
                    $message = "Laporan berhasil dikumpulkan!";
                }
                $message_type = 'success';
            } else { $message = "Gagal unggah file."; $message_type = 'error'; }
        } else { $message = "Format file tidak diizinkan."; $message_type = 'error'; }
    } else { $message = "Anda harus memilih file."; $message_type = 'error'; }
}
$stmt_prak = $conn->prepare("SELECT nama_praktikum FROM mata_praktikum WHERE id = ?");
$stmt_prak->bind_param("i", $praktikum_id); $stmt_prak->execute();
$result_prak = $stmt_prak->get_result();
if ($result_prak->num_rows == 0) { header("Location: my_courses.php"); exit(); }
$praktikum = $result_prak->fetch_assoc();
$pageTitle = htmlspecialchars($praktikum['nama_praktikum']); $activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';
?>

<?php if ($message): 
    $colors = ['success' => 'bg-green-100 text-green-800', 'error' => 'bg-red-100 text-red-800'];
?>
<div class="mb-6 p-4 rounded-lg <?php echo $colors[$message_type]; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Detail & Tugas: <?php echo $pageTitle; ?></h2>
    <a href="my_courses.php" class="text-indigo-600 hover:underline">← Kembali</a>
</div>

<div class="space-y-8">
    <?php
    $stmt_modul = $conn->prepare("SELECT * FROM modul WHERE praktikum_id = ? ORDER BY nama_modul ASC");
    $stmt_modul->bind_param("i", $praktikum_id); $stmt_modul->execute();
    $result_modul = $stmt_modul->get_result();
    if ($result_modul->num_rows > 0):
        while ($modul = $result_modul->fetch_assoc()):
            $stmt_laporan = $conn->prepare("SELECT * FROM laporan WHERE modul_id = ? AND mahasiswa_id = ?");
            $stmt_laporan->bind_param("ii", $modul['id'], $mahasiswa_id); $stmt_laporan->execute();
            $laporan = $stmt_laporan->get_result()->fetch_assoc();
    ?>
    <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
            <div class="border-b md:border-b-0 md:border-r pr-8 pb-4 md:pb-0">
                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($modul['nama_modul']); ?></h3>
                <p class="text-gray-500 text-sm mt-1">Materi & Pengumpulan Laporan</p>
                <?php if (!empty($modul['file_materi'])): ?>
                <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" target="_blank" class="inline-block mt-4 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 text-sm font-semibold">
                    <i class="fa-solid fa-download mr-2"></i>Download Materi
                </a>
                <?php else: ?>
                <p class="text-gray-400 mt-4 text-sm italic">Materi belum tersedia.</p>
                <?php endif; ?>
                <form action="course_detail.php?id=<?php echo $praktikum_id; ?>" method="POST" enctype="multipart/form-data" class="mt-4">
                    <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                    <input type="file" name="file_laporan" id="file_laporan_<?php echo $modul['id']; ?>" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600" required>
                    <button type="submit" name="kumpul_laporan" class="mt-2 w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold"><?php echo $laporan ? 'Perbarui Laporan' : 'Kumpulkan Laporan'; ?></button>
                </form>
            </div>
            
            <div>
                <h4 class="font-semibold text-gray-700">Status Laporan Anda</h4>
                <?php if ($laporan): ?>
                <div class="mt-2 p-4 rounded-lg bg-gray-50 border space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <span class="font-bold text-lg <?php echo ($laporan['status'] == 'Dinilai') ? 'text-green-600' : 'text-yellow-600'; ?>"><?php echo ($laporan['status'] == 'Terkumpul') ? 'Menunggu Penilaian' : 'Sudah Dinilai'; ?></span>
                    </div>
                     <?php if ($laporan['status'] == 'Dinilai'): ?>
                    <div>
                        <p class="text-xs text-gray-500">Nilai Akhir</p>
                        <p class="text-3xl font-bold text-indigo-700"><?php echo $laporan['nilai']; ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Feedback Asisten</p>
                        <p class="mt-1 p-3 bg-white rounded border italic text-sm"><?php echo !empty($laporan['feedback']) ? nl2br(htmlspecialchars($laporan['feedback'])) : 'Tidak ada feedback.'; ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-xs text-gray-500">File Terkumpul</p>
                        <a href="../uploads/laporan/<?php echo htmlspecialchars($laporan['file_laporan']); ?>" target="_blank" class="font-semibold text-indigo-600 hover:underline break-words text-sm"><?php echo htmlspecialchars($laporan['file_laporan']); ?></a>
                    </div>
                </div>
                <?php else: ?>
                <p class="mt-2 text-gray-500 italic">Anda belum mengumpulkan laporan untuk modul ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; else: ?>
    <div class="bg-white p-10 rounded-lg shadow-md text-center">
        <p class="text-gray-700 text-xl">Belum ada modul yang ditambahkan untuk praktikum ini.</p>
    </div>
    <?php endif; $stmt_modul->close(); $conn->close(); ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>