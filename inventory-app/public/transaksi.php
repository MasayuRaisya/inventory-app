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

// Proses Form Transaksi Masuk/Keluar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $type = $_POST['type'] ?? ''; // 'masuk' atau 'keluar'
    $quantity = intval($_POST['quantity'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($product_id > 0 && ($type === 'masuk' || $type === 'keluar') && $quantity > 0) {
        // Cek stok saat ini jika transaksi keluar
        if ($type === 'keluar') {
            $stmtCheck = $pdo->prepare("SELECT stok, nama_barang FROM products WHERE id = ?");
            $stmtCheck->execute([$product_id]);
            $prod = $stmtCheck->fetch();

            if ($prod && $prod['stok'] < $quantity) {
                $error = "Stok tidak mencukupi! Stok '{$prod['nama_barang']}' saat ini tersisa {$prod['stok']}.";
            }
        }

        if (empty($error)) {
            try {
                $pdo->beginTransaction();

                // 1. Catat ke tabel transactions
                $stmtTrans = $pdo->prepare("INSERT INTO transactions (product_id, user_id, type, quantity) VALUES (?, ?, ?, ?)");
                $stmtTrans->execute([$product_id, $user_id, $type, $quantity]);

                // 2. Update stok di tabel products
                if ($type === 'masuk') {
                    $stmtUpdate = $pdo->prepare("UPDATE products SET stok = stok + ? WHERE id = ?");
                } else {
                    $stmtUpdate = $pdo->prepare("UPDATE products SET stok = stok - ? WHERE id = ?");
                }
                $stmtUpdate->execute([$quantity, $product_id]);

                $pdo->commit();
                $success = "Transaksi berhasil dicatat dan stok berhasil diperbarui!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    } else {
        $error = "Semua field wajib diisi dengan benar!";
    }
}

// Ambil daftar produk untuk dropdown pilihan barang
$products = $pdo->query("SELECT id, nama_barang, stok FROM products ORDER BY nama_barang ASC")->fetchAll();

// Ambil riwayat transaksi dengan JOIN ke products dan users
$queryHistory = "SELECT t.*, p.nama_barang, u.username 
                 FROM transactions t 
                 JOIN products p ON t.product_id = p.id 
                 JOIN users u ON t.user_id = u.id 
                 ORDER BY t.created_at DESC";
$history = $pdo->query($queryHistory)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Barang - Inventory System</title>
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
                    <a href="barang.php" class="flex items-center gap-3 px-4 py-3 hover:bg-white/10 rounded-xl font-medium text-white/80 hover:text-white transition">
                        <i class="fa-solid fa-box w-5"></i> Data Barang
                    </a>
                    <a href="transaksi.php" class="flex items-center gap-3 px-4 py-3 bg-white/20 rounded-xl font-medium text-white shadow">
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
                <h2 class="text-2xl font-bold">Transaksi Barang Masuk & Keluar</h2>
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

                <!-- Form Input Transaksi -->
                <div class="bg-white rounded-2xl p-6 shadow-xl mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-plus-minus text-blue-600"></i> Catat Transaksi Baru
                    </h3>
                    <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Pilih Barang</label>
                            <select name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                                <option value="">-- Pilih Barang --</option>
                                <?php foreach ($products as $prod): ?>
                                    <option value="<?= $prod['id'] ?>">
                                        <?= htmlspecialchars($prod['nama_barang']) ?> (Stok: <?= $prod['stok'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Jenis Transaksi</label>
                            <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                                <option value="masuk">Barang Masuk (+)</option>
                                <option value="keluar">Barang Keluar (-)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Jumlah</label>
                            <input type="number" name="quantity" min="1" required placeholder="Contoh: 5"
                                class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition shadow-md">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Riwayat Transaksi -->
                <div class="bg-white rounded-2xl p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Riwayat Transaksi Masuk & Keluar
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                                    <th class="p-3 rounded-l-xl">No</th>
                                    <th class="p-3">Tanggal & Waktu</th>
                                    <th class="p-3">Nama Barang</th>
                                    <th class="p-3">Jenis</th>
                                    <th class="p-3">Jumlah</th>
                                    <th class="p-3 rounded-r-xl">Petugas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm">
                                <?php if (count($history) > 0): ?>
                                    <?php $no = 1; foreach ($history as $t): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 font-semibold text-gray-500"><?= $no++ ?></td>
                                        <td class="p-3 text-gray-600"><?= $t['created_at'] ?></td>
                                        <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($t['nama_barang']) ?></td>
                                        <td class="p-3">
                                            <?php if ($t['type'] === 'masuk'): ?>
                                                <span class="bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-lg font-semibold"><i class="fa-solid fa-arrow-down mr-1"></i> Masuk</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-700 text-xs px-2.5 py-1 rounded-lg font-semibold"><i class="fa-solid fa-arrow-up mr-1"></i> Keluar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3 font-bold <?= $t['type'] === 'masuk' ? 'text-green-600' : 'text-red-600' ?>">
                                            <?= $t['type'] === 'masuk' ? '+' : '-' ?><?= $t['quantity'] ?>
                                        </td>
                                        <td class="p-3 text-gray-600 font-medium"><?= htmlspecialchars($t['username']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada riwayat transaksi tercatat.</td>
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