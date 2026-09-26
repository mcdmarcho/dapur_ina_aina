<?php
require_once 'koneksi.php';
$page_title = "Input & Update Stok Produk";
$active_tab = "stok";

$message = "";
$messageType = "";

// Set PDO agar melempar Exception jika ada error SQL
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fungsi Helper Upload Gambar
function uploadGambar($file) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($file["name"]);
    $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($imageFileType, $allowedTypes)) {
        return ['status' => false, 'message' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP!'];
    }

    if ($file["size"] > 2000000) { // Maksimal 2MB
        return ['status' => false, 'message' => 'Ukuran gambar maksimal 2MB!'];
    }

    $newFileName = time() . '_' . uniqid() . '.' . $imageFileType;
    $targetFilePath = $targetDir . $newFileName;

    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        return ['status' => true, 'filename' => $newFileName];
    }

    return ['status' => false, 'message' => 'Gagal mengunggah gambar ke server.'];
}

// 1. Handle Tambah Produk Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_produk'])) {
    $nama = trim($_POST['nama_produk']);
    $kategori = $_POST['kategori'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $gambarName = 'default.jpg';

    $uploadSuccess = true;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadGambar($_FILES['gambar']);
        if ($uploadResult['status']) {
            $gambarName = $uploadResult['filename'];
        } else {
            $message = $uploadResult['message'];
            $messageType = "danger";
            $uploadSuccess = false;
        }
    }

    if ($uploadSuccess) {
        try {
            $stmt = $pdo->prepare("INSERT INTO produk (nama_produk, kategori, harga, stok, gambar) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$nama, $kategori, $harga, $stok, $gambarName])) {
                $message = "Produk '{$nama}' berhasil ditambahkan!";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            $message = "Gagal menambah produk: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// 2. Handle Edit Nama, Detail, & Stok Produk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_produk'])) {
    $id_produk = trim($_POST['id_produk']);
    $nama_baru = trim($_POST['nama_produk']);
    $kategori_baru = $_POST['kategori'];
    $harga_baru = (int)$_POST['harga'];
    $stok_baru = (int)$_POST['stok_baru'];

    if (!empty($id_produk)) {
        try {
            // Cek apakah ada update gambar baru
            if (isset($_FILES['gambar_baru']) && $_FILES['gambar_baru']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadGambar($_FILES['gambar_baru']);
                if ($uploadResult['status']) {
                    $gambarBaru = $uploadResult['filename'];
                    $stmt = $pdo->prepare("UPDATE produk SET nama_produk = :nama, kategori = :kategori, harga = :harga, stok = :stok, gambar = :gambar WHERE id_produk = :id");
                    $stmt->bindValue(':gambar', $gambarBaru);
                } else {
                    $message = $uploadResult['message'];
                    $messageType = "danger";
                }
            } else {
                $stmt = $pdo->prepare("UPDATE produk SET nama_produk = :nama, kategori = :kategori, harga = :harga, stok = :stok WHERE id_produk = :id");
            }

            if (empty($message)) {
                $stmt->bindValue(':nama', $nama_baru);
                $stmt->bindValue(':kategori', $kategori_baru);
                $stmt->bindValue(':harga', $harga_baru, PDO::PARAM_INT);
                $stmt->bindValue(':stok', $stok_baru, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id_produk);
                $stmt->execute();

                $message = "Data produk '{$nama_baru}' berhasil diperbarui!";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            $message = "Gagal memperbarui produk: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "Harap pilih produk yang ingin diedit!";
        $messageType = "danger";
    }
}

// 3. Handle Hapus Produk
if (isset($_GET['hapus_id'])) {
    $id_hapus = trim($_GET['hapus_id']);
    try {
        // Ambil data gambar terlebih dahulu untuk dihapus dari folder jika bukan default
        $stmtImg = $pdo->prepare("SELECT gambar FROM produk WHERE id_produk = ?");
        $stmtImg->execute([$id_hapus]);
        $prodData = $stmtImg->fetch();

        if ($prodData) {
            if ($prodData['gambar'] !== 'default.jpg' && file_exists('uploads/' . $prodData['gambar'])) {
                unlink('uploads/' . $prodData['gambar']);
            }

            $stmtDel = $pdo->prepare("DELETE FROM produk WHERE id_produk = ?");
            $stmtDel->execute([$id_hapus]);

            $message = "Produk berhasil dihapus!";
            $messageType = "success";
        }
    } catch (PDOException $e) {
        $message = "Gagal menghapus produk: " . $e->getMessage();
        $messageType = "danger";
    }
}

// 4. Ambil Data Produk TERBARU
$produkList = $pdo->query("SELECT * FROM produk ORDER BY FIELD(kategori, 'makanan', 'minuman') ASC, stok DESC")->fetchAll();

include 'header.php';
?>

<?php if ($message): ?>
<div class="p-4 rounded-xl mb-4 font-semibold text-sm <?= $messageType == 'success' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($messageType == 'warning' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-rose-100 text-rose-800 border border-rose-300') ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1 space-y-6">
        <!-- Form Tambah Produk -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="font-bold text-slate-800 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-blue-600"></i> Tambah Produk Baru
            </h3>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Produk</label>
                    <input type="text" name="nama_produk" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori</label>
                    <select name="kategori" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                        <option value="makanan">Makanan</option>
                        <option value="minuman">Minuman</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Harga (Rp)</label>
                        <input type="number" name="harga" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Stok Awal</label>
                        <input type="number" name="stok" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gambar Produk</label>
                    <input type="file" name="gambar" accept="image/*" class="w-full px-2 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-600">
                </div>
                <button type="submit" name="tambah_produk" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow-md transition-all">Simpan Produk</button>
            </form>
        </div>

        <!-- Form Edit Nama & Detail Produk -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="font-bold text-slate-800 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-600"></i> Edit Produk / Stok
            </h3>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Produk</label>
                    <select id="select_edit_produk" name="id_produk" required onchange="autofillEditForm()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($produkList as $pr): ?>
                            <option value="<?= $pr['id_produk'] ?>" 
                                    data-nama="<?= htmlspecialchars($pr['nama_produk']) ?>" 
                                    data-kategori="<?= $pr['kategori'] ?>" 
                                    data-harga="<?= $pr['harga'] ?>" 
                                    data-stok="<?= $pr['stok'] ?>">
                                <?= htmlspecialchars($pr['nama_produk']) ?> (Stok: <?= $pr['stok'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Produk Baru</label>
                    <input type="text" id="edit_nama_produk" name="nama_produk" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori</label>
                    <select id="edit_kategori" name="kategori" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                        <option value="makanan">Makanan</option>
                        <option value="minuman">Minuman</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Harga (Rp)</label>
                        <input type="number" id="edit_harga" name="harga" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jumlah Stok</label>
                        <input type="number" id="edit_stok" name="stok_baru" min="0" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ganti Gambar (Opsional)</label>
                    <input type="file" name="gambar_baru" accept="image/*" class="w-full px-2 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-600">
                </div>
                <button type="submit" name="edit_produk" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-lg shadow-md transition-all">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Daftar Katalog Tabel -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-bold text-slate-800"><i class="fa-solid fa-box text-blue-600 mr-2"></i>Daftar Katalog Produk</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-extrabold text-slate-500">
                        <th class="py-3 px-4">Gambar</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Harga</th>
                        <th class="py-3 px-4">Stok Saat Ini</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php foreach ($produkList as $p): ?>
                        <?php $imgPath = (!empty($p['gambar']) && file_exists('uploads/' . $p['gambar'])) ? 'uploads/' . $p['gambar'] : 'assets/images/default.jpg'; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4">
                                <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" class="w-12 h-12 object-cover rounded-lg border border-slate-200">
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($p['nama_produk']) ?></td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md text-[10px] uppercase font-bold <?= strtolower($p['kategori']) == 'makanan' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200' ?>">
                                    <?= htmlspecialchars($p['kategori']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700"><?= function_exists('formatRp') ? formatRp($p['harga']) : 'Rp ' . number_format($p['harga'], 0, ',', '.') ?></td>
                            <td class="py-3 px-4">
                                <?php if ($p['stok'] <= 10): ?>
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200"><?= $p['stok'] ?> (Stok Rendah)</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700"><?= $p['stok'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="stok.php?hapus_id=<?= $p['id_produk'] ?>" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus produk <?= htmlspecialchars($p['nama_produk']) ?> ini?');" 
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors" title="Hapus Produk">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Fungsi JavaScript untuk mengisi otomatis nilai form Edit saat produk dipilih
function autofillEditForm() {
    var select = document.getElementById('select_edit_produk');
    var selectedOption = select.options[select.selectedIndex];

    if (select.value !== "") {
        document.getElementById('edit_nama_produk').value = selectedOption.getAttribute('data-nama');
        document.getElementById('edit_kategori').value = selectedOption.getAttribute('data-kategori');
        document.getElementById('edit_harga').value = selectedOption.getAttribute('data-harga');
        document.getElementById('edit_stok').value = selectedOption.getAttribute('data-stok');
    } else {
        document.getElementById('edit_nama_produk').value = "";
        document.getElementById('edit_harga').value = "";
        document.getElementById('edit_stok').value = "";
    }
}
</script>

<?php include 'footer.php'; ?>