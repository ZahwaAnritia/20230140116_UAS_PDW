<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') { header("Location: ../login.php"); exit(); }

$message = ''; 
$message_type = '';
$mahasiswa_id = $_SESSION['user_id'];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['batal'])) {
    $praktikum_id = $_POST['praktikum_id'];

   
    $stmt = $conn->prepare("DELETE FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
    $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);

    if ($stmt->execute()) {
        $message = 'Pendaftaran berhasil dibatalkan.';
        $message_type = 'success';
    } else {
        $message = 'Terjadi kesalahan saat membatalkan pendaftaran.';
        $message_type = 'error';
    }
    $stmt->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['daftar'])) {
    $praktikum_id = $_POST['praktikum_id'];
    $check_stmt = $conn->prepare("SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
    $check_stmt->bind_param("ii", $mahasiswa_id, $praktikum_id); 
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Anda sudah terdaftar pada praktikum ini.';
        $message_type = 'warning';
    } else {
        $stmt = $conn->prepare("INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
        if ($stmt->execute()) {
            $message = 'Pendaftaran berhasil!';
            $message_type = 'success';
        } else {
            $message = 'Terjadi kesalahan.';
            $message_type = 'error';
        }
        $stmt->close();
    }
    $check_stmt->close();
}

$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
require_once 'templates/header_mahasiswa.php';
?>

<?php if ($message): 
    $colors = ['success' => 'bg-green-100 text-green-800', 'error' => 'bg-red-100 text-red-800', 'warning' => 'bg-yellow-100 text-yellow-800'];
?>
<div class="mb-6 p-4 rounded-lg <?php echo $colors[$message_type]; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php
    $sql = "SELECT mp.*, (SELECT COUNT(*) FROM pendaftaran_praktikum pp WHERE pp.praktikum_id = mp.id AND pp.mahasiswa_id = $mahasiswa_id) as is_registered FROM mata_praktikum mp ORDER BY mp.nama_praktikum ASC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0):
        while($row = $result->fetch_assoc()):
    ?>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col justify-between transform hover:-translate-y-1.5 transition-transform duration-300">
        <div class="p-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
            <p class="text-gray-600 text-base mb-6 min-h-[5rem]"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
        </div>
        <div class="p-6 bg-gray-50">

            
            <?php if($row['is_registered']): ?>
                
                <form action="courses.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pendaftaran dari praktikum ini?');">
                    <input type="hidden" name="praktikum_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="batal" class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-bold py-3 px-4 rounded-lg transition-colors">
                        <i class="fa-solid fa-times-circle mr-2"></i>Batal Daftar
                    </button>
                </form>
            <?php else: ?>
                
                <form action="courses.php" method="POST">
                    <input type="hidden" name="praktikum_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="daftar" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                        <i class="fa-solid fa-plus-circle mr-2"></i>Daftar
                    </button>
                </form>
            <?php endif; ?>
            

        </div>
    </div>
    <?php endwhile; else: ?>
    <div class="col-span-full bg-white p-10 rounded-lg shadow-md text-center">
        <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
        <p class="text-xl text-gray-700">Belum ada praktikum yang tersedia.</p>
    </div>
    <?php endif; $conn->close(); ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>