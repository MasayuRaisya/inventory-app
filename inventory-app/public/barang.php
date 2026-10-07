<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'admin';
$success = '';
$error = '';

// Proses Tambah Barang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $stok = intval($_POST['stok'] ?? 0);
    $harga = floatval($_POST['harga'] ?? 0);

    if (!empty($nama_barang) && !empty($kategori)) {
        $stmt = $pdo->prepare("INSERT INTO products (nama_barang, kategori, stok, harga) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nama_barang, $kategori, $stok, $harga])) {
            $success = "Barang berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan barang.";
        }
    } else {
        $error = "Nama barang dan kategori wajib diisi!";
    }
}

// Logika Pencarian (Search)
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE nama_barang LIKE ? OR kategori LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
    $products = $stmt->fetchAll();
} else {
    $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Barang - Inventory System</title>
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
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/10 rounded-xl font-medium text-white/80 hover:text-white transition">
                        <i class="fa-solid fa-chart-pie w-5"></i> Dashboard
                    </a>
                    <a href="barang.php" class="flex items-center gap-3 px-4 py-3 bg-white/20 rounded-xl font-medium text-white shadow">
                        <i class="fa-solid fa-box w-5"></i> Data Barang
                    </a>
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
                <h2 class="text-2xl font-bold">Data Barang</h2>
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
                
                <?php if (!empty($success)): ?>
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl text-sm">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl text-sm">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Form Tambah Barang -->
                <div class="bg-white rounded-2xl p-6 shadow-xl mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-circle-plus text-blue-600"></i> Tambah Barang Baru
                    </h3>
                    <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nama Barang</label>
                            <input type="text" name="nama_barang" required placeholder="Contoh: Laptop Asus"
                                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Kategori</label>
                            <input type="text" name="kategori" required placeholder="Contoh: Elektronik"
                                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Stok</label>
                            <input type="number" name="stok" required placeholder="10"
                                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Harga (Rp)</label>
                            <input type="number" step="0.01" name="harga" required placeholder="5000000"
                                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition shadow-md">
                                <i class="fa-solid fa-save mr-2"></i> Simpan Barang
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Daftar Stok Barang & Search Bar -->
                <div class="bg-white rounded-2xl p-6 shadow-xl">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-blue-600"></i> Daftar Stok Barang
                        </h3>
                        <!-- Form Search -->
                        <form method="GET" action="" class="flex items-center gap-2 w-full md:w-auto">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama/kategori..." 
                                class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm w-full md:w-64">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                                <i class="fa-solid fa-search"></i>
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="barang.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-xl text-sm font-semibold transition" title="Reset Pencarian">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                                    <th class="p-3 rounded-l-xl">No</th>
                                    <th class="p-3">Nama Barang</th>
                                    <th class="p-3">Kategori</th>
                                    <th class="p-3">Stok</th>
                                    <th class="p-3">Harga</th>
                                    <th class="p-3 text-center rounded-r-xl">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm">
                                <?php if (count($products) > 0): ?>
                                    <?php $no = 1; foreach ($products as $p): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 font-semibold text-gray-500"><?= $no++ ?></td>
                                        <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($p['nama_barang']) ?></td>
                                        <td class="p-3">
                                            <span class="bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                                <?= htmlspecialchars($p['kategori']) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 font-semibold text-gray-700"><?= $p['stok'] ?></td>
                                        <td class="p-3 font-medium text-gray-700">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                        <td class="p-3 text-center space-x-2">
                                            <a href="edit_barang.php?id=<?= $p['id'] ?>" class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition shadow">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </a>
                                            <a href="hapus_barang.php?id=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus barang ini?')" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition shadow">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="p-6 text-center text-gray-400">Tidak ada data barang yang ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

</body>
</html>