<?php
require_once 'koneksi.php';
$page_title = "Billing & Pembayaran Kasir";
$active_tab = "billing";

$message = "";
$messageType = "";

// Handle Process Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_bayar'])) {
    $id_transaksi = $_POST['id_transaksi'];
    $metode = $_POST['metode_bayar'];
    $total_bayar = (float)$_POST['total_bayar'];
    $uang_bayar = $metode === 'tunai' ? (float)$_POST['uang_bayar'] : $total_bayar;

    if ($metode === 'tunai' && $uang_bayar < $total_bayar) {
        $message = "Uang pembayaran kurang dari total tagihan!";
        $messageType = "error";
    } else {
        $kembalian = $uang_bayar - $total_bayar;
        $stmt = $pdo->prepare("UPDATE pesanan SET status_bayar = 'lunas', metode_bayar = ?, uang_bayar = ?, kembalian = ? WHERE id_transaksi = ?");
        $stmt->execute([$metode, $uang_bayar, $kembalian, $id_transaksi]);
        $message = "Pembayaran #{$id_transaksi} Berhasil! Kembalian: " . formatRp($kembalian);
        $messageType = "success";
    }
}

// Fetch Orders
$pesananList = $pdo->query("SELECT p.*, pel.nama_pelanggan FROM pesanan p JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan ORDER BY p.tanggal DESC")->fetchAll();

// Detail Active Order
$selectedOrder = null;
$selectedItems = [];
if (isset($_GET['id'])) {
    $stmtSel = $pdo->prepare("SELECT p.*, pel.nama_pelanggan FROM pesanan p JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan WHERE p.id_transaksi = ?");
    $stmtSel->execute([$_GET['id']]);
    $selectedOrder = $stmtSel->fetch();

    if ($selectedOrder) {
        $stmtDet = $pdo->prepare("SELECT d.*, pr.nama_produk FROM detail_pesanan d JOIN produk pr ON d.id_produk = pr.id_produk WHERE d.id_transaksi = ?");
        $stmtDet->execute([$_GET['id']]);
        $selectedItems = $stmtDet->fetchAll();
    }
}

include 'header.php';
?>

