<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect ke halaman yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'asisten') {
        header("Location: asisten/dashboard.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

$message = '';
$message_type = ''; // 'success' atau 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi!";
        $message_type = 'error';
    } else {
        $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'asisten') {
                    header("Location: asisten/dashboard.php");
                } elseif ($user['role'] == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                }
                exit();
            } else {
                $message = "Email atau password yang Anda masukkan salah.";
                $message_type = 'error';
            }
        } else {
            $message = "Akun dengan email tersebut tidak ditemukan.";
            $message_type = 'error';
        }
        $stmt->close();
    }
}
$conn->close();

if (isset($_GET['status']) && $_GET['status'] == 'registered') {
    $message = "Registrasi berhasil! Silakan login.";
    $message_type = 'success';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Link Font Awesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<!-- DIUBAH: Background menjadi gradient halus -->
<body class="bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center h-screen">

    <!-- DIUBAH: Diberi border dan shadow lebih besar untuk efek 3D -->
    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-gray-200 p-8">
        <div class="text-center mb-8">
            <!-- DIUBAH: Warna ikon menjadi ungu (indigo) & ditambahkan branding -->
            <i class="fa-solid fa-graduation-cap text-4xl text-indigo-600"></i>
            <h1 class="text-3xl font-bold text-gray-800 mt-2">SIMPRAK Login</h1>
            <p class="text-gray-500">Selamat datang kembali!</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-4 text-center p-3 rounded-lg <?php echo ($message_type == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <!-- DIUBAH: Ditambahkan ikon di dalam input -->
                <div class="mt-1 relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-envelope text-gray-400"></i>
                    </span>
                    <input type="email" id="email" name="email" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="nama@email.com" required>
                </div>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                 <!-- DIUBAH: Ditambahkan ikon di dalam input -->
                 <div class="mt-1 relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-lock text-gray-400"></i>
                    </span>
                    <input type="password" id="password" name="password" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="********" required>
                </div>
            </div>
            
            <!-- DIUBAH: Warna tombol menjadi ungu (indigo) -->
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-300">
                Login
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                Belum punya akun? 
                <!-- DIUBAH: Warna link menjadi ungu (indigo) -->
                <a href="register.php" class="font-medium text-indigo-600 hover:text-indigo-500">Daftar di sini</a>
            </p>
        </div>
    </div>

</body>
</html>