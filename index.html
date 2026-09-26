<?php
require_once 'koneksi.php';
$page_title = "Dashboard Overview";
$active_tab = "dashboard";

// Fetch KPIs
$stmtRev = $pdo->query("SELECT SUM(total_bayar) as rev FROM pesanan WHERE status_bayar = 'lunas'");
$totalRevenue = $stmtRev->fetch()['rev'] ?? 0;

$stmtOrd = $pdo->query("SELECT COUNT(*) as cnt FROM pesanan");
$totalOrders = $stmtOrd->fetch()['cnt'];

$stmtUnpaid = $pdo->query("SELECT COUNT(*) as cnt FROM pesanan WHERE status_bayar = 'belum bayar'");
$totalUnpaid = $stmtUnpaid->fetch()['cnt'];

$stmtProd = $pdo->query("SELECT COUNT(*) as cnt FROM produk");
$totalProducts = $stmtProd->fetch()['cnt'];

// Fetch SELURUH Produk Dinamis tanpa LIMIT
$stmtMenu = $pdo->query("SELECT * FROM produk ORDER BY id_produk DESC");
$menuDashboard = $stmtMenu->fetchAll();

include 'header.php';
?>

<!-- Welcome Banner -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 p-6 md:p-8 text-white shadow-xl">
    <div class="relative z-10 max-w-2xl">
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 inline-block mb-3">Dapur Ina Aina</span>
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-100">Sistem Manajemen Dapur</h2>
        <p class="text-slate-300 text-sm mt-2 leading-relaxed">
            Kelola pesanan, stok produk, dan pembayaran kasir dengan cepat dan akurat.
        </p>
    </div>
    <i class="fa-solid fa-mug-saucer absolute -right-6 -bottom-6 text-9xl text-white/5 transform -rotate-12 pointer-events-none"></i>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pendapatan</span>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-wallet text-base"></i></div>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800 mt-2"><?= formatRp($totalRevenue) ?></h3>
        <p class="text-xs text-emerald-600 mt-1 font-medium"><i class="fa-solid fa-arrow-trend-up"></i> Total dari pesanan lunas</p>
    </div>

    <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pesanan</span>
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-cash-register text-base"></i></div>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800 mt-2"><?= $totalOrders ?></h3>
        <p class="text-xs text-slate-500 mt-1 font-medium">Transaksi terdaftar</p>
    </div>

    <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Belum Lunas</span>
            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-clock-rotate-left text-base"></i></div>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800 mt-2"><?= $totalUnpaid ?></h3>
        <p class="text-xs text-amber-600 mt-1 font-medium">Menunggu billing</p>
    </div>

    <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Menu/Stok</span>
            <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center"><i class="fa-solid fa-mug-hot text-base"></i></div>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800 mt-2"><?= $totalProducts ?></h3>
        <p class="text-xs text-slate-500 mt-1 font-medium">Item produk aktif</p>
    </div>
</div>

<!-- Section Seluruh Daftar Menu Katalog -->
<div class="mt-8 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Daftar Menu Katalog</h3>
            <p class="text-xs text-slate-500">Menampilkan seluruh menu produk yang terdaftar di Dapur Ina Aina</p>
        </div>
        <a href="stok.php" class="text-xs font-semibold text-amber-600 hover:text-amber-700 hover:underline flex items-center gap-1">
            Kelola Stok & Menu <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <!-- Grid Layout: 4 kolom di Laptop/PC, 3 di Tablet, 2 di HP -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
        <?php foreach ($menuDashboard as $m): ?>
            <?php $imgPath = (!empty($m['gambar']) && file_exists('uploads/' . $m['gambar'])) ? 'uploads/' . $m['gambar'] : 'uploads/default.jpg'; ?>
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden group hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                <div>
                    <!-- Container Gambar -->
                    <div class="relative h-36 md:h-40 overflow-hidden bg-slate-100">
                        <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($m['nama_produk']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <span class="absolute top-2.5 right-2.5 text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-xs uppercase tracking-wider <?= strtolower($m['kategori']) == 'makanan' ? 'bg-amber-500 text-white' : 'bg-blue-500 text-white' ?>">
                            <?= htmlspecialchars($m['kategori']) ?>
                        </span>
                    </div>

                    <!-- Detail Menu -->
                    <div class="p-3.5">
                        <h4 class="text-sm font-bold text-slate-800 line-clamp-1 group-hover:text-amber-600 transition-colors" title="<?= htmlspecialchars($m['nama_produk']) ?>">
                            <?= htmlspecialchars($m['nama_produk']) ?>
                        </h4>
                        <p class="text-xs font-extrabold text-slate-900 mt-1">
                            <?= function_exists('formatRp') ? formatRp($m['harga']) : 'Rp ' . number_format($m['harga'], 0, ',', '.') ?>
                        </p>
                    </div>
                </div>

                <!-- Footer Stok Card -->
                <div class="px-3.5 pb-3.5 pt-1 flex items-center justify-between border-t border-slate-50 mt-1">
                    <span class="text-[11px] text-slate-400 font-medium">Stok Tersedia:</span>
                    <span class="text-xs px-2 py-0.5 rounded-md <?= $m['stok'] <= 10 ? 'bg-rose-50 text-rose-600 font-bold' : 'bg-emerald-50 text-emerald-600 font-semibold' ?>">
                        <?= $m['stok'] ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
