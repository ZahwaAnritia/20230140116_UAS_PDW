<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Panel Asisten SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<div class="flex h-screen bg-gray-100">
    
    <aside class="w-64 bg-gray-800 text-white flex-shrink-0 flex flex-col">
        <div class="flex items-center justify-center h-20 border-b border-gray-700">
            <i class="fa-solid fa-graduation-cap text-2xl text-indigo-500 mr-2"></i>
            <h1 class="text-xl font-bold">SIMPRAK Asisten</h1>
        </div>
        <nav class="flex-grow p-4">
            <ul class="space-y-2">
                <?php 
                    $navItems = [
                        'dashboard' => ['icon' => 'fa-tachometer-alt', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
                        'matkul'    => ['icon' => 'fa-book', 'text' => 'Manajemen Praktikum', 'url' => 'matkul.php'],
                        'modul'     => ['icon' => 'fa-puzzle-piece', 'text' => 'Manajemen Modul', 'url' => 'modul.php'],
                        'laporan'   => ['icon' => 'fa-file-alt', 'text' => 'Laporan Masuk', 'url' => 'laporan.php'],
                        'users'     => ['icon' => 'fa-users', 'text' => 'Manajemen Pengguna', 'url' => 'users.php'],
                    ];
                    foreach ($navItems as $key => $item):
                        $isActive = ($activePage == $key);
                        $class = $isActive 
                            ? 'bg-indigo-600 text-white' 
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white';
                ?>
                <li>
                    <a href="<?php echo $item['url']; ?>" class="<?php echo $class; ?> flex items-center px-4 py-2.5 rounded-lg transition-colors duration-200">
                        <i class="fa-solid <?php echo $item['icon']; ?> w-5 h-5 mr-3 text-center"></i>
                        <span><?php echo $item['text']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div class="p-4 border-t border-gray-700">
             <a href="../logout.php" class="flex items-center w-full px-4 py-2.5 rounded-lg text-gray-300 hover:bg-red-500 hover:text-white transition-colors duration-200">
                <i class="fa-solid fa-sign-out-alt w-5 h-5 mr-3 text-center"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

   
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-6 bg-white border-b">
            <h1 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600 text-sm">Selamat datang, <strong class="text-gray-900"><?php echo htmlspecialchars($_SESSION['nama']); ?></strong></span>
            </div>
        </header>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
           