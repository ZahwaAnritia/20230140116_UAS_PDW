<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Panel Mahasiswa SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

    
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
            
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fa-solid fa-graduation-cap text-2xl text-indigo-600 mr-2"></i>
                        <span class="text-xl font-bold text-gray-800">SIMPRAK</span>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-1">
                            <?php 
                                $navItems = [
                                    'dashboard' => ['text' => 'Dashboard', 'url' => 'dashboard.php'],
                                    'my_courses' => ['text' => 'Praktikum Saya', 'url' => 'my_courses.php'],
                                    'courses' => ['text' => 'Cari Praktikum', 'url' => 'courses.php'],
                                ];
                                foreach ($navItems as $key => $item):
                                    $isActive = ($activePage == $key);
                                    // Ganti gaya link agar mirip dengan sidebar asisten
                                    $class = $isActive 
                                        ? 'bg-indigo-100 text-indigo-700' 
                                        : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900';
                            ?>
                            <a href="<?php echo $item['url']; ?>" class="<?php echo $class; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors"><?php echo $item['text']; ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
               
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6 space-x-4">
                        <span class="text-gray-600 text-sm">Selamat datang, <strong class="text-gray-900"><?php echo htmlspecialchars($_SESSION['nama']); ?></strong></span>
                        <a href="../logout.php" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center">
                            <i class="fa-solid fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
       