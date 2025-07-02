<?php

require_once 'config.php';
$message = ''; $message_type = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") { $nama = trim($_POST['nama']); $email = trim($_POST['email']); $password = trim($_POST['password']); $role = trim($_POST['role']); if (empty($nama) || empty($email) || empty($password) || empty($role)) { $message = "Semua field harus diisi!"; $message_type = 'error'; } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $message = "Format email tidak valid!"; $message_type = 'error'; } elseif (!in_array($role, ['mahasiswa', 'asisten'])) { $message = "Peran tidak valid!"; $message_type = 'error'; } else { $sql = "SELECT id FROM users WHERE email = ?"; $stmt = $conn->prepare($sql); $stmt->bind_param("s", $email); $stmt->execute(); $stmt->store_result(); if ($stmt->num_rows > 0) { $message = "Email sudah terdaftar."; $message_type = 'error'; } else { $hashed_password = password_hash($password, PASSWORD_BCRYPT); $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)"; $stmt_insert = $conn->prepare($sql_insert); $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role); if ($stmt_insert->execute()) { header("Location: login.php?status=registered"); exit(); } else { $message = "Terjadi kesalahan."; $message_type = 'error'; } $stmt_insert->close(); } $stmt->close(); } } $conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center min-h-screen py-12">
    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-gray-200 p-8">
        <div class="text-center mb-8">
            <i class="fa-solid fa-user-plus text-4xl text-indigo-600"></i>
            <h1 class="text-3xl font-bold text-gray-800 mt-2">Buat Akun Baru</h1>
            <p class="text-gray-500">Daftar untuk mulai menggunakan SIMPRAK.</p>
        </div>
        <?php if (!empty($message)): ?>
            <div class="mb-4 text-center p-3 rounded-lg bg-red-100 text-red-700"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="register.php" method="post" class="space-y-4">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Daftar Sebagai</label>
                <select id="role" name="role" class="mt-1 w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="asisten">Asisten</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">Daftar</button>
        </form>
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                Sudah punya akun? <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">Login di sini</a>
            </p>
        </div>
    </div>
</body>
</html>