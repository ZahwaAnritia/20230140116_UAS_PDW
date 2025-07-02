<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') { header("Location: ../login.php"); exit(); }

$mahasiswa_id = $_SESSION['user_id'];


$stmt_prak = $conn->prepare("SELECT COUNT(id) AS total FROM pendaftaran_praktikum WHERE mahasiswa_id = ?");
$stmt_prak->bind_param("i", $mahasiswa_id);
$stmt_prak->execute();
$result_prak = $stmt_prak->get_result()->fetch_assoc();
$prak_diikuti = $result_prak ? $result_prak['total'] : 0;
$stmt_prak->close();


$stmt_selesai = $conn->prepare("SELECT COUNT(l.id) AS total FROM laporan l WHERE l.mahasiswa_id = ? AND l.status = 'Dinilai'");
$stmt_selesai->bind_param("i", $mahasiswa_id);
$stmt_selesai->execute();
$result_selesai = $stmt_selesai->get_result()->fetch_assoc();
$tugas_selesai = $result_selesai ? $result_selesai['total'] : 0;
$stmt_selesai->close();


$stmt_total_modul = $conn->prepare("SELECT COUNT(m.id) AS total FROM modul m JOIN pendaftaran_praktikum pp ON m.praktikum_id = pp.praktikum_id WHERE pp.mahasiswa_id = ?");
$stmt_total_modul->bind_param("i", $mahasiswa_id);
$stmt_total_modul->execute();
$result_total_modul = $stmt_total_modul->get_result()->fetch_assoc();
$total_modul = $result_total_modul ? $result_total_modul['total'] : 0;
$stmt_total_modul->close();


$stmt_terkumpul = $conn->prepare("SELECT COUNT(id) AS total FROM laporan WHERE mahasiswa_id = ?");
$stmt_terkumpul->bind_param("i", $mahasiswa_id);
$stmt_terkumpul->execute();
$result_terkumpul = $stmt_terkumpul->get_result()->fetch_assoc();
$laporan_terkumpul = $result_terkumpul ? $result_terkumpul['total'] : 0;
$stmt_terkumpul->close();


$tugas_menunggu = $total_modul - $laporan_terkumpul;
if ($tugas_menunggu < 0) $tugas_menunggu = 0;


$notifikasi_nilai = $conn->query("SELECT m.nama_modul, mp.nama_praktikum, l.nilai FROM laporan l JOIN modul m ON l.modul_id = m.id JOIN mata_praktikum mp ON m.praktikum_id = mp.id WHERE l.mahasiswa_id = $mahasiswa_id AND l.status = 'Dinilai' ORDER BY l.tanggal_kumpul DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);



$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php'; 
?>


<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-indigo-100 p-4 rounded-full"><i class="fa-solid fa-book-open text-2xl text-indigo-600"></i></div>
        <div>
            <p class="text-sm text-gray-500">Praktikum Diikuti</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $prak_diikuti; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-4 rounded-full"><i class="fa-solid fa-check-double text-2xl text-green-600"></i></div>
        <div>
            <p class="text-sm text-gray-500">Tugas Selesai Dinilai</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $tugas_selesai; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-4 rounded-full"><i class="fa-solid fa-pencil-alt text-2xl text-yellow-600"></i></div>
        <div>
            <p class="text-sm text-gray-500">Tugas Perlu Dikerjakan</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $tugas_menunggu; ?></p>
        </div>
    </div>
</div>

<!-- === BAGIAN BAWAH DISESUAIKAN DENGAN GAYA ASISTEN === -->
<div class="bg-white p-6 rounded-xl shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Nilai & Aktivitas Terbaru</h3>
    <div class="space-y-4">
        <?php if (!empty($notifikasi_nilai)): ?>
        <?php foreach ($notifikasi_nilai as $notif): ?>
            <div class="flex items-start space-x-4 p-3 hover:bg-gray-50 rounded-lg">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fa-solid fa-award text-green-600"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-sm text-gray-800">
                        Nilai <strong class="font-medium"><?php echo htmlspecialchars($notif['nilai']); ?></strong> diberikan untuk modul <strong><?php echo htmlspecialchars($notif['nama_modul']); ?></strong>
                    </p>
                    <p class="text-xs text-gray-500">
                        Praktikum: <?php echo htmlspecialchars($notif['nama_praktikum']); ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p class="text-gray-500">Belum ada nilai yang diberikan.</p>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>