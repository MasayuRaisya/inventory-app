<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: barang.php');
    exit;
}

// Ambil data barang berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: barang.php');
    exit;
}

// Proses jika form edit disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $stok = intval($_POST['stok'] ?? 0);
    $harga = floatval($_POST['harga'] ?? 0);

    if (!empty($nama_barang) && !empty($kategori)) {
        $update = $pdo->prepare("UPDATE products SET nama_barang = ?, kategori = ?, stok = ?, harga = ? WHERE id = ?");
        if ($update->execute([$nama_barang, $kategori, $stok, $harga, $id])) {
            header('Location: barang.php');
            exit;
        } else {
            $error = 'Gagal memperbarui data barang.';
        }
    } else {
        $error = 'Nama barang dan kategori wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Barang - Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-indigo-700 min-h-screen font-sans text-gray-800 flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl p-8 shadow-2xl w-full max-w-xl">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-blue-600"></i> Edit Data Barang
            </h3>
            <a href="barang.php" class="text-gray-400 hover:text-gray-600 font-semibold text-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-xl text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nama Barang</label>
                <input type="text" name="nama_barang" required value="<?= htmlspecialchars($product['nama_barang']) ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Kategori</label>
                <input type="text" name="kategori" required value="<?= htmlspecialchars($product['kategori']) ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Stok</label>
                    <input type="number" name="stok" required value="<?= $product['stok'] ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Harga (Rp)</label>
                    <input type="number" step="0.01" name="harga" required value="<?= $product['harga'] ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl transition shadow-md">
                    <i class="fa-solid fa-save mr-2"></i> Update Barang
                </button>
            </div>
        </form>
    </div>

</body>
</html>