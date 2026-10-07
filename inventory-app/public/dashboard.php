<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'admin';

// Hitung total barang dari database secara dinamis
$stmtTotalBarang = $pdo->query("SELECT COUNT(*) FROM products");
$totalBarang = $stmtTotalBarang->fetchColumn();

// Hitung total kategori unik
$stmtTotalKategori = $pdo->query("SELECT COUNT(DISTINCT kategori) FROM products");
$totalKategori = $stmtTotalKategori->fetchColumn();

// Hitung total pengguna
$stmtTotalUser = $pdo->query("SELECT COUNT(*) FROM users");
$totalUser = $stmtTotalUser->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-indigo-700 min-h-screen font-sans text-gray-800">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-white/10 backdrop-blur-md border-r border-white/20 text-white">
                <div class="flex items-center justify-center h-20 border-b border-white/10">
                    <h1 class="text-xl font-bold tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-yellow-300"></i> InventorySys
                    </h1>
                </div>
                <div class="flex flex-col flex-grow px-4 py-6 space-y-2">
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-white/20 rounded-xl font-medium text-white shadow">
                        <i class="fa-solid fa-chart-pie w-5"></i> Dashboard
                    </a>
                    <a href="barang.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/10 rounded-xl font-medium text-white/80 hover:text-white transition">
                        <i class="fa-solid fa-box w-5"></i> Data Barang
                    </a>
                    <!-- Menu Transaksi Ditambahkan di Sini -->
                    <a href="transaksi.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/10 rounded-xl font-medium text-white/80 hover:text-white transition">
                        <i class="fa-solid fa-right-left w-5"></i> Transaksi
                    </a>
                </div>
                <div class="p-4 border-t border-white/10">
                    <a href="logout.php" class="flex items-center justify-center gap-2 w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-xl transition shadow-lg">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 h-full overflow-y-auto">
            
            <!-- Top Navbar -->
            <header class="bg-white/10 backdrop-blur-md border-b border-white/20 h-20 flex items-center justify-between px-8 text-white">
                <h2 class="text-2xl font-bold">Dashboard Utama</h2>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="font-semibold text-white"><?= htmlspecialchars($username) ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-widest font-semibold"><?= htmlspecialchars($role) ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-yellow-400 text-indigo-900 font-bold flex items-center justify-center shadow-md">
                        <?= strtoupper(substr($username, 0, 1)) ?>
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-8">
                
                <!-- Welcome Banner -->
                <div class="bg-white/20 backdrop-blur-md border border-white/30 rounded-2xl p-6 text-white mb-8 shadow-xl flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-3xl font-bold mb-2">Selamat Datang, <?= htmlspecialchars($username) ?>! 👋</h3>
                        <p class="text-blue-100 text-sm">Sistem manajemen inventaris Anda siap digunakan.</p>
                    </div>
                    <div class="bg-white/10 px-4 py-3 rounded-xl border border-white/20 text-center">
                        <span class="block text-xs text-blue-200 uppercase tracking-wider font-semibold">Status Sistem</span>
                        <span class="text-green-300 font-bold flex items-center gap-1.5 justify-center mt-0.5">
                            <span class="w-2.5 h-2.5 bg-green-400 rounded-full animate-pulse"></span> Online
                        </span>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-2xl p-6 shadow-xl flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase">Total Barang</p>
                            <h4 class="text-3xl font-extrabold text-gray-800 mt-1"><?= $totalBarang ?></h4>
                        </div>
                        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-xl flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase">Kategori</p>
                            <h4 class="text-3xl font-extrabold text-gray-800 mt-1"><?= $totalKategori ?></h4>
                        </div>
                        <div class="w-14 h-14 bg-yellow-100 text-yellow-600 rounded-2xl flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-tags"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-xl flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase">Pengguna</p>
                            <h4 class="text-3xl font-extrabold text-gray-800 mt-1"><?= $totalUser ?></h4>
                        </div>
                        <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

</body>
</html>