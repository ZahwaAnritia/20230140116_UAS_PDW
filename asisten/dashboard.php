<?php

require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') { header("Location: ../login.php"); exit(); }
$total_modul_q = $conn->query("SELECT COUNT(id) AS total FROM modul"); $total_modul = $total_modul_q ? $total_modul_q->fetch_assoc()['total'] : 0;
$total_laporan_q = $conn->query("SELECT COUNT(id) AS total FROM laporan"); $total_laporan = $total_laporan_q ? $total_laporan_q->fetch_assoc()['total'] : 0;
$belum_dinilai_q = $conn->query("SELECT COUNT(id) AS total FROM laporan WHERE status = 'Terkumpul'"); $laporan_belum_dinilai = $belum_dinilai_q ? $belum_dinilai_q->fetch_assoc()['total'] : 0;
$aktivitas_terbaru = $conn->query("SELECT u.nama, m.nama_modul, mp.nama_praktikum, l.tanggal_kumpul FROM laporan l JOIN users u ON l.mahasiswa_id = u.id JOIN modul m ON l.modul_id = m.id JOIN mata_praktikum mp ON m.praktikum_id = mp.id ORDER BY l.tanggal_kumpul DESC LIMIT 5");

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header.php'; 
?>


<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 border-l-4 border-indigo-500">
        <div class="bg-indigo-100 p-4 rounded-full"><i class="fa-solid fa-puzzle-piece text-2xl text-indigo-600"></i></div>
        <div>
            <p class="text-sm text-gray-500">Total Modul</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $total_modul; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 border-l-4 border-green-500">
        <div class="bg-green-100 p-4 rounded-full"><i class="fa-solid fa-file-circle-check text-2xl text-green-600"></i></div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $total_laporan; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 border-l-4 border-yellow-500">
        <div class="bg-yellow-100 p-4 rounded-full"><i class="fa-solid fa-hourglass-half text-2xl text-yellow-600"></i></div>
        <div>
            <p class="text-sm text-gray-500">Laporan Perlu Dinilai</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $laporan_belum_dinilai; ?></p>
        </div>
    </div>
</div>


<div class="bg-white p-6 rounded-xl shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php if ($aktivitas_terbaru && $aktivitas_terbaru->num_rows > 0):
            while($aktivitas = $aktivitas_terbaru->fetch_assoc()): 
        ?>
        <div class="flex items-start space-x-4 p-3 hover:bg-gray-50 rounded-lg">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                <i class="fa-solid fa-user text-gray-500"></i>
            </div>
            <div class="flex-grow">
                <p class="text-sm text-gray-800">
                    <strong class="font-medium"><?php echo htmlspecialchars($aktivitas['nama']); ?></strong> 
                    mengumpulkan laporan untuk modul <strong><?php echo htmlspecialchars($aktivitas['nama_modul']); ?></strong>
                </p>
                <p class="text-xs text-gray-500">
                    Praktikum: <?php echo htmlspecialchars($aktivitas['nama_praktikum']); ?> • <?php echo date('d M Y, H:i', strtotime($aktivitas['tanggal_kumpul'])); ?>
                </p>
            </div>
        </div>
        <?php endwhile; else: ?>
        <p class="text-gray-500">Belum ada laporan yang masuk.</p>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>