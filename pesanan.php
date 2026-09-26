<?php
require_once 'koneksi.php';
$page_title = "Data Pesanan Pelanggan";
$active_tab = "pesanan";

$message = "";
$messageType = "";

// Handle Tambah Pesanan Baru (Multi-Item)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_pesanan'])) {
    $id_pelanggan = $_POST['id_pelanggan'];
    $nama_pelanggan_baru = trim($_POST['nama_pelanggan_baru'] ?? '');
    
    // Array produk dan qty dari form
    $produk_ids = $_POST['id_produk'] ?? [];
    $qtys = $_POST['qty'] ?? [];

    if (empty($produk_ids) || count($produk_ids) === 0) {
        $message = "Harap pilih minimal satu produk!";
        $messageType = "error";
    } else {
        $pdo->beginTransaction();
        try {
            // 1. Opsi Tambah Pelanggan Baru
            if ($id_pelanggan === 'NEW') {
                if (empty($nama_pelanggan_baru)) {
                    throw new Exception("Nama pelanggan baru tidak boleh kosong!");
                }
                $insPel = $pdo->prepare("INSERT INTO pelanggan (nama_pelanggan) VALUES (?)");
                $insPel->execute([$nama_pelanggan_baru]);
                $id_pelanggan = $pdo->lastInsertId();
            }

            // 2. Cek stok & hitung total bayar untuk semua barang
            $total_bayar = 0;
            $items_to_insert = [];

            foreach ($produk_ids as $index => $id_prod) {
                $qty = (int)($qtys[$index] ?? 0);
                if ($qty <= 0) continue;

                // Ambil data produk
                $stmtProd = $pdo->prepare("SELECT * FROM produk WHERE id_produk = ?");
                $stmtProd->execute([$id_prod]);
                $prod = $stmtProd->fetch();

                if (!$prod) {
                    throw new Exception("Produk tidak ditemukan!");
                }

                if ($prod['stok'] < $qty) {
                    throw new Exception("Stok produk '{$prod['nama_produk']}' tidak mencukupi! (Sisa: {$prod['stok']})");
                }

                $subtotal = $prod['harga'] * $qty;
                $total_bayar += $subtotal;

                $items_to_insert[] = [
                    'id_produk' => $id_prod,
                    'harga' => $prod['harga'],
                    'qty' => $qty,
                    'subtotal' => $subtotal
                ];
            }

            if (empty($items_to_insert)) {
                throw new Exception("Jumlah item/qty harus lebih dari 0!");
            }

            // 3. Generate TRX ID unik
            $countStmt = $pdo->query("SELECT COUNT(*) FROM pesanan");
            $nextNum = $countStmt->fetchColumn() + 1;
            $id_transaksi = 'TRX-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            $tanggal = date('Y-m-d H:i:s');

            // 4. Insert Utama Pesanan
            $insStmt = $pdo->prepare("INSERT INTO pesanan (id_transaksi, id_pelanggan, tanggal, total_bayar) VALUES (?, ?, ?, ?)");
            $insStmt->execute([$id_transaksi, $id_pelanggan, $tanggal, $total_bayar]);

            // 5. Insert Detail Pesanan & Update Stok
            $insDetail = $pdo->prepare("INSERT INTO detail_pesanan (id_transaksi, id_produk, harga, qty, subtotal) VALUES (?, ?, ?, ?, ?)");
            $updStok = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");

            foreach ($items_to_insert as $item) {
                $insDetail->execute([
                    $id_transaksi, 
                    $item['id_produk'], 
                    $item['harga'], 
                    $item['qty'], 
                    $item['subtotal']
                ]);

                $updStok->execute([$item['qty'], $item['id_produk']]);
            }

            $pdo->commit();
            $message = "Pesanan #{$id_transaksi} berhasil dibuat!";
            $messageType = "success";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Gagal membuat pesanan: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Fetch Master Data
$pelangganList = $pdo->query("SELECT * FROM pelanggan ORDER BY nama_pelanggan ASC")->fetchAll();
$produkList = $pdo->query("SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk ASC")->fetchAll();

// Fetch Pesanan List
$pesananList = $pdo->query("
    SELECT p.*, pel.nama_pelanggan 
    FROM pesanan p 
    JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan 
    ORDER BY p.tanggal DESC
")->fetchAll();

include 'header.php';
?>

<?php if ($message): ?>
<div class="p-4 rounded-xl mb-4 font-semibold text-sm <?= $messageType == 'success' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Buat Pesanan -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs h-fit">
        <h3 class="font-bold text-slate-800 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-circle-plus text-amber-600"></i> Buat Pesanan Baru
        </h3>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Pelanggan *</label>
                <select name="id_pelanggan" id="select_pelanggan" onchange="togglePelangganBaru(this)" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm">
                    <option value="">-- Pilih Pelanggan --</option>
                    <option value="NEW" class="font-bold text-amber-600">+ Tambah Pelanggan Baru</option>
                    <?php foreach ($pelangganList as $pel): ?>
                        <option value="<?= $pel['id_pelanggan'] ?>"><?= htmlspecialchars($pel['nama_pelanggan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Input Nama Pelanggan Baru -->
            <div id="field_pelanggan_baru" class="hidden">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nama Pelanggan Baru *</label>
                <input type="text" name="nama_pelanggan_baru" id="input_pelanggan_baru" placeholder="Masukkan nama pelanggan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm">
            </div>

            <!-- Container Produk Dinamis -->
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Produk Pesanan *</label>
                    <button type="button" onclick="tambahBarisProduk()" class="text-xs text-amber-600 font-bold hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> Tambah Item
                    </button>
                </div>

                <div id="wrapper_produk" class="space-y-3">
                    <div class="baris-produk p-3 bg-slate-50 rounded-xl border border-slate-200 relative">
                        <div class="space-y-2">
                            <select name="id_produk[]" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm">
                                <option value="">-- Pilih Produk --</option>
                                <?php foreach ($produkList as $pr): ?>
                                    <option value="<?= $pr['id_produk'] ?>"><?= htmlspecialchars($pr['nama_produk']) ?> (Stok: <?= $pr['stok'] ?>, <?= formatRp($pr['harga']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="flex items-center gap-2">
                                <input type="number" name="qty[]" min="1" value="1" placeholder="Qty" required class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-sm">
                                <button type="button" onclick="hapusBarisProduk(this)" class="btn-hapus hidden p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" name="tambah_pesanan" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-lg shadow-md transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Pesanan
            </button>
        </form>
    </div>

    <!-- Tabel Daftar Pesanan -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800"><i class="fa-solid fa-list-ul text-amber-600 mr-2"></i>Daftar Seluruh Pesanan</h3>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600"><?= count($pesananList) ?> Pesanan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-extrabold text-slate-500">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php foreach ($pesananList as $ps): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($ps['id_transaksi']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($ps['nama_pelanggan']) ?></td>
                            <td class="py-3 px-4 text-xs text-slate-500"><?= htmlspecialchars($ps['tanggal']) ?></td>
                            <td class="py-3 px-4">
                                <?php if (($ps['status_bayar'] ?? '') == 'lunas'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Belum Bayar</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 font-extrabold text-slate-800"><?= formatRp($ps['total_bayar']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function togglePelangganBaru(select) {
    const fieldBaru = document.getElementById('field_pelanggan_baru');
    const inputBaru = document.getElementById('input_pelanggan_baru');
    
    if (select.value === 'NEW') {
        fieldBaru.classList.remove('hidden');
        inputBaru.setAttribute('required', 'required');
    } else {
        fieldBaru.classList.add('hidden');
        inputBaru.removeAttribute('required');
        inputBaru.value = '';
    }
}

function tambahBarisProduk() {
    const wrapper = document.getElementById('wrapper_produk');
    const firstRow = wrapper.querySelector('.baris-produk');
    const newRow = firstRow.cloneNode(true);

    // Reset nilai di baris baru
    newRow.querySelector('select').value = '';
    newRow.querySelector('input[type="number"]').value = '1';

    wrapper.appendChild(newRow);
    cekTombolHapus();
}

function hapusBarisProduk(btn) {
    const rows = document.querySelectorAll('.baris-produk');
    if (rows.length > 1) {
        btn.closest('.baris-produk').remove();
        cekTombolHapus();
    }
}

function cekTombolHapus() {
    const rows = document.querySelectorAll('.baris-produk');
    rows.forEach(row => {
        const btnHapus = row.querySelector('.btn-hapus');
        if (rows.length > 1) {
            btnHapus.classList.remove('hidden');
        } else {
            btnHapus.classList.add('hidden');
        }
    });
}
</script>

<?php include 'footer.php'; ?>