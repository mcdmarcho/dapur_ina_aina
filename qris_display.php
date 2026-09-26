<?php
require_once 'koneksi.php';

$id_transaksi = isset($_GET['id']) ? $_GET['id'] : null;
$order = null;

if ($id_transaksi) {
    $stmt = $pdo->prepare("SELECT p.*, pel.nama_pelanggan FROM pesanan p JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan WHERE p.id_transaksi = ?");
    $stmt->execute([$id_transaksi]);
    $order = $stmt->fetch();
}

// Check status via AJAX
if (isset($_GET['check_status']) && $id_transaksi) {
    header('Content-Type: application/json');
    echo json_encode(['status_bayar' => $order ? $order['status_bayar'] : '']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Pembayaran QRIS Pelanggan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden text-center p-8">
        <?php if ($order): ?>
            <?php if ($order['status_bayar'] == 'lunas'): ?>
                <div id="status_success" class="space-y-4 py-8">
                    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-4xl">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h2 class="text-2xl font-extrabold text-slate-800">Pembayaran Berhasil!</h2>
                    <p class="text-slate-500 text-sm">Terima kasih atas kunjungan Anda.</p>
                </div>
            <?php else: ?>
                <div id="status_pending" class="space-y-6">
                    <div>
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Total Pembayaran</span>
                        <h1 class="text-3xl font-extrabold text-slate-900 mt-1"><?= formatRp($order['total_bayar']) ?></h1>
                        <p class="text-xs text-slate-400 mt-1">ID Transaksi: #<?= $order['id_transaksi'] ?></p>
                    </div>

                    <div class="bg-emerald-50 border border-emerald-200 p-6 rounded-2xl space-y-3">
                        <h3 class="text-sm font-bold text-emerald-800">Scan QRIS untuk Pembayaran</h3>
                        <div class="flex justify-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=BILLING-<?= $order['id_transaksi'] ?>-<?= $order['total_bayar'] ?>" 
                                 alt="QRIS Code" 
                                 class="w-52 h-52 border border-slate-200 rounded-xl bg-white p-2 shadow-sm">
                        </div>
                        <p class="text-xs text-slate-500">Scan menggunakan GoPay, OVO, Dana, ShopeePay, atau Mobile Banking.</p>
                    </div>

                    <div class="flex items-center justify-center gap-2 text-xs text-slate-400">
                        <i class="fa-solid fa-spinner animate-spin text-emerald-600"></i>
                        <span>Menunggu konfirmasi kasir...</span>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="py-12 text-slate-400">
                <i class="fa-solid fa-qrcode text-5xl mb-3 text-slate-300"></i>
                <p class="text-sm font-semibold">Tidak ada transaksi QRIS yang aktif.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($order && $order['status_bayar'] != 'lunas'): ?>
    <script>
        // Cek status pembayaran setiap 2 detik secara teratur
        setInterval(function() {
            fetch('qris_display.php?id=<?= $order['id_transaksi'] ?>&check_status=1')
                .then(response => response.json())
                .then(data => {
                    if (data.status_bayar === 'lunas') {
                        location.reload();
                    }
                });
        }, 2000);
    </script>
    <?php endif; ?>

</body>
</html>