<?php if ($message): ?>
<div class="p-4 rounded-xl mb-4 font-semibold text-sm <?= $messageType == 'success' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Tabel Pilih Pesanan -->
    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-bold text-slate-800"><i class="fa-solid fa-file-invoice-dollar text-emerald-600 mr-2"></i>Pilih Pesanan untuk Ditagihkan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-extrabold text-slate-500">
                        <th class="py-3 px-4">ID Transaksi</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php foreach ($pesananList as $ps): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-bold text-slate-800"><?= $ps['id_transaksi'] ?></td>
                            <td class="py-3 px-4"><?= $ps['nama_pelanggan'] ?></td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $ps['status_bayar'] == 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                    <?= ucfirst($ps['status_bayar']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 font-extrabold text-slate-800"><?= formatRp($ps['total_bayar']) ?></td>
                            <td class="py-3 px-4 text-center">
                                <a href="billing.php?id=<?= $ps['id_transaksi'] ?>" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs inline-block">
                                    Lihat Billing
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Panel Detail & Pembayaran -->
    <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <?php if ($selectedOrder): ?>
            <div class="space-y-4">
                <div class="flex justify-between items-start border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-xs font-bold text-emerald-600 uppercase">Struk Billing</span>
                        <h3 class="text-lg font-extrabold text-slate-800">Pesanan #<?= $selectedOrder['id_transaksi'] ?></h3>
                        <p class="text-xs text-slate-500">Pelanggan: <?= $selectedOrder['nama_pelanggan'] ?></p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $selectedOrder['status_bayar'] == 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                        <?= strtoupper($selectedOrder['status_bayar']) ?>
                    </span>
                </div>

                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="uppercase text-slate-400 font-bold border-b border-slate-200">
                            <th class="pb-1">Produk</th>
                            <th class="pb-1">Harga</th>
                            <th class="pb-1">Qty</th>
                            <th class="pb-1 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($selectedItems as $item): ?>
                            <tr>
                                <td class="py-2 text-slate-800 font-medium"><?= $item['nama_produk'] ?></td>
                                <td class="py-2 text-slate-600"><?= formatRp($item['harga']) ?></td>
                                <td class="py-2 font-bold text-slate-700"><?= $item['qty'] ?></td>
                                <td class="py-2 text-right font-bold text-slate-800"><?= formatRp($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="bg-slate-50 p-3.5 rounded-xl space-y-1.5 border border-slate-200">
                    <div class="flex justify-between text-sm">
                        <span class="font-bold text-slate-600">Total Tagihan:</span>
                        <span class="font-extrabold text-slate-900 text-base"><?= formatRp($selectedOrder['total_bayar']) ?></span>
                    </div>
                    <?php if ($selectedOrder['status_bayar'] == 'lunas'): ?>
                        <div class="flex justify-between text-xs text-slate-500 pt-1 border-t border-slate-200">
                            <span>Metode Bayar:</span>
                            <span class="font-bold uppercase text-slate-700"><?= $selectedOrder['metode_bayar'] ?></span>
                        </div>
                        <div class="flex justify-between text-xs text-emerald-700 font-bold">
                            <span>Kembalian:</span>
                            <span><?= formatRp($selectedOrder['kembalian']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($selectedOrder['status_bayar'] != 'lunas'): ?>
                    <form action="" method="POST" class="space-y-3 pt-2">
                        <input type="hidden" name="id_transaksi" value="<?= $selectedOrder['id_transaksi'] ?>">
                        <input type="hidden" name="total_bayar" value="<?= $selectedOrder['total_bayar'] ?>">
                        
                        <div>
                            <label class="block text-xs text-slate-600 font-semibold mb-1">Metode Pembayaran</label>
                            <select name="metode_bayar" id="metode_select" onchange="toggleMetodeBayar(this.value)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold focus:outline-emerald-500">
                                <option value="tunai">Tunai</option>
                                <option value="qris">Scan QR (QRIS / GoPay / OVO / ShopeePay)</option>
                                <option value="transfer">Transfer Bank (BCA / Mandiri / BRI)</option>
                                <option value="kartu">Kartu Debit / Kredit</option>
                            </select>
                        </div>

                        <!-- Panel Input Uang Tunai -->
                        <div id="wrapper_tunai">
                            <label class="block text-xs text-slate-600 font-semibold mb-1">Uang Dibayarkan (Rp)</label>
                            <input type="number" id="uang_bayar_input" name="uang_bayar" min="<?= $selectedOrder['total_bayar'] ?>" placeholder="<?= $selectedOrder['total_bayar'] ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm font-bold focus:outline-emerald-500" required>
                        </div>

                        <!-- Panel QRIS dengan Tombol Buka Layar Customer -->
                        <div id="wrapper_qris" class="hidden text-center p-4 bg-emerald-50 border border-emerald-200 rounded-xl space-y-3">
                            <p class="text-xs font-bold text-emerald-800">Pembayaran QRIS Aktif</p>
                            <a href="qris_display.php?id=<?= $selectedOrder['id_transaksi'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm transition-all">
                                <i class="fa-solid fa-desktop"></i> Tampilkan QR di Layar Pelanggan
                            </a>
                            <p class="text-[11px] text-slate-500">Klik tombol di atas untuk membuka tampilan QRIS pada monitor kedua / layar pembeli.</p>
                        </div>

                        <!-- Panel Transfer Bank -->
                        <div id="wrapper_transfer" class="hidden p-3 bg-blue-50 border border-blue-200 rounded-xl space-y-1 text-xs">
                            <p class="font-bold text-blue-900 mb-1">Rekening Tujuan Transfer:</p>
                            <div class="flex justify-between text-slate-700">
                                <span>Bank BCA:</span>
                                <span class="font-extrabold">123-456-7890</span>
                            </div>
                            <div class="flex justify-between text-slate-700">
                                <span>Bank Mandiri:</span>
                                <span class="font-extrabold">987-000-12345</span>
                            </div>
                        </div>

                        <!-- Panel Kartu -->
                        <div id="wrapper_kartu" class="hidden p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                            <label class="block text-[11px] text-slate-600 font-semibold mb-1">Nomor Referensi EDC / Kartu (Opsional)</label>
                            <input type="text" name="no_referensi_kartu" placeholder="Contoh: EDC-12345678" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>

                        <button type="submit" name="proses_bayar" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-md transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check-circle"></i> Konfirmasi & Bayar
                        </button>
                    </form>

                    <script>
                        function toggleMetodeBayar(val) {
                            document.getElementById('wrapper_tunai').classList.add('hidden');
                            document.getElementById('wrapper_qris').classList.add('hidden');
                            document.getElementById('wrapper_transfer').classList.add('hidden');
                            document.getElementById('wrapper_kartu').classList.add('hidden');

                            const inputUang = document.getElementById('uang_bayar_input');

                            if (val === 'tunai') {
                                document.getElementById('wrapper_tunai').classList.remove('hidden');
                                inputUang.setAttribute('required', 'required');
                            } else {
                                inputUang.removeAttribute('required');
                                if (val === 'qris') {
                                    document.getElementById('wrapper_qris').classList.remove('hidden');
                                } else if (val === 'transfer') {
                                    document.getElementById('wrapper_transfer').classList.remove('hidden');
                                } else if (val === 'kartu') {
                                    document.getElementById('wrapper_kartu').classList.remove('hidden');
                                }
                            }
                        }
                    </script>
                <?php else: ?>
                    <button onclick="window.print()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2">
                        <i class="fa-solid fa-print"></i> Cetak Struk Transaksi
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 text-slate-400">
                <i class="fa-solid fa-hand-pointer text-4xl mb-3 text-slate-300"></i>
                <p class="text-sm font-semibold">Pilih pesanan di tabel sebelah kiri untuk melihat billing.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>