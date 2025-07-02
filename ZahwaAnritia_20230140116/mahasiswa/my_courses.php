<?php
// ... (Bagian PHP Anda, tidak perlu diubah) ...
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') { header("Location: ../login.php"); exit(); }
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php
    $mahasiswa_id = $_SESSION['user_id'];
    $sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi FROM pendaftaran_praktikum pp JOIN mata_praktikum mp ON pp.praktikum_id = mp.id WHERE pp.mahasiswa_id = ? ORDER BY mp.nama_praktikum ASC";
    $stmt = $conn->prepare($sql); $stmt->bind_param("i", $mahasiswa_id); $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0):
        while($row = $result->fetch_assoc()):
    ?>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col justify-between transform hover:-translate-y-1.5 transition-transform duration-300">
        <div class="p-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
            <p class="text-gray-600 text-base mb-6 min-h-[5rem]"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
        </div>
        <div class="p-6 bg-gray-50">
            <a href="course_detail.php?id=<?php echo $row['id']; ?>" class="block text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg">Lihat Detail <i class="fa-solid fa-arrow-right ml-2"></i></a>
        </div>
    </div>
    <?php endwhile; else: ?>
    <div class="col-span-full bg-white p-10 rounded-lg shadow-md text-center">
        <i class="fa-solid fa-book-open text-5xl text-gray-300 mb-4"></i>
        <p class="text-xl text-gray-700">Anda belum terdaftar di praktikum manapun.</p>
        <a href="courses.php" class="text-indigo-600 hover:underline mt-2 inline-block font-semibold">Cari praktikum untuk diikuti →</a>
    </div>
    <?php endif; $stmt->close(); $conn->close(); ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>